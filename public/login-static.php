<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1b3a5c">
    <title>Login - Tharimpepe FSMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f3f6fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); width: 100%; max-width: 320px; }
        .login-header { text-align: center; margin-bottom: 20px; }
        .login-header h1 { color: #1b3a5c; font-size: 24px; margin: 0; }
        .form-control { margin-bottom: 12px; }
        .btn-login { background: #1b3a5c; border: 0; width: 100%; padding: 12px; }
        .alert { margin-bottom: 16px; padding: 10px; border-radius: 6px; font-size: 14px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
        .hint { font-size: 12px; color: #64748b; text-align: center; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h1>Tharimpepe FSMS</h1>
        </div>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="../controllers/AuthController.php?action=login">
            <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="username">
            <input type="password" name="password" class="form-control" placeholder="Password" required autocomplete="current-password">
            <button type="submit" class="btn btn-login btn-primary">Login</button>
        </form>
        <p class="hint">Demo users: admin/admin123, volunteer/vol123</p>
    </div>
</body>
</html>