<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "MySQL connection OK\n";
} catch (Exception $e) {
    echo "MySQL not available: " . $e->getMessage() . "\n";
}