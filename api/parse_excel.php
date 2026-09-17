<?php
// api/parse_excel.php
// Multi-Platform Parser for Shopee, Lazada, and TikTok Shop Orders (.xlsx)

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/MultiPlatformExcelReader.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Please select a valid Excel file to upload.']);
    exit;
}

$fileTmp = $_FILES['excel_file']['tmp_name'];
$fileName = $_FILES['excel_file']['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

if ($fileExt !== 'xlsx') {
    echo json_encode(['success' => false, 'error' => 'Invalid file format. Please upload a .xlsx Excel file.']);
    exit;
}

$platformHint = trim($_POST['platform'] ?? '');

$parseResult = MultiPlatformExcelReader::parse($fileTmp, $platformHint);
if (!$parseResult['success']) {
    echo json_encode(['success' => false, 'error' => $parseResult['error']]);
    exit;
}

$detectedPlatform = $parseResult['platform'] ?? 'Shopee';
$orders = $parseResult['orders'];
if (empty($orders)) {
    echo json_encode(['success' => false, 'error' => 'No orders found in the uploaded file.']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $settings = getEinvSettings($conn);

    // Cross-check existing invoices
    $orderSns = array_column($orders, 'order_sn');
    $placeholders = implode(',', array_fill(0, count($orderSns), '?'));
    
    $stmt = $conn->prepare("SELECT order_sn, invoice_number, pdf_filename, status FROM einv_invoices WHERE order_sn IN ($placeholders)");
    $stmt->execute($orderSns);
    $existing = $stmt->fetchAll();

    $existingMap = [];
    foreach ($existing as $ex) {
        $existingMap[$ex['order_sn']] = $ex;
    }

    // Determine platform-specific invoice number prefix
    $currYear = date('Y');
    if ($detectedPlatform === 'Lazada') {
        $prefix = $settings['invoice_prefix_lazada'] ?? "SI-LAZ-{$currYear}-";
    } elseif ($detectedPlatform === 'TikTok Shop' || $detectedPlatform === 'TikTok') {
        $prefix = $settings['invoice_prefix_tiktok'] ?? "SI-TT-{$currYear}-";
    } else {
        $prefix = $settings['invoice_prefix_shopee'] ?? ($settings['invoice_prefix'] ?? "SI-SHP-{$currYear}-");
    }

    // Query highest invoice number with this prefix
    $stmtMax = $conn->prepare("SELECT invoice_number FROM einv_invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmtMax->execute([$prefix . '%']);
    $lastInv = $stmtMax->fetchColumn();

    $nextNum = 1;
    if ($lastInv) {
        $parts = explode('-', $lastInv);
        $lastSeq = (int)end($parts);
        if ($lastSeq > 0) {
            $nextNum = $lastSeq + 1;
        }
    }
    $nextInvoiceNumber = $prefix . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

    $readyCount = 0;
    $alreadyCount = 0;
    $totalAmountAll = 0;
    $totalVatableAll = 0;
    $totalVatAll = 0;

    foreach ($orders as &$ord) {
        $ord['platform_name'] = $detectedPlatform;
        $osn = $ord['order_sn'];
        if (isset($existingMap[$osn])) {
            $ord['is_generated'] = true;
            $ord['existing_invoice_number'] = $existingMap[$osn]['invoice_number'];
            $ord['pdf_filename'] = $existingMap[$osn]['pdf_filename'];
            $alreadyCount++;
        } else {
            $ord['is_generated'] = false;
            $ord['existing_invoice_number'] = null;
            $ord['pdf_filename'] = null;
            $readyCount++;
        }

        $totalAmountAll += $ord['grand_total'];
        $totalVatableAll += $ord['vatable_sales'];
        $totalVatAll += $ord['vat_amount'];
    }
    unset($ord);

    if ($readyCount === 0 && count($orders) > 0) {
        echo json_encode([
            'success' => false, 
            'error' => 'Duplicate Upload Detected: All ' . count($orders) . ' orders in this Excel file have already been generated and are in your Invoices History.'
        ]);
        exit;
    }

    echo json_encode([
        'success'             => true,
        'filename'            => $fileName,
        'platform'            => $detectedPlatform,
        'total_orders'        => count($orders),
        'ready_count'         => $readyCount,
        'already_count'       => $alreadyCount,
        'total_amount'        => round($totalAmountAll, 2),
        'total_vatable'       => round($totalVatableAll, 2),
        'total_vat'           => round($totalVatAll, 2),
        'next_invoice_number' => $nextInvoiceNumber,
        'prefix'              => $prefix,
        'next_seq'            => $nextNum,
        'orders'              => $orders
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
