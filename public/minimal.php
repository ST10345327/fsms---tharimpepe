<?php
// Absolute minimal response - no includes
header('Content-Type: text/html; charset=utf-8');
echo '<!DOCTYPE html><html><head><title>Test</title></head><body><h1>PHP Server Working</h1><p>Time: ' . date('H:i:s') . '</p></body></html>';