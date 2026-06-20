<?php
echo "1. Starting test\n";
flush();

echo "2. About to require bootstrap\n";
flush();

require_once __DIR__ . '/../app/helpers/bootstrap.php';

echo "3. Bootstrap loaded\n";
flush();

echo "4. Checking session\n";
flush();

if (isUserLoggedIn()) {
    echo "5. User is logged in\n";
} else {
    echo "5. User is not logged in\n";
}

echo "6. Test complete\n";
flush();
?>
