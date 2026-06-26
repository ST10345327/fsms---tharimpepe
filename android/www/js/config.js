const FSMS_API_BASE = (() => {
    const origin = window.location.origin;
    if (origin.startsWith('http://10.0.2.2') || origin.startsWith('http://192.168') || origin.startsWith('http://172')) {
        return origin + '/api';
    }
    if (origin.startsWith('capacitor') || origin.startsWith('file')) {
        return 'http://10.0.2.2:8080/api';
    }
    return origin + '/api';
})();
