/**
 * Tharimpepe FSMS - API Client Module
 * 
 * Centralized HTTP client with:
 * - Configurable base URL
 * - Token-based authentication (access + refresh)
 * - Automatic token persistence in localStorage
 * - Token refresh on 401 responses
 * - Auth header injection
 * 
 * Usage:
 *   import { api } from './api.js';
 *   const user = await api.login(username, password);
 *   const data = await api.get('/endpoint');
 */

const API = (() => {
  // Default to same-origin, but if running inside Capacitor use a reachable backend URL.
  // NOTE: This project does not define a server URL in capacitor.config.json, so we use a safe default.
  let baseURL = (() => {
    if (typeof window === 'undefined') return '';

    const isCapacitor =
      window.navigator.userAgent.includes('Capacitor') ||
      window.location.href.startsWith('capacitor://');

    if (!isCapacitor) return '';

    // Prefer an explicit backend URL if provided (via Capacitor config or by overriding baseURL at runtime).
    // If not provided, fall back to Android emulator alias (maps host localhost).
    // You can override this by calling API.setBaseURL('http://<host>:8000').
    const cfgUrl = (window?.CAPACITOR_BACKEND_URL || window?.process?.env?.CAPACITOR_BACKEND_URL) || '';
    if (cfgUrl) return cfgUrl;

    return 'http://10.0.2.2:8000';
  })();


  /**
   * Set the base URL for all API requests
   * @param {string} url - Base URL (e.g., 'http://localhost:8000' or empty for same-origin)
   */
  function setBaseURL(url) {
    baseURL = url;
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
    try {
      data = await response.json();
    } catch {
      throw new APIError('Invalid server response', 'PARSE_ERROR', response.status);
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

  function get(endpoint, skipAuth = false) {
    return request('GET', endpoint, null, !skipAuth);
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
    storeUser
  };
})();

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { API };
}