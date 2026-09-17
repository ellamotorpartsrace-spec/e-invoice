<?php
// api/export_csv.php
// Exports filtered invoices into BIR 2550Q compliant Sales Journal CSV / Excel spreadsheet

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isEinvLoggedIn()) {
    http_response_code(403);
    die("Access denied");
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $search    = trim($_GET['search'] ?? '');
    $platform  = trim($_GET['platform'] ?? '');
    $startDate = trim($_GET['start_date'] ?? '');
    $endDate   = trim($_GET['end_date'] ?? '');

    $where = ["1=1"];
    $params = [];

    if (!empty($search)) {
        $where[] = "(invoice_number LIKE ? OR order_sn LIKE ? OR buyer_name LIKE ? OR buyer_tin LIKE ?)";
        $p = "%{$search}%";
        $params = array_merge($params, [$p, $p, $p, $p]);
    }

    if (!empty($platform) && strtolower($platform) !== 'all') {
        $where[] = "platform_name = ?";
        $params[] = $platform;
    }

    if (!empty($startDate)) {
        $parsedStart = date('Y-m-d', strtotime($startDate));
        if ($parsedStart && $parsedStart !== '1970-01-01') {
            $where[] = "issue_date >= ?";
            $params[] = $parsedStart;
        }
    }

    if (!empty($endDate)) {
        $parsedEnd = date('Y-m-d', strtotime($endDate));
        if ($parsedEnd && $parsedEnd !== '1970-01-01') {
            $where[] = "issue_date <= ?";
            $params[] = $parsedEnd;
        }
    }

    $whereSql = implode(' AND ', $where);
    $stmt = $conn->prepare("SELECT * FROM einv_invoices WHERE $whereSql ORDER BY id ASC");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    $filename = 'BIR_Sales_Book_' . ($platform ? preg_replace('/[^a-zA-Z0-9]/', '', $platform) : 'All') . '_' . date('Ymd_His') . '.csv';

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output UTF-8 BOM so Excel displays special characters and Peso signs properly
    echo "\xEF\xBB\xBF";

    $output = fopen('php://output', 'w');

    // Header Row
    fputcsv($output, [
        'Invoice Number',
        'Issue Date',
        'Platform',
        'Order SN / Reference',
        'Buyer Registered Name',
        'Buyer TIN',
        'Buyer Address',
        'Gross Items Total (PHP)',
        'Shipping Fee (PHP)',
        'Discount Amount (PHP)',
        'Net VATable Sales (PHP)',
        '12% Output VAT (PHP)',
        'Total Amount Due (PHP)',
        'Status',
        'Created At'
    ]);

    foreach ($rows as $r) {
        fputcsv($output, [
            $r['invoice_number'],
            $r['issue_date'],
            $r['platform_name'] ?? 'Shopee',
            $r['order_sn'],
            $r['buyer_name'],
            $r['buyer_tin'] ?: '000-000-000-00000',
            $r['buyer_address'],
            number_format((float)$r['gross_items_total'], 2, '.', ''),
            number_format((float)$r['shipping_fee'], 2, '.', ''),
            number_format((float)$r['discount_amount'], 2, '.', ''),
            number_format((float)$r['vatable_sales'], 2, '.', ''),
            number_format((float)$r['vat_amount'], 2, '.', ''),
            number_format((float)$r['total_amount'], 2, '.', ''),
            strtoupper($r['status']),
            $r['created_at']
        ]);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    die("Export error: " . $e->getMessage());
}
