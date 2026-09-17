<?php
// login.php
// Modern Glassmorphic Login Gateway
// E-Invoice Management Portal | Developed by: Lester Bucag
require_once __DIR__ . '/config/config.php';

if (isEinvLoggedIn()) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — Electronic Invoicing Portal</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-mark.png">
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-red: #e52319;
            --brand-red-dark: #b91c1c;
            --brand-gold: #f59e0b;
            --brand-accent: #2563eb;
            --brand-accent-dark: #1d4ed8;
            
            /* Rich contrast light background (not purely white) */
            --bg-page: #edf2f7;
            --bg-card: #ffffff;
            --border-card: rgba(203, 213, 225, 0.7);
            --border-hover: #94a3b8;
            
            --text-main: #0f172a;
            --text-label: #334155;
            --text-muted: #64748b;
            --text-soft: #94a3b8;
            
            --input-bg: #f8fafc;
            --input-border: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-page);
            background-image: 
                radial-gradient(at 0% 0%, rgba(229, 35, 25, 0.08) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(238, 77, 45, 0.09) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(245, 158, 11, 0.05) 0px, transparent 50%);
            background-attachment: fixed;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 2rem 1rem;
        }

        /* Ambient Glowing Accents */
        .ambient-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: 0;
            opacity: 0.65;
            animation: pulseGlow 12s ease-in-out infinite alternate;
        }

        .ambient-glow-1 {
            width: 480px;
            height: 480px;
            top: -120px;
            left: -80px;
            background: radial-gradient(circle, rgba(229, 35, 25, 0.16) 0%, transparent 70%);
        }

        .ambient-glow-2 {
            width: 520px;
            height: 520px;
            bottom: -140px;
            right: -80px;
            background: radial-gradient(circle, rgba(238, 77, 45, 0.15) 0%, rgba(245, 158, 11, 0.08) 70%);
            animation-delay: -6s;
        }

        @keyframes pulseGlow {
            0% { transform: scale(1) translateY(0); opacity: 0.55; }
            50% { transform: scale(1.1) translateY(-16px); opacity: 0.75; }
            100% { transform: scale(1) translateY(0); opacity: 0.55; }
        }

        /* Elegant Micro Dot Grid */
        .grid-overlay {
            position: fixed;
            inset: 0;
            background-image: radial-gradient(rgba(100, 116, 139, 0.18) 1px, transparent 1px);
            background-size: 24px 24px;
            pointer-events: none;
            z-index: 1;
        }

        /* Main Container */
        .login-wrapper {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 460px;
            animation: slideUpFade 0.65s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Premium White Floating Card with High Contrast */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 24px;
            padding: 2.75rem 2.5rem 2.25rem;
            box-shadow: 
                0 25px 50px -12px rgba(15, 23, 42, 0.14),
                0 4px 16px -2px rgba(15, 23, 42, 0.06),
                0 0 0 1px rgba(255, 255, 255, 0.8) inset;
            position: relative;
            overflow: hidden;
        }

        /* Top Accent Racing Gradient Ribbon */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #e52319 0%, #ee4d2d 50%, #f59e0b 100%);
        }

        /* Brand Logo Area */
        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-img-wrapper {
            display: inline-block;
            padding: 0.5rem;
            position: relative;
            margin-bottom: 0.85rem;
            transition: transform 0.3s ease;
        }

        .logo-img-wrapper:hover {
            transform: scale(1.03);
        }

        .logo-img {
            max-width: 250px;
            height: auto;
            display: block;
            margin: 0 auto;
            filter: drop-shadow(0 4px 12px rgba(229, 35, 25, 0.15));
        }

        /* Electronic Invoicing Portal Badge */
        .portal-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid rgba(37, 99, 235, 0.25);
            padding: 0.35rem 0.95rem;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            margin-bottom: 0.85rem;
        }

        .portal-badge i {
            color: #2563eb;
            font-size: 0.82rem;
        }

        .login-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.02em;
            margin-bottom: 0;
        }

        /* Inputs & Form */
        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-label-custom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-label);
            margin-bottom: 0.5rem;
            letter-spacing: 0.01em;
        }

        .input-box {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 1.1rem;
            color: #94a3b8;
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.25s ease;
            z-index: 3;
        }

        .form-control-custom {
            width: 100%;
            height: 48px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            padding: 0.75rem 1.1rem 0.75rem 2.85rem;
            color: var(--text-main);
            font-size: 0.92rem;
            font-family: inherit;
            box-shadow: inset 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-control-custom:hover {
            border-color: var(--border-hover);
            background: #ffffff;
        }

        .form-control-custom:focus {
            outline: none;
            background: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.14), 0 2px 8px rgba(0,0,0,0.04);
            color: var(--text-main);
        }

        .form-control-custom:focus + .input-icon,
        .input-box:focus-within .input-icon {
            color: #2563eb;
        }

        .form-control-custom::placeholder {
            color: #94a3b8;
            font-size: 0.88rem;
        }

        /* Password Toggle */
        .password-toggle-btn {
            position: absolute;
            right: 0.75rem;
            background: none;
            border: none;
            color: #94a3b8;
            padding: 0.4rem 0.6rem;
            cursor: pointer;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            z-index: 3;
        }

        .password-toggle-btn:hover {
            color: #334155;
            background: rgba(0, 0, 0, 0.05);
        }

        /* Login Button */
        .btn-submit {
            font-family: 'Outfit', sans-serif;
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #2563eb 100%);
            background-size: 200% auto;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            cursor: pointer;
            box-shadow: 0 8px 20px -4px rgba(15, 23, 42, 0.35);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            margin-top: 1.5rem;
        }

        .btn-submit:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 12px 25px -4px rgba(37, 99, 235, 0.45);
            color: #ffffff;
        }

        .btn-submit:active {
            transform: translateY(1px);
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.3);
        }

        .btn-submit:disabled {
            opacity: 0.75;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Developer & Copyright Footer */
        .login-credits {
            text-align: center;
            margin-top: 1.75rem;
        }

        .credit-copy {
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: 0.01em;
            margin-bottom: 0.25rem;
        }

        .credit-dev {
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .credit-dev strong {
            color: var(--text-label);
            font-weight: 700;
        }
    </style>
</head>
<body>

    <!-- Ambient Glow Effects -->
    <div class="ambient-glow ambient-glow-1"></div>
    <div class="ambient-glow ambient-glow-2"></div>
    <div class="grid-overlay"></div>

    <div class="login-wrapper">
        <div class="login-card">
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="logo-img-wrapper">
                    <img src="<?= BASE_URL ?>assets/img/logo-horizontal.png" alt="E-Invoice Portal" class="logo-img">
                </div>
                
                <div class="d-flex justify-content-center mb-2">
                    <div class="portal-badge">
                        <i class="fa-solid fa-file-invoice-dollar"></i> Electronic Invoicing Portal
                    </div>
                </div>

                <h1 class="login-title">Sign In to Portal</h1>
            </div>

            <!-- Login Form -->
            <form id="loginForm" onsubmit="handleLogin(event)" autocomplete="off">
                <div class="form-group">
                    <label class="form-label-custom" for="username">
                        <span>Username</span>
                    </label>
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

                <div class="form-group">
                    <label class="form-label-custom" for="password">
                        <span>Password</span>
                    </label>
                    <div class="input-box">
                        <i class="fa-solid fa-lock input-icon"></i>
                        <input type="password" 
                               class="form-control-custom" 
                               id="password" 
                               placeholder="••••••••" 
                               required 
                               autocomplete="current-password"
                               style="padding-right: 2.85rem;">
                        <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility()" title="Show/Hide Password" tabindex="-1">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnLogin">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Sign In to Portal</span>
                </button>
            </form>
        </div>

        <!-- Developer & Business Credits -->
        <div class="login-credits">
            <div class="credit-copy">&copy; <?= date('Y') ?> E-Invoice Management Portal</div>
            <div class="credit-dev">
                Developed by: <strong>Lester Bucag</strong>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;

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

    function handleLogin(e) {
        e.preventDefault();
        const btn = document.getElementById('btnLogin');
        const u = document.getElementById('username').value.trim();
        const p = document.getElementById('password').value.trim();

        if (!u || !p) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Fields',
                text: 'Please enter both username and password.',
                confirmButtonColor: '#ee4d2d'
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
                    timer: 900,
                    timerProgressBar: true
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
                    confirmButtonColor: '#ee4d2d'
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
                confirmButtonColor: '#ee4d2d'
            });
        });
    }
    </script>
</body>
</html>
