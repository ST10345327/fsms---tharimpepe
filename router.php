<?php
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';
$publicRoot = __DIR__ . '/public';

if (preg_match('#^/api(?:/.*)?$#', $path, $m)) {
    $_GET['endpoint'] = ltrim(substr($path, 4), '/');
    require __DIR__ . '/api/index.php';
    return true;
}

$publicFile = $publicRoot . str_replace('/', DIRECTORY_SEPARATOR, $path);
$extension = strtolower(pathinfo($publicFile, PATHINFO_EXTENSION));
$resolvedPublicRoot = realpath($publicRoot);
$resolvedPublicFile = realpath($publicFile);

// Let PHP's built-in server execute real PHP pages (for example donate.php)
// instead of routing them through index.php and showing the staff login page.
if ($extension === 'php' && $resolvedPublicRoot && $resolvedPublicFile && is_file($resolvedPublicFile)) {
    $publicRootPrefix = rtrim($resolvedPublicRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (strncasecmp($resolvedPublicFile, $publicRootPrefix, strlen($publicRootPrefix)) === 0) {
        return false;
    }
}

$mimeTypes = [
    'css' => 'text/css; charset=UTF-8',
    'js' => 'application/javascript; charset=UTF-8',
    'json' => 'application/json; charset=UTF-8',
    'png' => 'image/png',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif' => 'image/gif',
    'svg' => 'image/svg+xml',
    'ico' => 'image/x-icon',
    'woff' => 'font/woff',
    'woff2' => 'font/woff2',
];
if (
    $path !== '/' &&
    $extension !== 'php' &&
    file_exists($publicFile) && !is_dir($publicFile) &&
    isset($mimeTypes[$extension])
) {
    header('Content-Type: ' . $mimeTypes[$extension]);
    readfile($publicFile);
    return true;
}

require $publicRoot . '/index.php';
return true;
