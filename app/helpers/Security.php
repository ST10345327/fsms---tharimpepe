<?php
function isHttpsTransport()
{
    $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
    $isSecure = $https !== '' && $https !== 'off';
    $trustedProxies = array_filter(array_map('trim', explode(',', getenv('FSMS_TRUSTED_PROXIES') ?: '')));
    $remoteAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($remoteAddress !== '' && in_array($remoteAddress, $trustedProxies, true)
        && strtolower((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https') {
        $isSecure = true;
    }
    return $isSecure;
}

/** Enforce HTTPS before application pages can process credentials or session data. */
function requireHttpsTransport($jsonResponse = false)
{
    // CLI tools and unit tests do not carry network traffic.
    if (PHP_SAPI === 'cli' && empty($_SERVER['HTTP_HOST'])) {
        return;
    }

    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $isLoopback = preg_match('/^(localhost|127\.0\.0\.1)(:\d+)?$/', $host) === 1;
    if (isHttpsTransport() || ($isLoopback && getenv('FSMS_ALLOW_HTTP_LOCAL') === '1')) {
        return;
    }

    http_response_code(426);
    if ($jsonResponse) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'HTTPS is required for API traffic']);
    } else {
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'HTTPS is required to access this application.';
    }
    exit();
}