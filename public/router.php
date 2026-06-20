<?php
/**
 * Dev server router for the FSMS mobile test environment.
 *
 * Routes public assets normally, but also exposes app controllers and views
 * so the login flow works when the site is served with `php -S`.
 */

// Set timeout for debugging
set_time_limit(30);

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicRoot = __DIR__;
$appRoot = dirname(__DIR__) . '/app';

$publicPath = $publicRoot . $uri;
if ($uri !== '/' && is_file($publicPath)) {
    return false;
}

if (preg_match('#^/controllers/(.+\.php)$#', $uri, $matches)) {
    $target = $appRoot . '/controllers/' . $matches[1];
    if (is_file($target)) {
        try {
            require $target;
            return true;
        } catch (Throwable $e) {
            http_response_code(500);
            echo "Error: " . htmlspecialchars($e->getMessage());
            return true;
        }
    }
}

if (preg_match('#^/views/(.+\.php)$#', $uri, $matches)) {
    $target = $appRoot . '/views/' . $matches[1];
    if (is_file($target)) {
        try {
            require $target;
            return true;
        } catch (Throwable $e) {
            http_response_code(500);
            echo "Error loading view: " . htmlspecialchars($e->getMessage());
            return true;
        }
    }
}

if ($uri === '/' || $uri === '/index.php') {
    require $publicRoot . '/index.php';
    return true;
}

// Route all /api/ requests to the corresponding api/*.php file
if (preg_match('#^/api/(.+\.php)$#', $uri, $matches)) {
    $target = dirname(__DIR__) . '/api/' . $matches[1];
    if (is_file($target)) {
        try {
            require $target;
            return true;
        } catch (Throwable $e) {
            http_response_code(500);
            echo "Error: " . htmlspecialchars($e->getMessage());
            return true;
        }
    }
}

http_response_code(404);
echo 'Not Found';
return true;
