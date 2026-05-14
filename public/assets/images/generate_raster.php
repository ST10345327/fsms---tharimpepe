<?php
// Simple raster generator for SVG logos with caching.
// Usage: generate_raster.php?name=tharimpepe-logo&w=172&h=48

$allowed = ['tharimpepe-logo'];
$name = preg_replace('/[^a-z0-9\-]/i', '', ($_GET['name'] ?? 'tharimpepe-logo'));
if (!in_array($name, $allowed)) {
    http_response_code(404);
    exit('Not found');
}
$w = isset($_GET['w']) ? (int)$_GET['w'] : 0;
$h = isset($_GET['h']) ? (int)$_GET['h'] : 0;
$dpr = isset($_GET['dpr']) ? (int)$_GET['dpr'] : 1;
if ($dpr < 1) $dpr = 1;
if ($w <= 0 && $h <= 0) { $w = 200; $h = 64; }
$targetW = $w * $dpr;
$targetH = $h > 0 ? $h * $dpr : 0;

$baseDir = __DIR__;
$svgPath = $baseDir . '/' . $name . '.svg';
$cacheDir = $baseDir . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);
$cacheFile = $cacheDir . '/' . $name . '-' . $targetW . 'x' . ($targetH?:'auto') . '@' . $dpr . '.png';

// Serve cached if exists
if (file_exists($cacheFile)) {
    header('Content-Type: image/png');
    header('Cache-Control: public, max-age=31536000');
    readfile($cacheFile);
    exit();
}

// If Imagick is available, try to render SVG
if (extension_loaded('imagick')) {
    try {
        $svg = file_get_contents($svgPath);
        $im = new Imagick();
        // Set density to improve raster quality
        $density = max(96, 72 * $dpr);
        $im->setOption('density', (string)$density);
        $im->readImageBlob($svg);
        $im->setImageFormat('png32');
        // If target height is zero, preserve aspect ratio
        if ($targetH <= 0) {
            $origW = $im->getImageWidth();
            $origH = $im->getImageHeight();
            if ($origW > 0) {
                $targetH = (int) max(1, ($targetW * $origH) / $origW);
            } else {
                $targetH = $targetW;
            }
        }
        $im->resizeImage($targetW, $targetH, Imagick::FILTER_LANCZOS, 1, true);
        $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
        $im->writeImage($cacheFile);
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000');
        readfile($cacheFile);
        exit();
    } catch (Exception $e) {
        // fall through to other methods
    }
}

// If Imagick extension is not available, try the ImageMagick CLI `magick` if present
$magickPath = trim(shell_exec('where magick 2>NUL')) ?: trim(shell_exec('which magick 2>/dev/null'));
if (!$magickPath) {
    // try PATH lookup without capturing errors
    $magickPath = trim(shell_exec('magick -version 2>&1 | findstr "ImageMagick"')) ? 'magick' : '';
}
if (!empty($magickPath)) {
    // Build command to rasterize SVG using ImageMagick CLI
    $tmpSvg = tempnam(sys_get_temp_dir(), 'svg_') . '.svg';
    file_put_contents($tmpSvg, file_get_contents($svgPath));
    // If targetH is zero, let magick compute aspect ratio by specifying width only
    $resizeArg = $targetH > 0 ? escapeshellarg($targetW . 'x' . $targetH) : escapeshellarg($targetW);
    $cmd = sprintf('magick %s -background none -resize %s %s', escapeshellarg($tmpSvg), $resizeArg, escapeshellarg($cacheFile));
    // Execute and check result
    exec($cmd . ' 2>&1', $out, $rc);
    @unlink($tmpSvg);
    if ($rc === 0 && file_exists($cacheFile)) {
        header('Content-Type: image/png');
        header('Cache-Control: public, max-age=31536000');
        readfile($cacheFile);
        exit();
    }
}

// Fallback: serve original SVG directly
if (file_exists($svgPath)) {
    header('Content-Type: image/svg+xml');
    header('Cache-Control: public, max-age=3600');
    readfile($svgPath);
    exit();
}

http_response_code(404);
exit('Not found');
