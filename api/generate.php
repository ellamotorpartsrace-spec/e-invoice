<?php
// api/generate.php
// Batch generation of BIR Annex A1 Invoices and ZIP package

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/ShopeeInvoicePDF.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (empty($input['orders']) || !is_array($input['orders'])) {
    echo json_encode(['success' => false, 'error' => 'No orders selected for generation.']);
    exit;
}

$orders = $input['orders'];
$issueDate = !empty($input['issue_date']) ? $input['issue_date'] : date('Y-m-d');
$userName = $_SESSION['einv_username'] ?? 'admin';

$storageDir = __DIR__ . '/../storage/invoices';
if (!is_dir($storageDir)) {
    @mkdir($storageDir, 0777, true);
}

try {
    $db = new Database();
    $conn = $db->getConnection();
    $settings = getEinvSettings($conn);

    $currYear = date('Y', strtotime($issueDate));
    $prefix = !empty($input['prefix']) ? trim($input['prefix']) : ($settings['invoice_prefix'] ?? "SI-SHP-{$currYear}-");

    $stmtMax = $conn->prepare("SELECT invoice_number FROM einv_invoices WHERE invoice_number LIKE ? ORDER BY id DESC LIMIT 1");
    $stmtMax->execute([$prefix . '%']);
    $lastInv = $stmtMax->fetchColumn();

    $seq = 1;
    if ($lastInv) {
        $parts = explode('-', $lastInv);
        $lastSeq = (int)end($parts);
        if ($lastSeq > 0) {
            $seq = $lastSeq + 1;
        }
    }

    if (!empty($input['starting_seq']) && (int)$input['starting_seq'] > $seq) {
        $seq = (int)$input['starting_seq'];
    }

    $generated = [];
    $pdfFilesForZip = [];

    $defaultPlatform = !empty($input['platform']) ? trim($input['platform']) : 'Shopee';

    $insertStmt = $conn->prepare("
        INSERT INTO einv_invoices 
        (invoice_number, order_sn, platform_name, buyer_name, buyer_tin, buyer_address, invoice_type, 
         order_date, issue_date, gross_items_total, shipping_fee, discount_amount, total_amount, 
         vatable_sales, vat_amount, vat_exempt_sales, zero_rated_sales, items_json, pdf_filename, status, created_by)
        VALUES 
        (:invoice_number, :order_sn, :platform_name, :buyer_name, :buyer_tin, :buyer_address, :invoice_type, 
         :order_date, :issue_date, :gross_items_total, :shipping_fee, :discount_amount, :total_amount, 
         :vatable_sales, :vat_amount, 0.00, 0.00, :items_json, :pdf_filename, 'generated', :created_by)
        ON DUPLICATE KEY UPDATE 
         buyer_name = VALUES(buyer_name),
         buyer_tin = VALUES(buyer_tin),
         buyer_address = VALUES(buyer_address),
         pdf_filename = VALUES(pdf_filename),
         updated_at = CURRENT_TIMESTAMP
    ");

    foreach ($orders as $ord) {
        $orderSn = trim($ord['order_sn'] ?? '');
        if (empty($orderSn)) continue;

        $platformName    = !empty($ord['platform_name']) ? trim($ord['platform_name']) : $defaultPlatform;
        $invNumber       = $prefix . str_pad($seq++, 5, '0', STR_PAD_LEFT);

        $buyerName       = trim($ord['buyer_name'] ?? 'CASH CUSTOMER');
        $buyerTin        = trim($ord['buyer_tin'] ?? '000-000-000-00000');
        $buyerAddress    = trim($ord['buyer_address'] ?? 'N/A');
        $invoiceType     = trim($ord['invoice_type'] ?? 'Personal');
        $orderDate       = !empty($ord['order_date']) ? $ord['order_date'] : date('Y-m-d H:i:s');
        $grandTotal      = (float)($ord['grand_total'] ?? 0);
        $shippingFee     = (float)($ord['shipping_fee'] ?? 0);
        $discountAmount  = (float)($ord['discount_amount'] ?? 0);
        $grossItemsTotal = (float)($ord['gross_items_total'] ?? 0);
        $items           = $ord['items'] ?? [];

        $vatableSales = round($grandTotal / 1.12, 2);
        $vatAmount    = round($grandTotal - $vatableSales, 2);

        $pdfFileName = $orderSn . '.pdf';
        $fullPdfPath = $storageDir . '/' . $pdfFileName;

        $invoicePayload = [
            'invoice_number'    => $invNumber,
            'order_sn'          => $orderSn,
            'platform_name'     => $platformName,
            'buyer_name'        => $buyerName,
            'buyer_tin'         => $buyerTin,
            'buyer_address'     => $buyerAddress,
            'invoice_type'      => $invoiceType,
            'order_date'        => $orderDate,
            'issue_date'        => $issueDate,
            'total_amount'      => $grandTotal,
            'vatable_sales'     => $vatableSales,
            'vat_amount'        => $vatAmount,
            'vat_exempt_sales'  => 0.00,
            'zero_rated_sales'  => 0.00,
            'shipping_fee'      => $shippingFee,
            'discount_amount'   => $discountAmount,
            'gross_items_total' => $grossItemsTotal,
            'items'             => $items
        ];

        // Generate PDF invoice
        $pdf = new ShopeeInvoicePDF($settings, $invoicePayload);
        $pdf->build();
        $pdf->Output('F', $fullPdfPath);

        // Store into DB
        $insertStmt->execute([
            ':invoice_number'    => $invNumber,
            ':order_sn'          => $orderSn,
            ':platform_name'     => $platformName,
            ':buyer_name'        => $buyerName,
            ':buyer_tin'         => $buyerTin,
            ':buyer_address'     => $buyerAddress,
            ':invoice_type'      => $invoiceType,
            ':order_date'        => $orderDate,
            ':issue_date'        => $issueDate,
            ':gross_items_total' => $grossItemsTotal,
            ':shipping_fee'      => $shippingFee,
            ':discount_amount'   => $discountAmount,
            ':total_amount'      => $grandTotal,
            ':vatable_sales'     => $vatableSales,
            ':vat_amount'        => $vatAmount,
            ':items_json'        => json_encode($items),
            ':pdf_filename'      => $pdfFileName,
            ':created_by'        => $userName
        ]);

        $generated[] = [
            'order_sn'       => $orderSn,
            'invoice_number' => $invNumber,
            'buyer_name'     => $buyerName,
            'total_amount'   => $grandTotal,
            'pdf_filename'   => $pdfFileName
        ];

        if (file_exists($fullPdfPath)) {
            $pdfFilesForZip[$pdfFileName] = $fullPdfPath;
        }
    }

    $zipFilename = null;
    $zipDownloadUrl = null;

    if (!empty($pdfFilesForZip)) {
        $zipFilename = 'Invoices_' . preg_replace('/[^a-zA-Z0-9]/', '', $defaultPlatform) . '_' . date('Ymd_His') . '.zip';
        $zipPath = $storageDir . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            foreach ($pdfFilesForZip as $localName => $filePath) {
                $zip->addFile($filePath, $localName);
            }
            $zip->close();

            if (file_exists($zipPath)) {
                $zipDownloadUrl = BASE_URL . 'api/download.php?file=' . urlencode($zipFilename);
            }
        }
    }

    echo json_encode([
        'success'          => true,
        'generated_count'  => count($generated),
        'zip_filename'     => $zipFilename,
        'zip_download_url' => $zipDownloadUrl,
        'invoices'         => $generated
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Generation error: ' . $e->getMessage()]);
}
