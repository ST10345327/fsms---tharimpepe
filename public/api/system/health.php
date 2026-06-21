<?php
header('Content-Type: application/json');

echo json_encode([
    "status"  => "ok",
    "app"     => "FSMS",
    "version" => "1.0",
    "time"    => date('c')
]);