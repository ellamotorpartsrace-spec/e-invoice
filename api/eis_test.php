<?php
// api/eis_test.php
// Endpoint to test BIR EIS sandbox schema and connectivity

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../classes/BirEisClient.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

try {
    $client = new BirEisClient();
    $result = $client->runSelfTest();
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
