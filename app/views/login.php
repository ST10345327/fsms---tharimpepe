<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FSMS Login - Tharimpepe Feeding Scheme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #333;
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 5px;
        }
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 5px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            padding: 12px 20px;
            font-weight: 600;
            width: 100%;
            transition: transform 0.2s ease;
        }
        .btn-login:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .alert-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
        .form-footer {
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }
        .form-footer p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .form-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .logout-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- HZ-UI-LOGIN-001: Login Form Header -->
        <div class="login-header">
            <h1>FSMS Login</h1>
            <p>Tharimpepe Feeding Scheme Management System</p>
        </div>

        <!-- Display logout success message if session ended -->
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="alert logout-success">
                You have been logged out successfully.
            </div>
        <?php endif; ?>

        <!-- Display error messages -->
        <?php if (!empty($error)): ?>
            <div class="alert-error">
                <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- HZ-UI-LOGIN-002: Login Form -->
        <form method="POST" action="../controllers/AuthController.php?action=login" class="login-form">
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="username" 
                    name="username" 
                    placeholder="Enter your username"
                    required
                    autocomplete="username"
                />
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                    autocomplete="current-password"
                />
            </div>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <!-- HZ-UI-LOGIN-003: Registration Link -->
        <div class="form-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>