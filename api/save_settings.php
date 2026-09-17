<?php
// api/save_settings.php
// Updates store profile, BIR tax details, and admin credentials

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $action = $_POST['action'] ?? 'settings';

    if ($action === 'password') {
        $oldPass = trim($_POST['old_password'] ?? '');
        $newPass = trim($_POST['new_password'] ?? '');

        if (empty($oldPass) || empty($newPass)) {
            echo json_encode(['success' => false, 'error' => 'Please enter both current and new password']);
            exit;
        }

        $userId = $_SESSION['einv_user_id'];
        $stmt = $conn->prepare("SELECT password_hash FROM einv_users WHERE id = ?");
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($oldPass, $hash)) {
            echo json_encode(['success' => false, 'error' => 'Current password is incorrect']);
            exit;
        }

        $newHash = password_hash($newPass, PASSWORD_DEFAULT);
        $upStmt = $conn->prepare("UPDATE einv_users SET password_hash = ? WHERE id = ?");
        $upStmt->execute([$newHash, $userId]);

        echo json_encode(['success' => true, 'message' => 'Password changed successfully']);
        exit;
    }

    $allowedKeys = [
        'store_name', 'store_tin', 'store_address', 'store_contact',
        'vat_status', 'invoice_prefix', 'invoice_prefix_shopee', 'invoice_prefix_lazada', 'invoice_prefix_tiktok',
        'permit_no', 'atp_no', 'approved_series',
        'eis_enabled', 'eis_env', 'eis_client_id', 'eis_client_secret', 'eis_api_key', 'eis_cert_serial'
    ];

    $stmt = $conn->prepare("INSERT INTO einv_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");

    foreach ($allowedKeys as $key) {
        if (isset($_POST[$key])) {
            $stmt->execute([$key, trim($_POST[$key])]);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Tax profile & settings saved successfully']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
