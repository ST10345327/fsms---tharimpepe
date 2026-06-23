/**
 * Runtime Configuration for FSMS Mobile App
 *
 * Supports dev/staging/prod environments.
 * Allows changing the API base URL at runtime without rebuilding the APK.
 * The value is persisted in localStorage so it survives app restarts.
 */

const ENVIRONMENTS = {
  dev: {
    API_BASE_URL: 'http://10.0.2.2:8000',
    WS_URL: '',
    DEBUG: true,
    MOCK_DELAY: 0,
    SYNC_INTERVAL: 5000,
    FEATURES: {
      notifications: true,
      offlineQueue: true,
      analytics: false,
      betaFeatures: true
    }
  },
  staging: {
    API_BASE_URL: 'http://10.0.2.2:8000',
    WS_URL: '',
    DEBUG: true,
    MOCK_DELAY: 0,
    SYNC_INTERVAL: 5000,
    FEATURES: {
      notifications: true,
      offlineQueue: true,
      analytics: false,
      betaFeatures: true
    }
  },
  prod: {
    API_BASE_URL: 'http://192.168.18.47:8000',
    WS_URL: '',
    DEBUG: false,
    MOCK_DELAY: 0,
    SYNC_INTERVAL: 30000,
    FEATURES: {
      notifications: true,
      offlineQueue: true,
      analytics: true,
      betaFeatures: false
    }
  }
};

const DEFAULT_ENV = 'prod';

function getEnvironment() {
  try {
    const saved = localStorage.getItem('FSMS_ENV');
    if (saved && ENVIRONMENTS[saved]) return saved;
  } catch (e) {
    // ignore
  }
  return DEFAULT_ENV;
}

const RuntimeConfig = (() => {
  let currentEnv = getEnvironment();
  let config = { ...ENVIRONMENTS[currentEnv] };
  let userApiBase = null;

  const listeners = [];

  function notifyChange(key, value) {
    listeners.forEach(fn => { try { fn(key, value); } catch (e) { /* no-op */ } });
  }

  function getConfig() {
    return {
      env: currentEnv,
      ...config,
      API_BASE_URL: userApiBase || config.API_BASE_URL
    };
  }

  function getAPIBase() {
    try {
      const saved = localStorage.getItem('API_BASE_URL');
      if (saved) {
        userApiBase = saved;
        return saved;
      }
    } catch (e) {
      console.warn('Failed to read API_BASE_URL from localStorage, using default:', e);
    }
    return userApiBase || config.API_BASE_URL;
  }

  function setAPIBase(url) {
    userApiBase = url;
    try {
      if (url) {
        localStorage.setItem('API_BASE_URL', url);
      } else {
        localStorage.removeItem('API_BASE_URL');
      }
    } catch (e) {
      console.error('Failed to save API_BASE_URL to localStorage:', e);
    }
    notifyChange('API_BASE_URL', url);
  }

  function resetAPIBase() {
    userApiBase = null;
    try {
      localStorage.removeItem('API_BASE_URL');
    } catch (e) {
      console.error('Failed to remove API_BASE_URL from localStorage:', e);
    }
    notifyChange('API_BASE_URL', null);
  }

  function getEnvironmentName() {
    return currentEnv;
  }

  function setEnvironment(env) {
    if (!ENVIRONMENTS[env]) {
      throw new Error(`Unknown environment: ${env}`);
    }
    currentEnv = env;
    config = { ...ENVIRONMENTS[env] };
    try {
      localStorage.setItem('FSMS_ENV', env);
    } catch (e) {
      console.error('Failed to save FSMS_ENV:', e);
    }
    notifyChange('ENV', env);
  }

  function isFeatureEnabled(feature) {
    return !!config.FEATURES[feature];
  }

  function isDebug() {
    return !!config.DEBUG;
  }

  function getSyncInterval() {
    return config.SYNC_INTERVAL;
  }

  function onChange(fn) {
    if (typeof fn === 'function') listeners.push(fn);
  }

  return {
    getConfig,
    getAPIBase,
    setAPIBase,
    resetAPIBase,
    getEnvironmentName,
    setEnvironment,
    isFeatureEnabled,
    isDebug,
    getSyncInterval,
    onChange,
    ENVIRONMENTS
  };
})();

window.RuntimeConfig = RuntimeConfig;
