<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
require_once __DIR__ . '/app/helpers/bootstrap.php';
try {
    $db = getDBConnection();
    echo "Columns in Volunteers table:\n";
    $q = $db->query("DESCRIBE Volunteers");
    while ($row = $q->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
