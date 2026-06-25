<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FSMS Login - Tharimpepe Feeding Scheme</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/fsms-ui.css">
    <style>
        body {
            background: #f3f6fa;
            color: #071326;
            font-family: Inter, Arial, sans-serif;
            min-height: 100vh;
        }

        .login-shell {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 100vh;
        }

        .login-mission {
            align-items: center;
            background: #1b3a5c;
            color: #fff;
            display: flex;
            justify-content: center;
            padding: 48px;
            text-align: center;
        }

        .login-mission-inner {
            max-width: 520px;
        }

        .login-logo {
            display: block;
            height: 96px;
            margin: 0 auto 28px;
            object-fit: contain;
        }

        .login-mission h1 {
            font-size: 36px;
            font-weight: 700;
            line-height: 1.12;
            margin: 0 auto 24px;
            max-width: 420px;
        }

        .login-mission .system-name {
            color: #fff;
            font-size: 18px;
            margin-bottom: 28px;
        }

        .login-mission .mission-copy {
            color: rgba(255, 255, 255, 0.76);
            font-size: 16px;
            line-height: 1.55;
            margin: 0;
        }

        .login-panel {
            align-items: center;
            background: #f4f7fb;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 48px 32px;
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 16px 28px rgba(17, 24, 39, 0.12);
            max-width: 448px;
            padding: 36px 32px;
            width: 100%;
        }

        .login-header {
            margin-bottom: 28px;
            text-align: center;
        }

        .login-header h2 {
            color: #071326;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .login-header p {
            color: #4b5563;
            font-size: 14px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-group label {
            color: #111827;
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 9px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            color: #94a3b8;
            font-size: 18px;
            left: 16px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
        }

        .login-card .form-control {
            border: 1px solid #cfd6e1;
            border-radius: 10px;
            font-size: 16px;
            min-height: 50px;
            padding: 12px 16px 12px 42px;
        }

        .login-card .form-control::placeholder {
            color: #8da0ba;
        }

        .btn-login {
            background: #1b3a5c;
            border: 0;
            border-radius: 8px;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            margin-top: 6px;
            min-height: 48px;
            width: 100%;
        }

        .btn-login:hover {
            background: #2e4a6c;
        }

        .form-footer {
            margin-top: 26px;
            text-align: center;
        }

        .form-footer a {
            color: #1b3a5c;
            font-size: 14px;
            text-decoration: none;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }

        .login-footer {
            color: #46566b;
            font-size: 12px;
            margin-top: 26px;
            text-align: center;
        }

        .alert-error,
        .logout-success {
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            padding: 11px 12px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .logout-success {
            background: #ecfdf5;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        @media (max-width: 900px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-mission {
                min-height: 430px;
            }
        }

        @media (max-width: 520px) {
            .login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <section class="login-mission" aria-label="Tharimpepe mission">
            <div class="login-mission-inner">
                 <img class="login-logo" src="/assets/images/generate_raster.php?name=tharimpepe-logo&w=200&h=96&dpr=1"
                     srcset="/assets/images/generate_raster.php?name=tharimpepe-logo&w=200&h=96&dpr=1 1x, /assets/images/generate_raster.php?name=tharimpepe-logo&w=400&h=192&dpr=2 2x"
                     sizes="(max-width:640px) 120px, 200px"
                     alt="Tharimpepe" loading="lazy" width="200" height="96">
                <h1>Tharimpepe Feeding Scheme</h1>
                <p class="system-name">Feeding Scheme Management System</p>
                <p class="mission-copy">
                    Empowering communities through efficient meal distribution and volunteer coordination
                </p>
            </div>
        </section>

        <section class="login-panel" aria-label="Sign in">
            <div class="login-card">
                <div class="login-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to access the admin portal</p>
                </div>

                <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
                    <div class="logout-success" role="status">
                        <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                        You have been logged out successfully.
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert-error" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="index.php?action=login">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-wrapper">
                            <i class="far fa-user input-icon" aria-hidden="true"></i>
                            <input
                                type="text"
                                class="form-control"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                required
                                autocomplete="username"
                            >
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon" aria-hidden="true"></i>
                            <input
                                type="password"
                                class="form-control"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                                autocomplete="current-password"
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn-login">Login</button>
                </form>

                <div class="form-footer">
                    <a href="../controllers/AuthController.php?action=register">Don't have an account? Register here</a>
                </div>
            </div>

            <div class="login-footer">
                © 2026 Tharimpepe Feeding Scheme. All rights reserved.
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
