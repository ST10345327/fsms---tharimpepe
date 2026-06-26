<?php
$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

if (preg_match('#^/api(/.*)?$#', $path, $m)) {
    $_GET['endpoint'] = ltrim($m[1] ?? '', '/');
    require __DIR__ . '/api/index.php';
    return true;
}

if ($path === '/') {
    readfile(__DIR__ . '/www/index.html');
    return true;
}

$rootFile = __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, $path);
if (file_exists($rootFile) && !is_dir($rootFile)) {
    return false;
}

$wwwFile = __DIR__ . '/www' . str_replace('/', DIRECTORY_SEPARATOR, $path);
if (file_exists($wwwFile) && !is_dir($wwwFile)) {
    $ext = pathinfo($wwwFile, PATHINFO_EXTENSION);
    $mime = ['css' => 'text/css', 'js' => 'application/javascript', 'png' => 'image/png',
             'jpg' => 'image/jpeg', 'svg' => 'image/svg+xml', 'ico' => 'image/x-icon',
             'json' => 'application/json', 'woff2' => 'font/woff2'];
    if (isset($mime[$ext])) header('Content-Type: ' . $mime[$ext]);
    readfile($wwwFile);
    return true;
}

readfile(__DIR__ . '/www/index.html');
return true;
