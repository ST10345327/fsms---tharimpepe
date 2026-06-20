<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/helpers/db.php';
require_once __DIR__ . '/../app/models/Attendance.php';

$pdo = getDBConnection();
if (!$pdo) {
    die('DB connection failed');
}
$attendance = new Attendance($pdo);
try {
    $data = $attendance->getAllAttendance();
    echo '<pre>'; print_r($data); echo '</pre>';
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
