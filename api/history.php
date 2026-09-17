<?php
// api/history.php
// Returns invoices archive list with server-side pagination and BIR tax summary KPIs

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!isEinvLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $search    = trim($_GET['search'] ?? '');
    $startDate = trim($_GET['start_date'] ?? '');
    $endDate   = trim($_GET['end_date'] ?? '');
    $platform  = trim($_GET['platform'] ?? '');

    $page      = max(1, (int)($_GET['page'] ?? 1));
    $perPage   = (int)($_GET['per_page'] ?? 50);
    if (!in_array($perPage, [25, 50, 100, 250])) {
        $perPage = 50;
    }

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

    // 1. KPI & Total Counts across whole filtered set
    $kpiStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_invoices,
            COALESCE(SUM(total_amount), 0) as total_gross,
            COALESCE(SUM(vatable_sales), 0) as total_vatable,
            COALESCE(SUM(vat_amount), 0) as total_vat
        FROM einv_invoices 
        WHERE $whereSql
    ");
    $kpiStmt->execute($params);
    $kpi = $kpiStmt->fetch();

    $totalRecords = (int)$kpi['total_invoices'];
    $totalPages = max(1, (int)ceil($totalRecords / $perPage));

    if ($page > $totalPages) {
        $page = $totalPages;
    }

    $offset = ($page - 1) * $perPage;
    if ($offset < 0) $offset = 0;

    $fromRecord = $totalRecords > 0 ? $offset + 1 : 0;
    $toRecord = min($offset + $perPage, $totalRecords);

    // 2. Paginated list
    $listSql = "
        SELECT 
            id, invoice_number, order_sn, platform_name, buyer_name, buyer_tin, invoice_type, 
            order_date, issue_date, gross_items_total, discount_amount, shipping_fee,
            total_amount, vatable_sales, vat_amount, pdf_filename, status, created_at
        FROM einv_invoices 
        WHERE $whereSql
        ORDER BY id DESC
        LIMIT $perPage OFFSET $offset
    ";
    $listStmt = $conn->prepare($listSql);
    $listStmt->execute($params);
    $invoices = $listStmt->fetchAll();

    echo json_encode([
        'success'    => true,
        'kpi'        => [
            'total_invoices' => $totalRecords,
            'total_gross'    => round((float)$kpi['total_gross'], 2),
            'total_vatable'  => round((float)$kpi['total_vatable'], 2),
            'total_vat'      => round((float)$kpi['total_vat'], 2),
        ],
        'pagination' => [
            'page'          => $page,
            'per_page'      => $perPage,
            'total_records' => $totalRecords,
            'total_pages'   => $totalPages,
            'from'          => $fromRecord,
            'to'            => $toRecord
        ],
        'invoices'   => $invoices
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
