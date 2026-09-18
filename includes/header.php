<?php
// includes/header.php
require_once __DIR__ . '/../config/config.php';
requireEinvLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$pageTitle = $pageTitle ?? 'Electronic Invoicing Portal';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> — E-Invoice Management Portal</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>assets/img/logo-mark.png?v=<?= file_exists(__DIR__ . '/../assets/img/logo-mark.png') ? filemtime(__DIR__ . '/../assets/img/logo-mark.png') : '2.0' ?>">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    
    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Modern Styling -->
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/app.css?v=2.0">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>window.BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
</head>
<body>

<!-- Top Brand Racing Gradient Ribbon -->
<div class="top-accent-bar"></div>

<!-- Modern Glassmorphism Navbar -->
<nav class="app-navbar">
    <div class="d-flex align-items-center justify-content-between max-w-1340 mx-auto w-100 flex-wrap gap-2">
        
        <!-- Brand / Logo Area -->
        <a href="<?= BASE_URL ?>index.php" class="app-brand">
            <img src="<?= BASE_URL ?>assets/img/logo-horizontal.png" alt="E-Invoice Portal" class="brand-logo-img">
            <div class="brand-portal-tag d-none d-sm-flex">
                <span class="brand-title">E-Invoice Portal</span>
                <span class="brand-sub">BIR Annex A1 System</span>
            </div>
        </a>

        <!-- Center Navigation Tabs -->
        <div class="nav-pill-group">
            <a href="<?= BASE_URL ?>index.php" class="nav-link-custom <?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-cloud-arrow-up"></i>
                <span>Upload & Generate</span>
            </a>
            <a href="<?= BASE_URL ?>history.php" class="nav-link-custom <?= $currentPage === 'history.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-receipt"></i>
                <span>Invoices History</span>
            </a>
            <a href="<?= BASE_URL ?>settings.php" class="nav-link-custom <?= $currentPage === 'settings.php' ? 'active' : '' ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </div>

        <!-- Right System Status & User Profile -->
        <div class="d-flex align-items-center gap-3">
            <!-- BIR Status Pill -->
            <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle d-none d-lg-inline-flex align-items-center gap-2 py-1 px-3">
                <span class="pulse-dot"></span>
                <span style="font-size:0.75rem; font-weight:600;">BIR Ready</span>
            </span>

            <!-- User Badge -->
            <div class="nav-user-pill d-none d-md-flex">
                <div class="user-avatar-circle">
                    <?= strtoupper(substr($_SESSION['einv_username'] ?? 'A', 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($_SESSION['einv_full_name'] ?? 'Administrator') ?></span>
            </div>

            <!-- Sign Out Button -->
            <a href="<?= BASE_URL ?>logout.php" class="btn-logout" title="Sign Out">
                <i class="fa-solid fa-power-off"></i>
            </a>
        </div>

    </div>
</nav>

<div class="main-content">
