const FSMS_API_BASE = (() => {
    const origin = window.location.origin;
    // Android emulator: loaded via server.url from PHP dev server
    if (origin.includes('10.0.2.2') || origin.includes('192.168') || origin.includes('172.')) {
        return origin.replace(/\/+$/, '') + '/api';
    }
    // Production Android: loaded from local assets, API on host
    if (origin.startsWith('file') || origin.startsWith('capacitor')) {
        return 'http://10.0.2.2:8080/api';
    }
    // Browser dev: same origin as the page
    return origin.replace(/\/+$/, '') + '/api';
})();
