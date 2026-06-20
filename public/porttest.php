<?php
$start = microtime(true);
$host = '127.0.0.1';
$port = 3306;
$fp = @fsockopen($host, $port, $errno, $errstr, 5);
$elapsed = round(microtime(true) - $start, 2);
if ($fp) {
    echo "TCP connected in {$elapsed}s\n";
    fclose($fp);
} else {
    echo "TCP failed in {$elapsed}s: $errstr ($errno)\n";
}
