<?php
// api/download.php
// Secure downloader for generated PDF invoices and batch ZIP packages

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../classes/ShopeeInvoicePDF.php';

if (!isEinvLoggedIn()) {
    http_response_code(403);
    die("Access denied. Please log in.");
}

$file = $_GET['file'] ?? '';
if (empty($file)) {
    http_response_code(400);
    die("File parameter missing");
}

$fileName = basename($file);
$filePath = __DIR__ . '/../storage/invoices/' . $fileName;

$ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Auto-regenerate PDF if not found
if (!file_exists($filePath) && $ext === 'pdf') {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        $settings = getEinvSettings($conn);
        
        $stmt = $conn->prepare("SELECT * FROM einv_invoices WHERE pdf_filename = ? LIMIT 1");
        $stmt->execute([$fileName]);
        $inv = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($inv) {
            $invoicePayload = [
                'invoice_number'    => $inv['invoice_number'],
                'order_sn'          => $inv['order_sn'],
                'platform_name'     => $inv['platform_name'] ?? 'Shopee',
                'buyer_name'        => $inv['buyer_name'],
                'buyer_tin'         => $inv['buyer_tin'],
                'buyer_address'     => $inv['buyer_address'],
                'invoice_type'      => $inv['invoice_type'],
                'order_date'        => $inv['order_date'],
                'issue_date'        => $inv['issue_date'],
                'total_amount'      => (float)$inv['total_amount'],
                'vatable_sales'     => (float)$inv['vatable_sales'],
                'vat_amount'        => (float)$inv['vat_amount'],
                'shipping_fee'      => (float)$inv['shipping_fee'],
                'discount_amount'   => (float)$inv['discount_amount'],
                'gross_items_total' => (float)$inv['gross_items_total'],
                'items'             => json_decode($inv['items_json'] ?? '[]', true) ?: []
            ];

            $pdf = new ShopeeInvoicePDF($settings, $invoicePayload);
            $pdf->build();
            $pdf->Output('F', $filePath);
        }
    } catch (Exception $ex) {
        // Fall back to 404 below if generation fails
    }
}

if (!file_exists($filePath)) {
    http_response_code(404);
    die("File not found on server");
}

$mimeMap = [
    'pdf' => 'application/pdf',
    'zip' => 'application/zip'
];
$mime = $mimeMap[$ext] ?? 'application/octet-stream';

$disposition = (isset($_GET['view']) && $_GET['view'] == '1' && $ext === 'pdf') ? 'inline' : 'attachment';

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposition . '; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));
readfile($filePath);
exit;