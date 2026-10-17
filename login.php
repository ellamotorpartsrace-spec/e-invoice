<?php
// login.php
// Electronic Invoicing Portal — Sign In Gateway
// ELLA MOTOR PARTS | Developed by: Lester Bucag
require_once __DIR__ . '/config/config.php';

if (isEinvLoggedIn()) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — ELLA MOTOR PARTS | BIR E-Invoice Portal</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-mark.png?v=<?= file_exists(__DIR__ . '/assets/img/logo-mark.png') ? filemtime(__DIR__ . '/assets/img/logo-mark.png') : '2.0' ?>">
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & FontAwesome 6 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --font-heading: 'Outfit', sans-serif;
            --font-body: 'Inter', system-ui, -apple-system, sans-serif;
            --racing-ribbon: linear-gradient(90deg, #e11d2a 0%, #ea580c 50%, #f59e0b 100%);
            --btn-racing-red: linear-gradient(180deg, #ed3524 0%, #db2212 100%);
        }

        /* ── Light Mode (Default) ── */
        [data-theme="light"] {
            --bg-page: #f8fafc;
            --bg-grid-color: rgba(148, 163, 184, 0.13);
            --bg-glow: none;
            
            --bg-card: #ffffff;
            --card-border: rgba(226, 232, 240, 0.85);
            --card-shadow: 0 22px 45px -12px rgba(15, 23, 42, 0.07), 0 4px 16px -2px rgba(15, 23, 42, 0.03);
            
            --text-title: #0f172a;
            --text-label: #334155;
            --text-muted: #94a3b8;
            --text-footer-main: #475569;
            --text-footer-bold: #1e293b;
            
            --badge-bg: #fef2f2;
            --badge-border: #fee2e2;
            --badge-text: #e11d2a;
            
            --input-bg: #f8fafc;
            --input-border: #e2e8f0;
            --input-border-hover: #cbd5e1;
            --input-text: #0f172a;
            --input-focus-bg: #ffffff;
            --input-focus-border: #ff705a;
            --input-focus-ring: rgba(255, 112, 90, 0.2);
            --input-icon: #94a3b8;
            --input-icon-focus: #ff5733;
            
            --toggle-bg: #ffffff;
            --toggle-border: #e2e8f0;
            --toggle-color: #475569;
            --toggle-hover-bg: #f1f5f9;
            --toggle-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            
            --logo-drop-shadow: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.04));
            --btn-shadow: 0 10px 22px -3px rgba(234, 43, 28, 0.48);
            --btn-shadow-hover: 0 14px 26px -3px rgba(234, 43, 28, 0.6);
        }

        /* ── Dark Mode ── */
        [data-theme="dark"] {
            --bg-page: #0b0d14;
            --bg-grid-color: rgba(255, 255, 255, 0.035);
            --bg-glow: radial-gradient(circle at 50% 38%, rgba(225, 29, 42, 0.14) 0%, rgba(234, 88, 12, 0.06) 42%, transparent 72%);
            
            --bg-card: #131622;
            --card-border: rgba(255, 255, 255, 0.08);
            --card-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.75), 0 0 1px 1px rgba(255, 255, 255, 0.05) inset;
            
            --text-title: #ffffff;
            --text-label: #cbd5e1;
            --text-muted: #64748b;
            --text-footer-main: #64748b;
            --text-footer-bold: #cbd5e1;
            
            --badge-bg: rgba(225, 29, 42, 0.14);
            --badge-border: rgba(225, 29, 42, 0.28);
            --badge-text: #fca5a5;
            
            --input-bg: #191d2c;
            --input-border: rgba(255, 255, 255, 0.1);
            --input-border-hover: rgba(255, 255, 255, 0.18);
            --input-text: #ffffff;
            --input-focus-bg: #191d2c;
            --input-focus-border: #ff705a;
            --input-focus-ring: rgba(255, 112, 90, 0.25);
            --input-icon: #64748b;
            --input-icon-focus: #ff5733;
            
            --toggle-bg: #191d2c;
            --toggle-border: rgba(255, 255, 255, 0.12);
            --toggle-color: #f1f5f9;
            --toggle-hover-bg: #22273b;
            --toggle-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
            
            --logo-drop-shadow: drop-shadow(0 4px 14px rgba(225, 29, 42, 0.35));
            --btn-shadow: 0 10px 24px -3px rgba(234, 43, 28, 0.55);
            --btn-shadow-hover: 0 14px 28px -3px rgba(234, 43, 28, 0.7);
        }

        body {
            font-family: var(--font-body);
            background-color: var(--bg-page);
            background-image: 
                var(--bg-glow),
                linear-gradient(to right, var(--bg-grid-color) 1px, transparent 1px),
                linear-gradient(to bottom, var(--bg-grid-color) 1px, transparent 1px);
            background-size: auto, 32px 32px, 32px 32px;
            background-attachment: fixed;
            color: var(--text-title);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 1.25rem;
            position: relative;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* ── Top-Right Theme Toggle Button ── */
        .theme-toggle-btn {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--toggle-bg);
            border: 1px solid var(--toggle-border);
            color: var(--toggle-color);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.05rem;
            box-shadow: var(--toggle-shadow);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 50;
        }

        .theme-toggle-btn:hover {
            background: var(--toggle-hover-bg);
            transform: scale(1.08) rotate(12deg);
        }

        .theme-toggle-btn:active {
            transform: scale(0.95);
        }

        /* ── Centered Card Wrapper ── */
        .login-wrapper {
            width: 100%;
            max-width: 440px;
            animation: slideUpFade 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUpFade {
            from { opacity: 0; transform: translateY(18px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── Floating Card ── */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--card-border);
            border-radius: 28px;
            padding: 2.5rem 2.5rem 2.5rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        /* Top Racing Gradient Ribbon */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--racing-ribbon);
        }

        /* ── Brand Header (Tightly Spaced) ── */
        .brand-header {
            text-align: center;
            margin-bottom: 1.65rem;
        }

        .logo-img-box {
            display: inline-block;
            margin-bottom: 0.65rem;
            transition: transform 0.25s ease;
        }

        .logo-img-box:hover {
            transform: scale(1.02);
        }

        .logo-img {
            max-width: 235px;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: var(--logo-drop-shadow);
            transition: filter 0.25s ease;
        }

        /* Portal Pill Badge (Snug below logo) */
        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: var(--badge-bg);
            color: var(--badge-text);
            border: 1px solid var(--badge-border);
            padding: 0.35rem 1rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            margin-bottom: 1rem;
            transition: all 0.25s ease;
        }

        .portal-badge i {
            color: var(--badge-text);
            font-size: 0.8rem;
        }

        .login-title {
            font-family: var(--font-heading);
            font-size: 1.7rem;
            font-weight: 800;
            color: var(--text-title);
            letter-spacing: -0.025em;
            margin-bottom: 0;
            line-height: 1.2;
            transition: color 0.25s ease;
        }

        /* ── Form Inputs ── */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-label);
            margin-bottom: 0.48rem;
            letter-spacing: 0.01em;
            transition: color 0.25s ease;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1.15rem;
            color: var(--input-icon);
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s ease;
            z-index: 3;
        }

        .form-control-custom {
            width: 100%;
            height: 50px;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 14px;
            padding: 0.7rem 1.15rem 0.7rem 2.95rem;
            color: var(--input-text);
            font-size: 0.92rem;
            font-family: inherit;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-control-custom.has-toggle {
            padding-right: 2.95rem;
        }

        .form-control-custom::placeholder {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .form-control-custom:hover {
            border-color: var(--input-border-hover);
        }

        .form-control-custom:focus {
            outline: none;
            background: var(--input-focus-bg);
            border: 2px solid var(--input-focus-border);
            box-shadow: 0 0 0 4px var(--input-focus-ring);
            color: var(--input-text);
        }

        .form-control-custom:focus + .input-icon,
        .input-box:focus-within .input-icon {
            color: var(--input-icon-focus);
        }

        /* Password Visibility Toggle */
        .password-toggle-btn {
            position: absolute;
            right: 0.85rem;
            background: none;
            border: none;
            color: var(--input-icon);
            padding: 0.4rem;
            cursor: pointer;
            border-radius: 6px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
            z-index: 3;
        }

        .password-toggle-btn:hover {
            color: var(--input-text);
        }

        /* ── Submit Action Button ── */
        .btn-submit {
            font-family: var(--font-heading);
            width: 100%;
            height: 50px;
            background: var(--btn-racing-red);
            border: none;
            border-radius: 14px;
            color: #ffffff;
            font-size: 1.02rem;
            font-weight: 700;
            letter-spacing: 0.01em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.65rem;
            cursor: pointer;
            box-shadow: var(--btn-shadow);
            margin-top: 1.85rem;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: var(--btn-shadow-hover);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px -2px rgba(234, 43, 28, 0.35);
        }

        .btn-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ── Footer Credits ── */
        .login-credits {
            text-align: center;
            margin-top: 1.85rem;
        }

        .credit-copy {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-footer-main);
            letter-spacing: 0.01em;
            margin-bottom: 0.3rem;
            transition: color 0.25s ease;
        }

        .credit-dev {
            font-size: 0.8rem;
            color: var(--text-muted);
            transition: color 0.25s ease;
        }

        .credit-dev strong {
            color: var(--text-footer-bold);
            font-weight: 700;
            transition: color 0.25s ease;
        }
    </style>
</head>
<body>

    <!-- Top-Right Theme Toggle Button (Light / Dark) -->
    <button type="button" class="theme-toggle-btn" id="themeToggleBtn" onclick="toggleTheme()" title="Switch to Dark Mode" aria-label="Toggle Theme">
        <i class="fa-regular fa-moon" id="themeToggleIcon"></i>
    </button>

    <div class="login-wrapper">
        <div class="login-card">
            
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="logo-img-box">
                    <img src="<?= BASE_URL ?>assets/img/logo-horizontal.png?v=<?= file_exists(__DIR__ . '/assets/img/logo-horizontal.png') ? filemtime(__DIR__ . '/assets/img/logo-horizontal.png') : '2.0' ?>" alt="ELLA MOTOR PARTS" class="logo-img">
                </div>
                
                <div class="d-flex justify-content-center">
                    <div class="portal-badge">
                        <i class="fa-solid fa-file-invoice"></i>
                        <span>Electronic Invoicing Portal</span>
                    </div>
                </div>

                <h1 class="login-title">Sign In to Portal</h1>
            </div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)" autocomplete="off">
                
                <!-- Username -->
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-box">
                        <i class="fa-solid fa-user input-icon"></i>
                        <input type="text" 
                               class="form-control-custom" 
                               id="username" 
                               placeholder="Enter your username" 
                               required 
                               autofocus 
                               autocomplete="username">
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control-custom has-toggle" 
                               id="password" 
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" title="Show/Hide Password" tabindex="-1">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit" id="btnLogin">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Sign In to Portal</span>
                </button>
            </form>
        </div>

        <!-- Developer & Business Credits -->
        <div class="login-credits">
            <div class="credit-copy">&copy; 2026 Ella Motor Parts</div>
            <div class="credit-dev">
                Developed by: <strong>Lester Bucag</strong>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;

    // Theme Management (Light / Dark)
    function initTheme() {
        const savedTheme = localStorage.getItem('einv_login_theme') || 'light';
        applyTheme(savedTheme);
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('einv_login_theme', theme);
        
        const btn = document.getElementById('themeToggleBtn');
        const icon = document.getElementById('themeToggleIcon');
        if (icon && btn) {
            if (theme === 'dark') {
                icon.className = 'fa-regular fa-sun';
                btn.setAttribute('title', 'Switch to Light Mode');
            } else {
                icon.className = 'fa-regular fa-moon';
                btn.setAttribute('title', 'Switch to Dark Mode');
            }
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        applyTheme(newTheme);
    }

    // Initialize Theme immediately
    initTheme();

    // Password Visibility Toggle
    function togglePasswordVisibility() {
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Form Submission & Authentication
    function handleLogin(e) {
        e.preventDefault();
        const btn = document.getElementById('btnLogin');
        const u = document.getElementById('username').value.trim();
        const p = document.getElementById('password').value.trim();
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';

        if (!u || !p) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: 'Please enter both username and password.',
                background: isDark ? '#131622' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f172a',
                confirmButtonColor: '#e11d2a'
            });
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> <span>Authenticating...</span>';

        const formData = new FormData();
        formData.append('username', u);
        formData.append('password', p);

        fetch(window.BASE_URL + 'api/auth.php', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                btn.innerHTML = '<i class="fa-solid fa-check"></i> <span>Access Granted</span>';
                btn.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 850,
                    timerProgressBar: true,
                    background: isDark ? '#131622' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f172a'
                });
                
                Toast.fire({
                    icon: 'success',
                    title: 'Signed in successfully'
                }).then(() => {
                    window.location.href = data.redirect || 'index.php';
                });
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> <span>Sign In to Portal</span>';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Sign In Failed',
                    text: data.error || 'Invalid username or password.',
                    background: isDark ? '#131622' : '#ffffff',
                    color: isDark ? '#ffffff' : '#0f172a',
                    confirmButtonColor: '#e11d2a'
                });
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-arrow-right-to-bracket"></i> <span>Sign In to Portal</span>';
            Swal.fire({
                icon: 'error',
                title: 'Connection Error',
                text: err.message,
                background: isDark ? '#131622' : '#ffffff',
                color: isDark ? '#ffffff' : '#0f172a',
                confirmButtonColor: '#e11d2a'
            });
        });
    }
    </script>
</body>
</html>
