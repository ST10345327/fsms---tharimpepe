<?php
require_once __DIR__ . '/../app/helpers/bootstrap.php';
$db = getDBConnection();
echo $db ? "DB_OK\n" : "DB_FAIL\n";
