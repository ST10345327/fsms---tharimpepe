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
            grid-template-columns: minmax(0, 1.16fr) minmax(420px, 0.84fr);
            min-height: 100vh;
        }

        .login-mission {
            background: #1b3a5c;
            color: #fff;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .login-slide {
            inset: 0;
            opacity: 0;
            position: absolute;
            transition: opacity 700ms ease, visibility 0s linear 700ms;
            visibility: hidden;
        }

        .login-slide.is-active {
            opacity: 1;
            transition-delay: 0s;
            visibility: visible;
            z-index: 1;
        }

        .login-slide img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        .login-mission::after {
            background: linear-gradient(180deg, rgba(9, 25, 31, .3), transparent 36%, rgba(9, 25, 31, .64));
            content: "";
            inset: 0;
            pointer-events: none;
            position: absolute;
            z-index: 2;
        }

        .login-mission-brand {
            position: absolute;
            z-index: 3;
        }

        .login-mission-brand {
            align-items: center;
            display: flex;
            gap: 12px;
            left: 36px;
            top: 30px;
        }

        .login-mission-brand img {
            background: rgba(255,255,255,.92);
            border-radius: 10px;
            height: 44px;
            object-fit: contain;
            padding: 4px;
            width: 58px;
        }

        .login-mission-brand strong,
        .login-mission-brand span { display: block; }
        .login-mission-brand strong { font-size: 15px; }
        .login-mission-brand span { color: rgba(255,255,255,.82); font-size: 12px; margin-top: 2px; }

        @media (prefers-reduced-motion: reduce) {
            .login-slide { transition: none; }
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

        .quick-donate {
            display: block;
            background: #168447;
            border-radius: 8px;
            color: #fff !important;
            font-size: 15px !important;
            font-weight: 700;
            margin-top: 14px;
            padding: 12px;
            text-decoration: none !important;
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
                min-height: 320px;
            }

            .login-mission-brand { left: 24px; top: 22px; }

            .login-panel {
                min-height: 560px;
            }
        }

        @media (max-width: 520px) {
            .login-mission { min-height: 270px; }
            .login-mission-brand img { height: 38px; width: 50px; }
            .login-mission-brand { font-size: 13px; }
            .login-card {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <main class="login-shell">
        <!-- Keep this carousel image-led while retaining the organization logo and name. -->
        <section class="login-mission" aria-label="Tharimpepe community stories" data-login-carousel>
            <div class="login-slide is-active" data-slide aria-hidden="false">
                <img src="/assets/images/login-carousel/catering.jpeg" alt="Community volunteers preparing fresh vegetables together" fetchpriority="high">
            </div>
            <div class="login-slide" data-slide aria-hidden="true">
                <img src="/assets/images/login-carousel/donation.jpeg" alt="A volunteer handing a grocery parcel to a community member" loading="lazy">
            </div>
            <div class="login-slide" data-slide aria-hidden="true">
                <img src="/assets/images/login-carousel/serving.jpg" alt="School children sharing a meal outdoors" loading="lazy">
            </div>
            <div class="login-mission-brand" aria-hidden="true">
                <img src="/assets/images/fsmslogo.png" alt="" width="58" height="44">
                <div><strong>Tharimpepe</strong><span>Feeding Scheme</span></div>
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

                <form method="POST" action="/index.php?action=login">
                    <?php echo csrfTokenInput(); ?>
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
                    <a href="/index.php?action=register">Don't have an account? Register here</a>
                    <a class="quick-donate" href="/donate.php"><i class="fas fa-hand-holding-heart me-2" aria-hidden="true"></i>Quick Donate as Guest</a>
                </div>
            </div>

            <div class="login-footer">
                © 2026 Tharimpepe Feeding Scheme. All rights reserved.
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const carousel = document.querySelector('[data-login-carousel]');
            if (!carousel) return;

            const slides = Array.from(carousel.querySelectorAll('[data-slide]'));
            let current = 0;
            let timer = null;

            const showSlide = (index) => {
                current = (index + slides.length) % slides.length;
                slides.forEach((slide, i) => {
                    const active = i === current;
                    slide.classList.toggle('is-active', active);
                    slide.setAttribute('aria-hidden', String(!active));
                });
            };

            const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
            const start = () => {
                stop();
                if (!document.hidden && slides.length > 1) {
                    timer = window.setInterval(() => showSlide(current + 1), 6000);
                }
            };
            document.addEventListener('visibilitychange', () => start());
            showSlide(0);
            start();

            const loginForm = document.querySelector('.login-card form');
            const loginButton = loginForm?.querySelector('[type="submit"]');
            if (loginForm && loginButton) {
                loginForm.addEventListener('submit', (event) => {
                    if (loginForm.dataset.submitting === 'true') {
                        event.preventDefault();
                        return;
                    }
                    loginForm.dataset.submitting = 'true';
                    loginButton.disabled = true;
                    loginButton.setAttribute('aria-busy', 'true');
                    loginButton.textContent = 'Signing in...';
                });
            }
        })();
    </script>
</body>
</html>
