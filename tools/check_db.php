<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';

try {
    echo "DBG: Before getDBConnection()\n";
    $db = getDBConnection();
    echo "DBG: After getDBConnection()\n";
    if ($db instanceof PDO) {
        echo "OK: PDO connection established\n";
    } else {
        echo "NO: Connection not PDO\n";
    }
} catch (Exception $e) {
    echo "ERR: " . $e->getMessage() . "\n";
}

?>