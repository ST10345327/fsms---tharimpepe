<?php
$host = '127.0.0.1';
$port = 3306;
$user = 'root';
$pass = '';
$timeout = 3;

$start = microtime(true);
$socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($socket) {
    fclose($socket);
    echo "Port open: ".round(microtime(true)-$start,2)."s\n";
} else {
    echo "Port closed: $errstr ($errno)\n";
}

$start = microtime(true);
$link = @mysqli_connect($host, $user, $pass, null, $port);
if ($link) {
    echo "mysqli OK: ".round(microtime(true)-$start,2)."s\n";
    mysqli_close($link);
} else {
    echo "mysqli FAIL: ".mysqli_connect_error()." (".round(microtime(true)-$start,2)."s)\n";
}