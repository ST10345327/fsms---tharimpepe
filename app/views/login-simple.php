<?php
// Minimal login test - no external dependencies
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Login</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f7fa; }
        .form-box { background: #fff; padding: 20px; border-radius: 8px; max-width: 400px; margin: 40px auto; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ccc; border-radius: 4px; }
        button { width: 100%; padding: 12px; background: #1b3a5c; color: white; border: 0; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="form-box">
        <h2>Login Test</h2>
        <form method="POST" action="/controllers/AuthController.php?action=login">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>