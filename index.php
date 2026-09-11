<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The MK Students — Learn. Connect. Achieve.</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fc;
            color: #333;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ================= TOP NAVBAR ================= */
        .top-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 60px;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(45, 58, 100, 0.06);
            border-bottom: 1px solid rgba(102, 126, 234, 0.08);
        }

        .top-nav .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 800;
            color: #17213c;
            text-decoration: none;
            letter-spacing: -0.3px;
        }
        .top-nav .brand-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 6px 15px rgba(102, 126, 234, 0.35);
        }

        .top-nav .nav-links {
            display: flex;
            gap: 34px;
            list-style: none;
        }
        .top-nav .nav-links a {
            color: #555;
            text-decoration: none;
            font-size: 14.5px;
            font-weight: 500;
            transition: 0.2s;
            position: relative;
        }
        .top-nav .nav-links a:hover {
            color: #667eea;
        }
        .top-nav .nav-links a::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -6px;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            transition: 0.3s;
        }
        .top-nav .nav-links a:hover::after {
            width: 100%;
        }

        /* ================= HERO WRAPPER ================= */
        .hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            max-width: 1250px;
            margin: 40px auto;
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 25px 70px rgba(45, 58, 100, 0.15);
            min-height: 640px;
            animation: fadeUp 0.7s ease both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(25px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ============ LEFT: FORM PANEL ============ */
        .form-panel {
            padding: 55px 55px;
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-panel h1 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            color: #17213c;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }
        .form-panel .subtitle {
            color: #888;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-container { display: none; }
        .form-container.active { display: block; }

        .form-group { margin-bottom: 18px; }
        .form-group label {
            display: block;
            margin-bottom: 7px;
            color: #555;
            font-weight: 500;
            font-size: 13.5px;
        }

        .input-wrap {
            position: relative;
        }
        .input-wrap .field-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 15px;
            color: #a0a8be;
            pointer-events: none;
            transition: 0.2s;
        }
        .input-wrap input,
        .input-wrap select {
            width: 100%;
            padding: 13px 15px 13px 44px;
            border: 2px solid #e1e5eb;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            background: #f8faff;
            color: #27324d;
            transition: 0.25s;
        }
        .input-wrap input:focus,
        .input-wrap select:focus {
            outline: none;
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }
        .input-wrap input:focus ~ .field-icon {
            color: #667eea;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .btn-login, .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.25s;
            margin-top: 8px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.25);
        }
        .btn-login:hover, .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(102, 126, 234, 0.35);
        }

        .register-link, .login-link {
            text-align: center;
            color: #666;
            font-size: 13.5px;
            margin-top: 20px;
        }
        .register-link a, .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }
        .register-link a:hover, .login-link a:hover {
            text-decoration: underline;
        }

        /* Social buttons */
        .social-divider {
            display: flex;
            align-items: center;
            margin: 22px 0 14px;
            color: #a0a8be;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .social-divider::before,
        .social-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e6ebf3;
        }
        .social-divider span { padding: 0 14px; }

        .social-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13.5px;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid #e1e5eb;
            background: #fff;
            color: #333;
            cursor: pointer;
            transition: 0.25s;
            font-family: inherit;
        }
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(45, 58, 100, 0.12);
            border-color: #d4d9e3;
        }
        .google-btn:hover { background: #fafafa; }
        .github-btn {
            background: #24292e;
            color: #fff;
            border-color: #24292e;
        }
        .github-btn:hover {
            background: #1b1f23;
            box-shadow: 0 8px 20px rgba(36, 41, 46, 0.35);
        }

        /* Alerts */
        .error-message {
            background: #fff1f1;
            color: #c0392b;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            border-left: 4px solid #c0392b;
            font-size: 13.5px;
        }
        .success-message {
            background: #edfff4;
            color: #155724;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            border-left: 4px solid #28a745;
            font-size: 13.5px;
        }

        /* ============ RIGHT: HERO PANEL ============ */
        .hero-panel {
            position: relative;
            background:
                linear-gradient(135deg, rgba(102, 126, 234, 0.88) 0%, rgba(118, 75, 162, 0.92) 100%),
                url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1200&q=80') center/cover no-repeat;
            padding: 55px 45px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }

        .hero-panel .crest {
            width: 78px;
            height: 78px;
            margin: 0 auto 22px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            backdrop-filter: blur(8px);
        }

        .hero-panel h2 {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 14px;
            line-height: 1.2;
            letter-spacing: -0.5px;
        }
        .hero-panel .tagline {
            font-size: 14.5px;
            line-height: 1.7;
            opacity: 0.92;
            max-width: 360px;
            margin: 0 auto 26px;
            font-weight: 300;
        }

        .features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 28px;
        }
        .feature-item {
            background: rgba(255, 255, 255, 0.16);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 9px 17px;
            border-radius: 30px;
            font-size: 12.5px;
            font-weight: 500;
            backdrop-filter: blur(8px);
            transition: 0.25s;
        }
        .feature-item:hover {
            background: rgba(255, 255, 255, 0.28);
            transform: translateY(-2px);
        }

        /* Trust badges */
        .trust {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding-top: 22px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        .avatars {
            display: flex;
        }
        .avatars span {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.9);
            margin-left: -10px;
            background-size: cover;
            background-position: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        .avatars span:first-child { margin-left: 0; }
        .avatar-1 { background-image: url('https://i.pravatar.cc/80?img=12'); }
        .avatar-2 { background-image: url('https://i.pravatar.cc/80?img=32'); }
        .avatar-3 { background-image: url('https://i.pravatar.cc/80?img=56'); }
        .avatar-4 { background-image: url('https://i.pravatar.cc/80?img=8');  }
        .trust-text {
            font-size: 12.5px;
            opacity: 0.9;
            font-weight: 500;
        }
        .trust-text strong { font-weight: 700; }

        /* ================= FOOTER ================= */
        .footer {
            text-align: center;
            padding: 26px 20px;
            color: #8a92a6;
            font-size: 13px;
        }
        .footer span { color: #667eea; font-weight: 600; }

        /* ============== RESPONSIVE ============== */
        @media (max-width: 980px) {
            .hero {
                grid-template-columns: 1fr;
                margin: 20px;
            }
            .hero-panel { order: -1; padding: 40px 30px; }
            .hero-panel h2 { font-size: 26px; }
            .form-panel { padding: 40px 30px; }
        }

        @media (max-width: 700px) {
            .top-nav { padding: 14px 22px; }
            .top-nav .nav-links { display: none; }
            .form-panel { padding: 32px 22px; }
            .form-panel h1 { font-size: 26px; }
            .form-row { grid-template-columns: 1fr; }
            .social-buttons { grid-template-columns: 1fr; }
            .hero-panel h2 { font-size: 24px; }
        }
    </style>
</head>
<body>

    <!-- ============ TOP NAVBAR ============ -->
    <nav class="top-nav">
        <a href="index.php" class="brand">
            <span class="brand-icon">🎓</span>
            The MK Students
        </a>
    </nav>

    <!-- ============ HERO ============ -->
    <div class="hero">

        <!-- LEFT: FORM PANEL -->
        <div class="form-panel">

            <!-- LOGIN FORM -->
            <div class="form-container <?php echo (!isset($_SESSION['active_form']) || $_SESSION['active_form'] === 'login') ? 'active' : ''; ?>" id="loginForm">
                <h1>Welcome Back</h1>
                <p class="subtitle">Sign in to continue your learning journey</p>

                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?></div>
                <?php endif; ?>

                <?php if (isset($_SESSION['register_success'])): ?>
                    <div class="success-message"><?php echo htmlspecialchars($_SESSION['register_success']); unset($_SESSION['register_success']); ?></div>
                <?php endif; ?>

                <form method="POST" action="login.php">
                    <div class="form-group">
                        <label>Email address</label>
                        <div class="input-wrap">
                            <input type="email" name="email" placeholder="you@example.com" required>
                            <span class="field-icon">✉️</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrap">
                            <input type="password" name="password" placeholder="Enter your password" required>
                            <span class="field-icon">🔒</span>
                        </div>
                    </div>

                    <button type="submit" name="login" class="btn-login">Login →</button>

                    <!-- SOCIAL -->
                    <div class="social-divider">
                        <span>Or continue with</span>
                    </div>
                    <div class="social-buttons">
                        <a href="google-login.php" class="social-btn google-btn">
                            <svg width="18" height="18" viewBox="0 0 48 48">
                                <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
                                <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
                                <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
                                <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
                            </svg>
                            <span>Google</span>
                        </a>
                        <a href="github-login.php" class="social-btn github-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/>
                            </svg>
                            <span>GitHub</span>
                        </a>
                    </div>
                </form>

                <div class="register-link">
                    Don't have an account? <a href="#" onclick="toggleForms()">Create Account</a>
                </div>
            </div>

            <!-- REGISTER FORM -->
            <div class="form-container <?php echo (isset($_SESSION['active_form']) && $_SESSION['active_form'] === 'register') ? 'active' : ''; ?>" id="registerForm">
                <h1>Create Account</h1>
                <p class="subtitle">Start your learning journey today</p>

                <?php if (isset($_SESSION['register_error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_SESSION['register_error']); unset($_SESSION['register_error']); ?></div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name</label>
                            <div class="input-wrap">
                                <input type="text" name="first_name" placeholder="First name" required>
                                <span class="field-icon">👤</span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Last Name</label>
                            <div class="input-wrap">
                                <input type="text" name="last_name" placeholder="Last name" required>
                                <span class="field-icon">👤</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-wrap">
                            <input type="email" name="email" placeholder="you@example.com" required>
                            <span class="field-icon">✉️</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-wrap">
                            <input type="password" name="password" placeholder="Min 8 characters" required>
                            <span class="field-icon">🔒</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <div class="input-wrap">
                            <input type="password" name="password_confirm" placeholder="Repeat password" required>
                            <span class="field-icon">🔒</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>I am a</label>
                        <div class="input-wrap">
                            <select name="role" required>
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                            <span class="field-icon">🎭</span>
                        </div>
                    </div>

                    <button type="submit" name="register" class="btn-register">Create Account →</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="#" onclick="toggleForms()">Sign In</a>
                </div>
            </div>

        </div>

        <!-- RIGHT: HERO PANEL -->
        <div class="hero-panel">
            <div class="crest">🎓</div>
            <h2>Continue your<br>learning journey</h2>
            <p class="tagline">
                Your digitized community sparks learning, collaboration, and success — all in one place.
            </p>

            <div class="features">
                <span class="feature-item">📚 Learn</span>
                <span class="feature-item">🤝 Connect</span>
                <span class="feature-item">🏆 Achieve</span>
                <span class="feature-item">📖 Courses</span>
                <span class="feature-item">👥 Classes</span>
                <span class="feature-item">✨ Success</span>
            </div>

            <div class="trust">
                <div class="avatars">
                    <span class="avatar-1"></span>
                    <span class="avatar-2"></span>
                    <span class="avatar-3"></span>
                    <span class="avatar-4"></span>
                </div>
                <div class="trust-text">
                    Trusted by <strong>2,000+</strong> students
                </div>
            </div>
        </div>

    </div>

    <!-- ============ FOOTER ============ -->
    <footer class="footer">
        © 2026 <span>The MK Students</span> · All rights reserved
    </footer>

    <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            loginForm.classList.toggle('active');
            registerForm.classList.toggle('active');
            document.querySelectorAll('.error-message, .success-message').forEach(el => el.remove());
        }

        <?php if (isset($_SESSION['register_error']) || (isset($_SESSION['active_form']) && $_SESSION['active_form'] === 'register')): ?>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('loginForm').classList.remove('active');
                document.getElementById('registerForm').classList.add('active');
            });
        <?php endif; ?>
    </script>
</body>
</html>