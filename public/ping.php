<?php
// ping test
header('Content-Type: application/json');
echo json_encode([
  'status' => 'ok',
  'message' => 'pong',
  'timestamp' => date('c')
]);
