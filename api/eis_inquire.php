<?php
// api/eis_inquire.php
// Polls BIR EIS Gateway (/api/invoice_result/{submitId}) for invoice processing & verification results

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/BirEisClient.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true) ?: $_GET;

$submitId = trim($input['submit_id'] ?? '');

try {
    $db = new Database();
    $conn = $db->getConnection();
    $client = new BirEisClient($conn);

    if (!empty($submitId)) {
        $result = $client->inquireResult($submitId);
        echo json_encode($result);
        exit;
    }

    // If sync_all_transmitted is requested, check all pending submitIds
    if (!empty($input['sync_all'])) {
        $stmt = $conn->query("SELECT DISTINCT eis_submit_id FROM einv_invoices WHERE eis_status = 'transmitted' AND eis_submit_id IS NOT NULL AND eis_submit_id != '' LIMIT 10");
        $submitIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($submitIds)) {
            echo json_encode([
                'success' => true,
                'message' => 'No active transmissions currently awaiting verification.',
                'synced_count' => 0
            ]);
            exit;
        }

        $results = [];
        foreach ($submitIds as $sId) {
            try {
                $results[$sId] = $client->inquireResult($sId);
            } catch (Exception $ex) {
                $results[$sId] = ['error' => $ex->getMessage()];
            }
        }

        echo json_encode([
            'success'      => true,
            'synced_count' => count($submitIds),
            'results'      => $results
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Please provide a submit_id or sync_all flag.']);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
