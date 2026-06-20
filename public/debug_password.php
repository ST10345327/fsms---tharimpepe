<?php
$hash = '$2y$10$k9XtUPRIFGvd1VtsmCgh3eIyXHWo0CWxNQt7S1l8tnA1MQrW/xn7W';
$passwords = ['admin','password','123456','admin123','test','letmein'];
foreach ($passwords as $pw) {
    $result = password_verify($pw, $hash) ? 'TRUE' : 'FALSE';
    echo "Testing '$pw': $result\n";
}
?>
