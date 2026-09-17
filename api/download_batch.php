<?php
// api/download_batch.php
// Compiles selected or filtered invoices into a downloadable ZIP archive
// Automatically recovers/re-generates PDF if missing

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

try {
    $db = new Database();
    $conn = $db->getConnection();
    $settings = getEinvSettings($conn);

    $storageDir = __DIR__ . '/../storage/invoices';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0777, true);
    }

    $invoices = [];
    $mode = $input['mode'] ?? 'selected';

    if ($mode === 'selected' && !empty($input['ids']) && is_array($input['ids'])) {
        $ids = array_map('intval', $input['ids']);
        if (empty($ids)) {
            echo json_encode(['success' => false, 'error' => 'No invoices selected']);
            exit;
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $conn->prepare("SELECT * FROM einv_invoices WHERE id IN ($placeholders) ORDER BY id ASC");
        $stmt->execute($ids);
        $invoices = $stmt->fetchAll();
    } elseif ($mode === 'filtered') {
        $search    = trim($input['search'] ?? '');
        $platform  = trim($input['platform'] ?? '');
        $startDate = trim($input['start_date'] ?? '');
        $endDate   = trim($input['end_date'] ?? '');

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
        $invoices = $stmt->fetchAll();
    }

    if (empty($invoices)) {
        echo json_encode(['success' => false, 'error' => 'No invoices found matching criteria']);
        exit;
    }

    $zipName = 'Invoices_Batch_' . date('Ymd_His') . '_' . count($invoices) . 'files.zip';
    $zipPath = $storageDir . '/' . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        echo json_encode(['success' => false, 'error' => 'Could not create ZIP archive on server']);
        exit;
    }

    $addedCount = 0;
    foreach ($invoices as $inv) {
        $pdfFile = $inv['pdf_filename'] ?: ($inv['order_sn'] . '.pdf');
        $fullPath = $storageDir . '/' . $pdfFile;

        // Auto-regenerate PDF if not found on disk
        if (!file_exists($fullPath)) {
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

            try {
                $pdf = new ShopeeInvoicePDF($settings, $invoicePayload);
                $pdf->build();
                $pdf->Output('F', $fullPath);
            } catch (Exception $ex) {
                continue;
            }
        }

        if (file_exists($fullPath)) {
            // Give clean filename inside ZIP: SI-SHP-2026-00001_orderSn.pdf
            $safeInvNo = preg_replace('/[^a-zA-Z0-9_-]/', '', $inv['invoice_number']);
            $entryName = $safeInvNo . '_' . $inv['order_sn'] . '.pdf';
            $zip->addFile($fullPath, $entryName);
            $addedCount++;
        }
    }

    $zip->close();

    if ($addedCount === 0) {
        @unlink($zipPath);
        echo json_encode(['success' => false, 'error' => 'Failed to pack PDF invoices into ZIP']);
        exit;
    }

    echo json_encode([
        'success'      => true,
        'count'        => $addedCount,
        'zip_filename' => $zipName,
        'download_url' => BASE_URL . 'api/download.php?file=' . urlencode($zipName)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Batch processing error: ' . $e->getMessage()]);
}
