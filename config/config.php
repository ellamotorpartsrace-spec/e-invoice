<?php
// config/config.php
// Global Application Settings & Dynamic Base URL Detection

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Dynamic Base URL Detection (Works on both Localhost and Hostinger domains automatically)
if (!defined('BASE_URL')) {
    // Respect ngrok and load balancers' forwarded headers
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $protocol = $_SERVER['HTTP_X_FORWARDED_PROTO'] . '://';
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? 80) == 443 ? 'https://' : 'http://';
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    }
    
    // Determine subdirectory if any
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    // If in api/ or views/, navigate up
    $scriptDir = preg_replace('#/(api|classes|database|storage).*$#i', '', $scriptDir);
    $basePath = rtrim($scriptDir, '/') . '/';
    
    define('BASE_URL', $protocol . $host . $basePath);
}

define('APP_NAME', 'E-Invoice Management Portal');
define('APP_VERSION', '2.0.0');

// 2. Settings Helper Functions
require_once __DIR__ . '/database.php';

function getEinvSettings($conn) {
    static $cache = null;
    if ($cache !== null) return $cache;
    try {
        $stmt = $conn->query("SELECT setting_key, setting_value FROM einv_settings");
        $cache = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
    } catch (Exception $e) {
        $cache = [];
    }
    return $cache;
}

function getEinvSetting($conn, $key, $default = '') {
    $s = getEinvSettings($conn);
    return $s[$key] ?? $default;
}

// 3. Auth Helpers
function isEinvLoggedIn() {
    return !empty($_SESSION['einv_user_id']);
}

function requireEinvLogin() {
    if (!isEinvLoggedIn()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}
