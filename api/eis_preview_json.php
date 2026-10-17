<?php
// api/eis_preview_json.php
// Returns generated BIR CAS JSON payload for a given invoice ID

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/BirEisClient.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid invoice ID']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("SELECT * FROM einv_invoices WHERE id = ?");
    $stmt->execute([$id]);
    $inv = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inv) {
        echo json_encode(['success' => false, 'error' => 'Invoice not found']);
        exit;
    }

    $client = new BirEisClient($conn);
    $casData = $client->buildCasInvoiceJson($inv);

    echo json_encode([
        'success'        => true,
        'invoice_number' => $inv['invoice_number'],
        'eis_unique_id'  => $casData['EisUniqueId'],
        'eis_status'     => $inv['eis_status'],
        'eis_ack_id'     => $inv['eis_ack_id'],
        'eis_submit_id'  => $inv['eis_submit_id'],
        'cas_json'       => json_encode($casData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
        'cas_data'       => $casData
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
