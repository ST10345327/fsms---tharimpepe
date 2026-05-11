<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FSMS Login - Tharimpepe Feeding Scheme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --color-text-primary: #1f2937;
            --color-text-secondary: #667085;
            --color-background-primary: #ffffff;
            --color-background-secondary: #f8fafc;
            --color-background-tertiary: #f3f6f8;
            --color-border-secondary: #cfd8e3;
            --color-border-tertiary: #dde5ec;
            --color-green-primary: #1D9E75;
            --color-green-dark: #0F6E56;
            --border-radius-md: 10px;
            --border-radius-lg: 16px;
        }

        * { 
            box-sizing: border-box; 
            margin: 0; 
            padding: 0; 
        }

        body {
            font-family: 'Segoe UI', 'Aptos', 'Helvetica Neue', Arial, sans-serif;
            background: 
                radial-gradient(circle at top left, rgba(29, 158, 117, 0.08), transparent 24%),
                linear-gradient(180deg, #f7faf8 0%, #eef3f6 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-text-primary);
            padding: 20px;
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .login-container {
            background: var(--color-background-primary);
            border: 0.5px solid var(--color-border-tertiary);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 24px 60px rgba(31, 41, 55, 0.08);
            padding: 48px 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: var(--color-text-primary);
            margin-bottom: 8px;
        }

        .login-header p {
            font-size: 14px;
            color: var(--color-text-secondary);
            margin: 0;
        }

        .form-group {
            margin-bottom: 16px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 12px;
            font-weight: 600;
            color: var(--color-text-secondary);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            color: var(--color-text-secondary);
            font-size: 16px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px 10px 40px;
            border: 0.5px solid var(--color-border-secondary);
            border-radius: var(--border-radius-md);
            font-size: 14px;
            background: var(--color-background-primary);
            color: var(--color-text-primary);
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-green-primary);
            box-shadow: 0 0 0 2px rgba(29, 158, 117, 0.1);
        }

        .form-control::placeholder {
            color: var(--color-text-secondary);
        }

        .btn-login {
            width: 100%;
            padding: 10px 16px;
            margin-top: 8px;
            background: var(--color-green-primary);
            color: white;
            border: none;
            border-radius: var(--border-radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .btn-login:hover {
            background: var(--color-green-dark);
            box-shadow: 0 4px 12px rgba(29, 158, 117, 0.3);
        }

        .btn-login:active {
            transform: translateY(1px);
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 0.5px solid var(--color-border-tertiary);
        }

        .form-footer a {
            color: var(--color-green-primary);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .form-footer a:hover {
            color: var(--color-green-dark);
        }

        .login-footer {
            text-align: center;
            margin-top: 32px;
            font-size: 12px;
            color: var(--color-text-secondary);
        }

        .alert-error {
            background-color: #fcebeb;
            border: 0.5px solid #f09595;
            color: #a32d2d;
            border-radius: var(--border-radius-md);
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .logout-success {
            background-color: #e1f5ee;
            border: 0.5px solid #5dcaa5;
            color: #0f6e56;
            border-radius: var(--border-radius-md);
            padding: 12px 14px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        @media (max-width: 600px) {
            .login-container {
                padding: 40px 24px;
            }

            .login-header h2 {
                font-size: 24px;
            }

            .form-control {
                font-size: 16px;
            }

            .btn-login {
                padding: 12px 16px;
                font-size: 14px;
            }
        }

        @media (max-width: 400px) {
            body {
                padding: 12px;
            }

            .login-container {
                padding: 32px 16px;
            }

            .login-header h2 {
                font-size: 22px;
            }

            .login-header p {
                font-size: 13px;
            }
        }
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            border-radius: 5px;
            padding: 12px 15px;
            margin-bottom: 20px;
        }
</head>
<body>
    <div class="login-wrapper">
        <div class="login-container">
            <!-- HZ-UI-LOGIN-001: Login Form Header -->
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access the admin portal</p>
            </div>

            <!-- Display logout success message if session ended -->
            <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                <div class="alert logout-success">
                    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>
                    You have been logged out successfully.
                </div>
            <?php endif; ?>

            <!-- Display error messages -->
            <?php if (!empty($error)): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- HZ-UI-LOGIN-002: Login Form -->
            <form method="POST" action="../controllers/AuthController.php?action=login" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
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
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
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
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>

            <!-- HZ-UI-LOGIN-003: Additional Links -->
            <div class="form-footer">
                <a href="#forgot-password">Forgot password?</a>
            </div>
        </div>

        <!-- HZ-UI-LOGIN-004: Footer -->
        <div class="login-footer">
            © 2026 Tharimpepe Feeding Scheme. All rights reserved.
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>