<?php
$url = 'http://127.0.0.1:8000/api/auth/login.php';
$data = ['username' => 'admin', 'password' => 'admin123'];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Response Status: " . $http_response_header[0] . "\n";
echo "Response Body: " . $result . "\n";
