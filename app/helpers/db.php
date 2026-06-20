<?php
// app/helpers/db.php
// Provides a global function to obtain a PDO connection using existing config
require_once __DIR__ . '/../../config/database.php';
/**
 * Returns a PDO connection instance.
 * Uses the Database class defined in config/database.php.
 * @return PDO|null
 */
function getDBConnection(): ?PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $db = new Database();
    $pdo = $db->connect();
    return $pdo;
}
?>
