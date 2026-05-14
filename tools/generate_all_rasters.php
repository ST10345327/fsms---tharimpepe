<?php
// CLI utility: generate PNG fallbacks for all SVGs in public/assets/images
// Usage: php tools/generate_all_rasters.php

$dir = __DIR__ . '/../public/assets/images';
$cacheDir = $dir . '/cache';
if (!is_dir($cacheDir)) mkdir($cacheDir, 0777, true);

$svgs = glob($dir . '/*.svg');
if (!$svgs) {
    echo "No SVGs found in $dir\n";
    exit(0);
}

$sizes = [
    ['w'=>58,'h'=>0,'dpr'=>1],
    ['w'=>120,'h'=>0,'dpr'=>1],
    ['w'=>172,'h'=>48,'dpr'=>1],
    ['w'=>200,'h'=>96,'dpr'=>1],
    ['w'=>344,'h'=>96,'dpr'=>2],
    ['w'=>400,'h'=>192,'dpr'=>2],
];

foreach ($svgs as $svgPath) {
    $name = pathinfo($svgPath, PATHINFO_FILENAME);
    echo "Processing $name...\n";

    foreach ($sizes as $s) {
        $targetW = (int)$s['w'] * (int)$s['dpr'];
        $targetH = $s['h'] ? (int)$s['h'] * (int)$s['dpr'] : 0;
        $cacheFile = $cacheDir . '/' . $name . '-' . $targetW . 'x' . ($targetH?:'auto') . '@' . $s['dpr'] . '.png';
        if (file_exists($cacheFile)) {
            echo "  Exists: " . basename($cacheFile) . "\n";
            continue;
        }
        // Try PHP Imagick first
        if (extension_loaded('imagick')) {
            try {
                $svg = file_get_contents($svgPath);
                $im = new Imagick();
                $density = max(96, 72 * (int)$s['dpr']);
                $im->setOption('density', (string)$density);
                $im->readImageBlob($svg);
                $im->setImageFormat('png32');

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
                echo "  Created: " . basename($cacheFile) . "\n";
                $im->clear();
                $im->destroy();
                continue;
            } catch (Exception $e) {
                echo "  Imagick error for " . basename($cacheFile) . ": " . $e->getMessage() . "\n";
            }
        }

        // Fall back to ImageMagick CLI `magick` if available
        $magickAvailable = false;
        $whereMagick = trim(shell_exec('where magick 2>NUL')) ?: trim(shell_exec('which magick 2>/dev/null'));
        if (!empty($whereMagick)) $magickAvailable = true;
        else {
            // try a simple version probe
            $ver = trim(shell_exec('magick -version 2>&1'));
            if (stripos($ver, 'ImageMagick') !== false) $magickAvailable = true;
        }

        if ($magickAvailable) {
            $tmpSvg = tempnam(sys_get_temp_dir(), 'svg_') . '.svg';
            file_put_contents($tmpSvg, file_get_contents($svgPath));
            $resizeArg = $targetH > 0 ? ($targetW . 'x' . $targetH) : $targetW;
            $cmd = sprintf('magick %s -background none -resize %s %s', escapeshellarg($tmpSvg), escapeshellarg($resizeArg), escapeshellarg($cacheFile));
            exec($cmd . ' 2>&1', $out, $rc);
            @unlink($tmpSvg);
            if ($rc === 0 && file_exists($cacheFile)) {
                echo "  Created (magick): " . basename($cacheFile) . "\n";
                continue;
            } else {
                echo "  Magick failed for " . basename($cacheFile) . "\n";
            }
        } else {
            echo "  Imagick not installed and magick CLI not available — skipping " . basename($cacheFile) . "\n";
        }
    }
}

echo "Done. Check public/assets/images/cache for generated PNGs.\n";
