<?php
// api/auth.php
// Login and Logout API for Standalone Shopee E-Invoice Portal

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

$action = $_GET['action'] ?? ($_POST['action'] ?? 'login');

if ($action === 'logout') {
    unset($_SESSION['einv_user_id']);
    unset($_SESSION['einv_username']);
    unset($_SESSION['einv_full_name']);
    session_destroy();
    echo json_encode(['success' => true, 'redirect' => BASE_URL . 'login.php']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'error' => 'Please enter username and password']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, username, password_hash, full_name, role FROM einv_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['einv_user_id']   = $user['id'];
        $_SESSION['einv_username']  = $user['username'];
        $_SESSION['einv_full_name'] = $user['full_name'];
        $_SESSION['einv_role']      = $user['role'];

        echo json_encode([
            'success'  => true,
            'user'     => ['username' => $user['username'], 'name' => $user['full_name']],
            'redirect' => BASE_URL . 'index.php'
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid username or password']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
