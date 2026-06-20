<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
$start = microtime(true);
try {
    $db = getDBConnection();
    $elapsed = round(microtime(true) - $start, 2);
    echo "Connected in {$elapsed}s\n";
} catch (Exception $e) {
    $elapsed = round(microtime(true) - $start, 2);
    echo "Failed in {$elapsed}s: " . $e->getMessage() . "\n";
}
