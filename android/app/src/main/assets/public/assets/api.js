/**
 * Tharimpepe FSMS - API Client Module
 *
 * Centralized HTTP client.
 * Persists state in localStorage.
 */

const API = (() => {
  // Use runtime-configurable base URL or fallback to default
  let baseURL = (typeof RuntimeConfig !== 'undefined') ? RuntimeConfig.getAPIBase() : '';

  // Request cache for idempotent reads
  const CACHE_PREFIX = 'api_cache_';
  const CACHE_TTL_MS = 30 * 1000;

  function getCached(key) {
    try {
      const raw = localStorage.getItem(CACHE_PREFIX + key);
      if (!raw) return null;
      const entry = JSON.parse(raw);
      if (!entry || !entry.expiresAt || Date.now() > entry.expiresAt) {
        localStorage.removeItem(CACHE_PREFIX + key);
        return null;
      }
      return entry.data;
    } catch (e) {
      return null;
    }
  }

  function setCache(key, data) {
    try {
      localStorage.setItem(CACHE_PREFIX + key, JSON.stringify({
        data,
        expiresAt: Date.now() + CACHE_TTL_MS
      }));
    } catch (e) {
      // Ignore storage failures
    }
  }

  function invalidateCache(pattern) {
    if (!pattern) {
      try {
        const keys = Object.keys(localStorage).filter(k => k.startsWith(CACHE_PREFIX));
        keys.forEach(k => localStorage.removeItem(k));
      } catch (e) {
        // ignore
      }
      return;
    }
    try {
      const keys = Object.keys(localStorage).filter(k => k.startsWith(CACHE_PREFIX) && k.includes(pattern));
      keys.forEach(k => localStorage.removeItem(k));
    } catch (e) {
      // ignore
    }
  }


  /**
   * Set the base URL for all API requests and persist it.
   * @param {string} url - Base URL (e.g., 'http://192.168.1.100:8000' or empty for same-origin)
   */
  function setBaseURL(url) {
    baseURL = url;
    RuntimeConfig.setAPIBase(url);
  }

  /**
   * Get stored tokens
   */
  function getTokens() {
    try {
      const accessToken = localStorage.getItem('access_token');
      const refreshToken = localStorage.getItem('refresh_token');
      const expiresAt = localStorage.getItem('token_expires_at');
      return { accessToken, refreshToken, expiresAt };
    } catch {
      return { accessToken: null, refreshToken: null, expiresAt: null };
    }
  }

  /**
   * Store tokens in localStorage
   */
  function storeTokens(accessToken, refreshToken, expiresAt) {
    try {
      localStorage.setItem('access_token', accessToken || '');
      localStorage.setItem('refresh_token', refreshToken || '');
      localStorage.setItem('token_expires_at', expiresAt || '');
    } catch (e) {
      console.error('Failed to store tokens:', e);
    }
  }

  /**
   * Clear all auth data from storage
   */
  function clearAuth() {
    try {
      localStorage.removeItem('access_token');
      localStorage.removeItem('refresh_token');
      localStorage.removeItem('token_expires_at');
      localStorage.removeItem('user');
      sessionStorage.removeItem('user');
    } catch (e) {
      console.error('Failed to clear auth:', e);
    }
  }

  /**
   * Get stored user data
   */
  function getStoredUser() {
    try {
      const data = localStorage.getItem('user');
      return data ? JSON.parse(data) : null;
    } catch {
      return null;
    }
  }

  /**
   * Store user data
   */
  function storeUser(user) {
    try {
      localStorage.setItem('user', JSON.stringify(user));
    } catch (e) {
      console.error('Failed to store user:', e);
    }
  }

  /**
   * Make an HTTP request with auth headers
   * @param {string} method - HTTP method
   * @param {string} endpoint - API endpoint path (e.g., '/api/auth/login.php')
   * @param {object|null} body - Request body (for POST/PUT)
   * @param {boolean} requiresAuth - Whether to include Bearer token
   * @returns {Promise<object>} Parsed JSON response
   */
  async function request(method, endpoint, body = null, requiresAuth = true) {
    const url = baseURL + endpoint;
    const headers = { 'Content-Type': 'application/json' };

    // Add auth token if required
    if (requiresAuth) {
      const { accessToken } = getTokens();
      if (accessToken) {
        headers['Authorization'] = 'Bearer ' + accessToken;
      }
    }

    const options = { method, headers };
    if (body !== null) {
      options.body = JSON.stringify(body);
    }

    let response;
    try {
      response = await fetch(url, options);
    } catch (err) {
      if (!navigator.onLine && method !== 'GET' && typeof OfflineQueue !== 'undefined') {
        OfflineQueue.enqueue({ url, method: options.method, body: options.body, headers: options.headers });
      }
      throw new APIError('Network error: Unable to reach server', 'NETWORK_ERROR', 0);
    }

    // Handle 401 - try token refresh
    if (response.status === 401 && requiresAuth) {
      const refreshed = await tryRefreshToken();
      if (refreshed) {
        // Retry the original request with new token
        const { accessToken } = getTokens();
        headers['Authorization'] = 'Bearer ' + accessToken;
        const retryOptions = { method, headers };
        if (body !== null) {
          retryOptions.body = JSON.stringify(body);
        }
        try {
          response = await fetch(url, retryOptions);
        } catch (err) {
          throw new APIError('Network error on retry', 'NETWORK_ERROR', 0);
        }
      } else {
        clearAuth();
        throw new APIError('Session expired. Please log in again.', 'SESSION_EXPIRED', 401);
      }
    }

    // Parse JSON response
    let data;
    const responseText = await response.text();
    
    try {
      data = JSON.parse(responseText);
    } catch {
      // Server returned non-JSON (likely HTML error page)
      console.error('[API] Non-JSON response:', responseText.substring(0, 200));
      throw new APIError(
        `Invalid server response (HTTP ${response.status}). Expected JSON but got HTML. Check if server URL is correct.`,
        'PARSE_ERROR',
        response.status
      );
    }

    if (!response.ok) {
      throw new APIError(
        data.message || data.error || 'Request failed',
        data.error || 'API_ERROR',
        response.status
      );
    }

    return data;
  }

  /**
   * Try to refresh the access token using the refresh token
   * @returns {Promise<boolean>} Whether refresh succeeded
   */
  async function tryRefreshToken() {
    const { refreshToken } = getTokens();
    if (!refreshToken) return false;

    try {
      const url = baseURL + '/api/auth/refresh.php';
      const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ refresh_token: refreshToken })
      });

      if (!response.ok) return false;

      const data = await response.json();
      if (data.success && data.token) {
        storeTokens(data.token, data.refresh_token, data.expires_at);
        if (data.user) {
          storeUser(data.user);
        }
        return true;
      }
      return false;
    } catch {
      return false;
    }
  }

  /**
   * Authenticate user with username and password
   * @param {string} username
   * @param {string} password
   * @returns {Promise<object>} User object
   */
  async function login(username, password) {
    const data = await request('POST', '/api/auth/login.php', {
      username: username.trim(),
      password: password
    }, false);

    if (data.success && data.token) {
      storeTokens(data.token, data.refresh_token, data.expires_at);
      storeUser(data.user);
      return data.user;
    }

    throw new APIError(data.message || 'Login failed', 'LOGIN_FAILED', 401);
  }

  /**
   * Logout - revoke token on server and clear local storage
   */
  async function logout() {
    try {
      const { accessToken } = getTokens();
      if (accessToken) {
        // Attempt to revoke the token server-side
        await request('POST', '/api/auth/logout.php', {}, true).catch(() => {});
      }
    } catch {
      // Silently ignore - we clear local state regardless
    }
    clearAuth();
  }

  /**
   * Validate the current token and get user info
   * @returns {Promise<object|null>} User data if token valid, null otherwise
   */
  async function validateToken() {
    const { accessToken } = getTokens();
    if (!accessToken) return null;

    try {
      const data = await request('GET', '/api/auth/validate.php', null, true);
      if (data.success && data.user) {
        storeUser(data.user);
        return data.user;
      }
      return null;
    } catch {
      return null;
    }
  }

  /**
   * Check if user is authenticated (token exists, not necessarily valid)
   */
  function isAuthenticated() {
    const { accessToken } = getTokens();
    return !!accessToken;
  }

  /**
   * Get the current user from local storage
   */
  function getCurrentUser() {
    return getStoredUser();
  }

  // ===== Convenience HTTP method wrappers =====

  async function get(endpoint, skipAuth = false, useCache = true) {
    if (useCache && !skipAuth) {
      const cached = getCached(endpoint);
      if (cached) return cached;
    }
    const result = await request('GET', endpoint, null, !skipAuth);
    if (useCache && !skipAuth && result && result.success !== false) {
      setCache(endpoint, result);
    }
    return result;
  }

  function post(endpoint, body, skipAuth = false) {
    return request('POST', endpoint, body, !skipAuth);
  }

  function put(endpoint, body) {
    return request('PUT', endpoint, body, true);
  }

  function del(endpoint) {
    return request('DELETE', endpoint, null, true);
  }

  // ===== API Error class =====

  class APIError extends Error {
    constructor(message, code, status) {
      super(message);
      this.name = 'APIError';
      this.code = code || 'API_ERROR';
      this.status = status || 0;
    }
  }

  // Public API
  return {
    setBaseURL,
    login,
    logout,
    validateToken,
    getCurrentUser,
    isAuthenticated,
    getTokens,
    get,
    post,
    put,
    del,
    request,
    APIError,
    clearAuth,
    storeUser,
    getCached,
    setCache,
    invalidateCache
  };
})();

// Make globally accessible
window.API = API;
