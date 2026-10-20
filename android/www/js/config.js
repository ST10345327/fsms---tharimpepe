const FSMS_API_BASE = (() => {
    const origin = window.location.origin || '';
    const pathname = window.location.pathname || '/';
    const isNative = Boolean(window.Capacitor?.isNativePlatform?.()) || !origin || origin === 'null' || origin.startsWith('file') || origin.startsWith('capacitor');
    const configured = window.FSMS_API_BASE_URL || '';

    if (isNative) {
        const apiBase = configured || 'https://192.168.18.73:8080/api';
        if (!/^https:\/\//i.test(apiBase)) {
            throw new Error('Android API URL must use HTTPS. Configure window.FSMS_API_BASE_URL.');
        }
        return apiBase.replace(/\/+$/, '');
    }

    const projectRoot = pathname.split('?')[0].split('#')[0]
        .replace(/\/(?:index\.(?:html|php))?$/, '')
        .replace(/\/(?:public|www)$/, '').replace(/\/+$/, '');
    const secureOrigin = origin.replace(/^http:/i, 'https:').replace(/\/+$/, '');
    const apiBase = configured || secureOrigin + (projectRoot && projectRoot !== '/' ? projectRoot : '') + '/api';
    if (!/^https:\/\//i.test(apiBase)) {
        throw new Error('API URL must use HTTPS.');
    }
    return apiBase.replace(/\/+$/, '');
})();
