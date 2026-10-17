<?php
// api/eis_transmit.php
// Transmits selected or pending invoices to BIR EIS Gateway (POST /api/invoices)

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/BirEisClient.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed. Use POST.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

try {
    $db = new Database();
    $conn = $db->getConnection();
    $client = new BirEisClient($conn);

    $invoiceIds = [];

    if (!empty($input['invoice_ids']) && is_array($input['invoice_ids'])) {
        $invoiceIds = array_map('intval', $input['invoice_ids']);
    } elseif (!empty($input['invoice_id'])) {
        $invoiceIds = [intval($input['invoice_id'])];
    } elseif (!empty($input['transmit_all_pending'])) {
        // Fetch up to 100 pending invoices
        $stmt = $conn->query("SELECT id FROM einv_invoices WHERE eis_status = 'pending' AND status = 'generated' ORDER BY id ASC LIMIT 100");
        $invoiceIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    if (empty($invoiceIds)) {
        echo json_encode(['success' => false, 'error' => 'No invoices selected or found for transmission.']);
        exit;
    }

    $result = $client->transmitInvoices($invoiceIds);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
