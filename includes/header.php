<?php
// includes/header.php
require_once __DIR__ . '/../config/config.php';
requireEinvLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Electronic Invoicing Portal';
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — ELLA MOTOR PARTS | BIR E-Invoice Portal</title>
    
    <script>
        // Immediately apply saved theme to avoid FOUC
        (function() {
            try {
                const savedTheme = localStorage.getItem('einv_theme') || localStorage.getItem('einv_login_theme') || 'light';
                document.documentElement.setAttribute('data-theme', savedTheme);
                document.documentElement.setAttribute('data-bs-theme', savedTheme);
            } catch (e) {}
        })();
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-mark.png?v=<?= file_exists(__DIR__ . '/../assets/img/logo-mark.png') ? filemtime(__DIR__ . '/../assets/img/logo-mark.png') : '2.0' ?>">

    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Modern Styling -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css?v=2.1">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
</head>
<body>

<!-- Modern Clean Navbar -->
<nav class="app-navbar">
    <div class="d-flex align-items-center justify-content-between max-w-1340 mx-auto w-100 flex-wrap gap-2">
        
        <!-- Brand / Logo Area -->
        <a href="<?= BASE_URL ?>index.php" class="app-brand" title="E-Invoice Portal">
            <img src="<?= BASE_URL ?>assets/img/logo-horizontal.png?v=<?= file_exists(__DIR__ . '/../assets/img/logo-horizontal.png') ? filemtime(__DIR__ . '/../assets/img/logo-horizontal.png') : '2.0' ?>" alt="ELLA MOTOR PARTS" class="brand-logo-img">
            <div class="brand-portal-tag d-none d-sm-flex">
                <span class="brand-title">E-Invoice Portal</span>
                <span class="brand-sub">BIR ANNEX A1 SYSTEM</span>
            </div>
        </a>

        <!-- Center Navigation Tabs -->
        <div class="nav-pill-group">
            <a href="<?= BASE_URL ?>index.php" class="nav-link-custom <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Upload & Generate</span>
            </a>
            <a href="<?= BASE_URL ?>history.php" class="nav-link-custom <?= $currentPage === 'history.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-file-invoice"></i>
                <span>Invoices Archive</span>
            </a>
            <a href="<?= BASE_URL ?>settings.php" class="nav-link-custom <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </div>

        <!-- Right System Status & User Profile -->
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <!-- BIR Status Pill -->
            <span class="bir-status-pill d-none d-lg-inline-flex align-items-center gap-2">
                <span class="pulse-dot"></span>
                <span>BIR Ready</span>
            </span>

            <!-- User Badge -->
            <?php
            $displayAdminName = 'Ella Motor Parts Admin';
            if (!empty($_SESSION['einv_full_name']) && $_SESSION['einv_full_name'] !== 'Demo Administrator') {
                $displayAdminName = $_SESSION['einv_full_name'];
            }
            $_SESSION['einv_full_name'] = $displayAdminName;
            ?>
            <div class="nav-user-pill d-none d-md-flex align-items-center">
                <div class="user-avatar-circle">A</div>
                <span><?= htmlspecialchars($displayAdminName) ?></span>
            </div>

            <!-- Dark / Light Mode Toggle Button -->
            <button type="button" class="btn-theme-toggle" id="appThemeToggleBtn" onclick="toggleAppTheme()" title="Toggle Dark/Light Mode">
                <i class="fa-solid fa-moon theme-icon-moon"></i>
                <i class="fa-solid fa-sun theme-icon-sun d-none"></i>
            </button>

            <!-- Sign Out Button -->
            <a href="<?= BASE_URL ?>logout.php" class="btn-logout" title="Sign Out">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>

    </div>
</nav>

<script>
function toggleAppTheme() {
    const cur = document.documentElement.getAttribute('data-theme') || 'light';
    const next = cur === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    document.documentElement.setAttribute('data-bs-theme', next);
    try {
        localStorage.setItem('einv_theme', next);
        localStorage.setItem('einv_login_theme', next);
    } catch(e) {}
    updateThemeToggleIcons(next);
}

function updateThemeToggleIcons(theme) {
    const moon = document.querySelector('.theme-icon-moon');
    const sun = document.querySelector('.theme-icon-sun');
    if (moon && sun) {
        if (theme === 'dark') {
            moon.classList.add('d-none');
            sun.classList.remove('d-none');
        } else {
            moon.classList.remove('d-none');
            sun.classList.add('d-none');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const theme = document.documentElement.getAttribute('data-theme') || 'light';
    updateThemeToggleIcons(theme);
});
</script>

<div class="main-content">
