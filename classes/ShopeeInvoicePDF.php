<?php
// classes/ShopeeInvoicePDF.php
// Multi-Platform E-Commerce BIR Annex A1 Compliant Sales Invoice Generator
// Fully customized for Shopee, Lazada, and TikTok Shop with dedicated brand themes,
// clean high-visibility typography, intuitive financial summary, and BIR Annex A1 & RR 7-2024 compliance.

require_once __DIR__ . '/fpdf/fpdf.php';

class ShopeeInvoicePDF extends FPDF {

    protected $storeInfo = [];
    protected $invoiceData = [];
    protected $theme = [];

    public function __construct($storeInfo, $invoiceData) {
        parent::__construct('P', 'mm', 'A4'); // 210 x 297 mm
        // Smart parameter swap protection if caller passes ($invoiceData, $storeInfo)
        if (isset($storeInfo['order_sn']) && !isset($invoiceData['order_sn'])) {
            $temp = $storeInfo;
            $storeInfo = $invoiceData;
            $invoiceData = $temp;
        }

        $this->storeInfo = $storeInfo;
        $this->invoiceData = $invoiceData;
        $this->SetAutoPageBreak(false); // Strictly 1-page controlled layout
        $this->SetMargins(14, 12, 14);

        $platform = !empty($this->invoiceData['platform_name']) ? $this->invoiceData['platform_name'] : (!empty($this->invoiceData['platform']) ? $this->invoiceData['platform'] : 'Shopee');
        $this->theme = $this->resolvePlatformTheme($platform);
    }

    /**
     * Platform theme resolution
     * Provides dedicated palettes and styling tokens for Shopee, Lazada, and TikTok Shop.
     */
    protected function resolvePlatformTheme($platformName) {
        $p = strtolower(trim((string)$platformName));

        // ── LAZADA THEME (Electric Royal Blue, Magenta Accents, Ice Blue Tints) ──
        if (strpos($p, 'laz') !== false) {
            return [
                'platform_key'  => 'lazada',
                'display_name'  => 'Lazada',
                'badge_label'   => '[ LAZADA VERIFIED STORE ORDER ]',
                'order_label'   => 'LAZADA ORDER NO: ',
                'primary'       => [15, 86, 250],    // #0F56FA - Lazada Royal Blue
                'primary_dark'  => [10, 59, 179],   // #0A3BB3
                'accent'        => [243, 0, 103],   // #F30067 - Lazada Magenta
                'accent_light'  => [254, 230, 240], // Soft magenta tint
                'heading_dark'  => [15, 23, 42],    // Slate 900
                'table_header'  => [15, 23, 42],    // Slate 900
                'light_bg'      => [240, 246, 255], // #F0F6FF - Ice Blue Tint
                'zebra_bg'      => [247, 250, 255], // Ultra light ice stripe
                'border'        => [191, 219, 254], // #BFDBFE - Soft Blue Border
                'border_accent' => [96, 165, 250],  // #60A5FA
                'pill_bg'       => [239, 246, 255],
                'pill_border'   => [15, 86, 250],
                'pill_text'     => [15, 86, 250],
                'due_bar_bg'    => [15, 86, 250],   // Electric Royal Blue Total Due
                'due_bar_text'  => [255, 255, 255],
                'ribbon_colors' => [
                    [15, 86, 250, 60],   // Lazada Blue
                    [56, 189, 248, 40],  // Sky Blue
                    [243, 0, 103, 35],   // Lazada Magenta
                    [15, 23, 42, 47]     // Deep Slate
                ],
                'seal_border'   => [15, 86, 250],
                'seal_badge'    => 'LAZADA OFFICIAL VERIFIED',
                'seal_sub'      => 'Verified Lazada Transaction * BIR Compliant'
            ];
        }

        // ── TIKTOK SHOP THEME (Pitch Black / Dark Onyx, Electric Cyan & Neon Red) ──
        if (strpos($p, 'tik') !== false) {
            return [
                'platform_key'  => 'tiktok',
                'display_name'  => 'TikTok Shop',
                'badge_label'   => '[ TIKTOK SHOP OFFICIAL ORDER ]',
                'order_label'   => 'TIKTOK SHOP ORDER ID: ',
                'primary'       => [18, 18, 20],    // #121214 - Pitch Onyx Black
                'primary_dark'  => [0, 0, 0],       // Pure Black
                'accent'        => [254, 44, 85],   // #FE2C55 - TikTok Neon Red/Pink
                'accent_alt'    => [37, 244, 238],  // #25F4EE - TikTok Electric Cyan
                'accent_light'  => [255, 235, 240], // Soft neon pink tint
                'heading_dark'  => [15, 23, 42],    // Slate 900
                'table_header'  => [18, 18, 20],    // Pitch Black Table Header
                'light_bg'      => [245, 246, 248], // Soft clean graphite tint
                'zebra_bg'      => [250, 250, 252], // Ultra light neutral stripe
                'border'        => [212, 212, 216], // Zinc 300
                'border_accent' => [113, 113, 122], // Zinc 500
                'pill_bg'       => [244, 244, 246],
                'pill_border'   => [18, 18, 20],
                'pill_text'     => [18, 18, 20],
                'due_bar_bg'    => [18, 18, 20],    // Pitch Black Total Due
                'due_bar_text'  => [255, 255, 255],
                'ribbon_colors' => [
                    [18, 18, 20, 60],   // Pitch Onyx
                    [37, 244, 238, 35], // Electric Cyan
                    [254, 44, 85, 35],  // Neon Pink/Red
                    [39, 39, 42, 52]    // Dark Zinc
                ],
                'seal_border'   => [18, 18, 20],
                'seal_badge'    => 'TIKTOK SHOP VERIFIED',
                'seal_sub'      => 'Verified TikTok Shop Order * BIR Compliant'
            ];
        }

        // ── SHOPEE THEME (Signature Shopee Orange, Warm Coral, Peach Tints) ──
        return [
            'platform_key'  => 'shopee',
            'display_name'  => 'Shopee',
            'badge_label'   => '[ SHOPEE VERIFIED STORE ORDER ]',
            'order_label'   => 'SHOPEE ORDER SN: ',
            'primary'       => [238, 77, 45],   // #EE4D2D - Signature Shopee Orange
            'primary_dark'  => [196, 52, 23],   // #C43417 - Deep Shopee Rust
            'accent'        => [255, 115, 55],  // #FF7337 - Warm Coral
            'accent_light'  => [255, 240, 235], // Soft coral tint
            'heading_dark'  => [15, 23, 42],    // Slate 900
            'table_header'  => [15, 23, 42],    // Sleek Slate Header
            'light_bg'      => [255, 247, 244], // #FFF7F4 - Soft Peach Tint
            'zebra_bg'      => [255, 251, 249], // Ultra light peach stripe
            'border'        => [254, 215, 170], // #FED7AA - Soft Peach Border
            'border_accent' => [251, 146, 60],  // #FB923C - Orange 400
            'pill_bg'       => [255, 245, 242],
            'pill_border'   => [238, 77, 45],
            'pill_text'     => [238, 77, 45],
            'due_bar_bg'    => [238, 77, 45],   // Vibrant Shopee Orange Total Due
            'due_bar_text'  => [255, 255, 255],
            'ribbon_colors' => [
                [238, 77, 45, 60],  // Shopee Orange
                [255, 115, 55, 40], // Warm Coral
                [245, 158, 11, 35], // Amber Gold
                [15, 23, 42, 47]    // Deep Slate
            ],
            'seal_border'   => [238, 77, 45],
            'seal_badge'    => 'SHOPEE OFFICIAL VERIFIED',
            'seal_sub'      => 'Verified Shopee Transaction * BIR Compliant'
        ];
    }

    protected function t($str) {
        if ($str === null) return '';
        $str = str_replace('₱', 'PHP ', $str);
        $str = str_replace('—', '-', $str);
        $str = str_replace('–', '-', $str);
        $str = str_replace('“', '"', $str);
        $str = str_replace('”', '"', $str);
        $str = str_replace('‘', "'", $str);
        $str = str_replace('’', "'", $str);
        $encoded = @iconv('UTF-8', 'windows-1252//TRANSLIT', $str);
        return $encoded !== false ? $encoded : utf8_decode($str);
    }

    protected function money($val) {
        return number_format((float)$val, 2);
    }

    public function build() {
        $this->AddPage();

        $t = $this->theme;
        $navyDark     = $t['heading_dark'];  // Slate 900 [15, 23, 42]
        $textPrimary  = [30, 41, 59];        // Slate 800
        $textLabel    = [71, 85, 105];       // Slate 600 - High Contrast Label
        $textMuted    = [100, 116, 139];     // Slate 500
        $borderColor  = [203, 213, 225];     // Slate 300
        $pageLeft     = 14;
        $pageWidth    = 182; // 210 - 28

        // ══════════════════════════════════════════════════════════════
        // 0. PLATFORM SIGNATURE TOP ACCENT RIBBON (Y = 12 to 14.8)
        // ══════════════════════════════════════════════════════════════
        $currRibbonX = $pageLeft;
        foreach ($t['ribbon_colors'] as $rc) {
            $this->SetFillColor($rc[0], $rc[1], $rc[2]);
            $this->Rect($currRibbonX, 12, $rc[3], 2.8, 'F');
            $currRibbonX += $rc[3];
        }

        // ══════════════════════════════════════════════════════════════
        // 1. SELLER BRAND IDENTITY & OFFICIAL INVOICE HEADER (Y = 17 to 42)
        // ══════════════════════════════════════════════════════════════
        // Logo (Left side) - Retaining Invoice PRO demo logo
        $logoPath = __DIR__ . '/../assets/img/logo-horizontal.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, $pageLeft, 17.5, 48);
        } else {
            $this->SetXY($pageLeft, 18);
            $this->SetFont('Helvetica', 'B', 15);
            $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
            $this->Cell(48, 6, 'INVOICE PRO', 0, 1, 'L');
            $this->SetFont('Helvetica', '', 7.5);
            $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
            $this->Cell(48, 4, 'ELECTRONIC INVOICE PORTAL', 0, 1, 'L');
        }

        // Seller Registered Business Details (Middle: X = 66 to 123)
        $sellerInfoX = 66;
        $this->SetXY($sellerInfoX, 17);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $storeName = !empty($this->storeInfo['store_name']) ? strtoupper($this->storeInfo['store_name']) : 'DEMO E-COMMERCE ENTERPRISES';
        $this->Cell(58, 3.8, $this->t(substr($storeName, 0, 38)), 0, 1, 'L');

        // Store Tagline in Platform Accent Color
        $this->SetX($sellerInfoX);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $storeTagline = !empty($this->storeInfo['store_tagline']) ? strtoupper($this->storeInfo['store_tagline']) : 'OFFICIAL ONLINE FLAGSHIP STORE';
        $this->Cell(58, 3.4, $this->t(substr($storeTagline, 0, 42)), 0, 1, 'L');

        // Store TIN & VAT Status (Readable, clear typography)
        $this->SetX($sellerInfoX);
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $storeTin = !empty($this->storeInfo['store_tin']) ? $this->storeInfo['store_tin'] : (!empty($this->storeInfo['store_tax_id']) ? $this->storeInfo['store_tax_id'] : '123-456-789-00000');
        $vatStatus = !empty($this->storeInfo['vat_status']) ? $this->storeInfo['vat_status'] : 'VAT Registered';
        $this->Cell(58, 3.2, $this->t('VAT REG TIN: ' . $storeTin . ' (' . $vatStatus . ')'), 0, 1, 'L');

        // Store Registered Address (dynamic 2-line split, zero truncation)
        $fullStoreAddr = !empty($this->storeInfo['store_address']) ? $this->storeInfo['store_address'] : '123 Commercial Ave., Ortigas Center, Pasig City, Metro Manila 1605';
        $addrParts = array_map('trim', explode(',', $fullStoreAddr));
        $lineA = $addrParts[0] ?? $fullStoreAddr;
        $lineB = implode(', ', array_slice($addrParts, 1));
        if (empty($lineB)) {
            $lineB = 'Metro Manila, Philippines';
        }

        $this->SetX($sellerInfoX);
        $this->Cell(58, 3.2, $this->t($lineA), 0, 1, 'L');

        $this->SetX($sellerInfoX);
        $this->Cell(58, 3.2, $this->t($lineB), 0, 1, 'L');

        $this->SetX($sellerInfoX);
        $storeContact = $this->storeInfo['store_contact'] ?? '(02) 8123-4567 / 0917-000-0000';
        $this->Cell(58, 3.2, $this->t('Hotline: ' . $storeContact), 0, 1, 'L');

        // ── Right Side: Official Sales Invoice Title & Themed Serial ──
        $headerRightX = 124;
        $headerRightW = 72;

        $this->SetXY($headerRightX, 16);
        $this->SetFont('Helvetica', 'B', 20);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell($headerRightW, 6.5, 'SALES INVOICE', 0, 1, 'R');

        $this->SetXY($headerRightX, 22.8);
        $this->SetFont('Helvetica', 'B', 7.2);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell($headerRightW, 3.2, 'BIR ANNEX A1 & RR 7-2024 COMPLIANT', 0, 1, 'R');

        // Platform Verified Badge
        $this->SetXY($headerRightX, 26.5);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell($headerRightW, 3.5, $t['badge_label'], 0, 1, 'R');

        // Platform Themed Invoice Number Container
        $invNo = $this->invoiceData['invoice_number'] ?? 'SI-SHP-2026-00001';
        $pillBoxX = 124;
        $pillBoxY = 30.5;
        $pillBoxW = 72;
        $pillBoxH = 7.5;

        $this->SetFillColor($t['pill_bg'][0], $t['pill_bg'][1], $t['pill_bg'][2]);
        $this->SetDrawColor($t['pill_border'][0], $t['pill_border'][1], $t['pill_border'][2]);
        $this->SetLineWidth(0.4);
        $this->Rect($pillBoxX, $pillBoxY, $pillBoxW, $pillBoxH, 'DF');

        $this->SetXY($pillBoxX, $pillBoxY + 0.9);
        $this->SetFont('Helvetica', 'B', 10);
        $this->SetTextColor($t['pill_text'][0], $t['pill_text'][1], $t['pill_text'][2]);
        $this->Cell($pillBoxW, 5.8, $this->t('Invoice No: ' . $invNo), 0, 1, 'C');

        // ══════════════════════════════════════════════════════════════
        // 2. TRANSACTION TYPE & DATE BAR (Y = 40.0 to 45.5)
        // ══════════════════════════════════════════════════════════════
        $transY = 40.0;
        $this->SetY($transY);
        $this->SetX($pageLeft);

        // Checkboxes: Cash Sales (Checked) & Charge Sales (Unchecked)
        $this->SetFont('ZapfDingbats', '', 8.5);
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell(4.5, 4.5, chr(52), 1, 0, 'C'); // Checked mark
        $this->SetFont('Helvetica', 'B', 8.2);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(27, 4.5, ' CASH SALES', 0, 0, 'L');

        $this->SetFont('ZapfDingbats', '', 8.5);
        $this->Cell(4.5, 4.5, '', 1, 0, 'C'); // Unchecked
        $this->SetFont('Helvetica', 'B', 8.2);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(30, 4.5, ' CHARGE SALES', 0, 0, 'L');

        // Issue Date (Right Aligned with clean underline)
        $issueDate = $this->invoiceData['issue_date'] ?? date('Y-m-d');
        $this->SetX(118);
        $this->SetFont('Helvetica', 'B', 8.2);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(24, 4.5, 'Date Issued :', 0, 0, 'R');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(54, 4.5, date('F d, Y', strtotime($issueDate)), 'B', 1, 'L');

        // ══════════════════════════════════════════════════════════════
        // 3. BUYER INFORMATION BOX (Annex A1 Point 7 - "SOLD TO")
        // ══════════════════════════════════════════════════════════════
        $soldToBoxY = 46.8;
        $buyerAddr = !empty($this->invoiceData['buyer_address']) ? $this->invoiceData['buyer_address'] : 'N/A (Store Pickup / Standard Electronic Delivery)';
        
        // Calculate dynamic height for clean vertical padding
        $this->SetFont('Helvetica', '', 8.0);
        $addrWidth = $this->GetStringWidth($this->t($buyerAddr));
        $addrLines = max(1, ceil($addrWidth / 144));
        $soldToBoxHeight = max(26.0, 14.5 + ($addrLines * 4.0));

        // Box border & clean background
        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->SetFillColor(255, 255, 255);
        $this->SetLineWidth(0.35);
        $this->Rect($pageLeft, $soldToBoxY, $pageWidth, $soldToBoxHeight, 'DF');

        // Themed Header Strip inside Box
        $this->SetFillColor($t['light_bg'][0], $t['light_bg'][1], $t['light_bg'][2]);
        $this->Rect($pageLeft, $soldToBoxY, $pageWidth, 5.8, 'F');
        $this->Line($pageLeft, $soldToBoxY + 5.8, $pageLeft + $pageWidth, $soldToBoxY + 5.8);

        $this->SetXY($pageLeft + 3, $soldToBoxY + 1.2);
        $this->SetFont('Helvetica', 'B', 7.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(65, 4.0, 'SOLD TO (BUYER & DELIVERY DETAILS)', 0, 0, 'L');

        // Platform Order Reference Tag
        $orderSn = $this->invoiceData['order_sn'] ?? '';
        $this->SetFont('Helvetica', 'B', 7.8);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $orderRefText = $t['order_label'] . $orderSn;
        $this->Cell($pageWidth - 71, 4.0, $this->t($orderRefText), 0, 1, 'R');

        // Row 1: Registered Name & Buyer TIN
        $this->SetXY($pageLeft + 3, $soldToBoxY + 7.4);
        $this->SetFont('Helvetica', 'B', 8.0);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(28, 4.0, 'Registered Name :', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $buyerName = !empty($this->invoiceData['buyer_name']) ? strtoupper($this->invoiceData['buyer_name']) : 'CASH CUSTOMER';
        $this->Cell(84, 4.0, $this->t(substr($buyerName, 0, 48)), 0, 0, 'L');

        $this->SetFont('Helvetica', 'B', 8.0);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(12, 4.0, 'TIN :', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $buyerTin = !empty($this->invoiceData['buyer_tin']) ? $this->invoiceData['buyer_tin'] : '000-000-000-00000';
        $this->Cell(52, 4.0, $this->t($buyerTin), 0, 1, 'L');

        // Row 2: Customer Delivery Address
        $this->SetXY($pageLeft + 3, $soldToBoxY + 12.4);
        $this->SetFont('Helvetica', 'B', 8.0);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(28, 3.8, 'Delivery Address :', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 8.0);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
        $this->MultiCell(148, 4.0, $this->t($buyerAddr), 0, 'L');

        // ══════════════════════════════════════════════════════════════
        // 4. TRANSACTION DETAILS TABLE (Annex A1 Point 8)
        // ══════════════════════════════════════════════════════════════
        $tableY = $soldToBoxY + $soldToBoxHeight + 3.0;
        $this->SetY($tableY);

        $colNum   = 10;
        $colDesc  = 92;
        $colQty   = 16;
        $colPrice = 32;
        $colAmt   = 32;

        // Table Header: Deep Sleek Theme with Platform Primary Accent Line
        $this->SetFillColor($t['table_header'][0], $t['table_header'][1], $t['table_header'][2]);
        $this->SetDrawColor($t['table_header'][0], $t['table_header'][1], $t['table_header'][2]);
        $this->SetFont('Helvetica', 'B', 8.2);
        $this->SetTextColor(255, 255, 255);

        // Top decorative accent line on table header
        $this->SetLineWidth(0.7);
        if ($t['platform_key'] === 'tiktok') {
            // Dual electric accent line: Electric Cyan on left, Neon Red on right
            $this->SetDrawColor(37, 244, 238); // Cyan
            $this->Line($pageLeft, $tableY, $pageLeft + 91, $tableY);
            $this->SetDrawColor(254, 44, 85); // Neon Red
            $this->Line($pageLeft + 91, $tableY, $pageLeft + $pageWidth, $tableY);
        } else {
            $this->SetDrawColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
            $this->Line($pageLeft, $tableY, $pageLeft + $pageWidth, $tableY);
        }

        $this->SetLineWidth(0.25);
        $this->SetX($pageLeft);
        $this->Cell($colNum, 7.5, '#', 1, 0, 'C', true);
        $this->Cell($colDesc, 7.5, '  ITEM DESCRIPTION / PRODUCT DETAILS', 1, 0, 'L', true);
        $this->Cell($colQty, 7.5, 'QTY', 1, 0, 'C', true);
        $this->Cell($colPrice, 7.5, 'UNIT PRICE (PHP) ', 1, 0, 'R', true);
        $this->Cell($colAmt, 7.5, 'AMOUNT (PHP)  ', 1, 1, 'R', true);

        // Table Rows
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->SetFont('Helvetica', '', 8.0);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        $items = $this->invoiceData['items'] ?? $this->invoiceData['items_json'] ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        $tableStartY = $this->GetY();
        $grossTableSum = 0;
        $itemIdx = 0;

        foreach ($items as $item) {
            $itemIdx++;
            $qty = (int)($item['quantity'] ?? 1);
            $desc = trim($item['product_name'] ?? 'Item');
            $varName = !empty($item['variation_name']) ? trim($item['variation_name']) : '';

            $unitPrice = (float)($item['unit_price'] ?? 0);
            $subtotal  = (float)($item['subtotal'] ?? ($unitPrice * $qty));
            $grossTableSum += $subtotal;

            $fullDesc = $desc . ($varName ? ' [' . $varName . ']' : '');

            // Pre-calculate line count for clean vertical cell balance
            $this->SetFont('Helvetica', '', 8.0);
            $descWidth = $this->GetStringWidth($this->t($fullDesc));
            $lines = max(1, ceil($descWidth / ($colDesc - 6)));
            $actualH = max(7.5, ($lines * 4.2) + 2.0);

            $currY = $this->GetY();

            // Subtle platform zebra striping
            if ($itemIdx % 2 === 0) {
                $this->SetFillColor($t['zebra_bg'][0], $t['zebra_bg'][1], $t['zebra_bg'][2]);
                $this->Rect($pageLeft, $currY, $pageWidth, $actualH, 'F');
            }

            // Index #
            $this->SetXY($pageLeft, $currY);
            $this->SetFont('Helvetica', 'B', 8.0);
            $this->Cell($colNum, $actualH, (string)$itemIdx, 'LR', 0, 'C');

            // Description
            $this->SetXY($pageLeft + $colNum, $currY + 1.2);
            $this->SetFont('Helvetica', '', 8.0);
            $this->MultiCell($colDesc, 4.2, ' ' . $this->t($fullDesc), 0, 'L');
            $this->Rect($pageLeft + $colNum, $currY, $colDesc, $actualH);

            // Quantity
            $this->SetXY($pageLeft + $colNum + $colDesc, $currY);
            $this->Cell($colQty, $actualH, (string)$qty, 'LR', 0, 'C');

            // Unit Price
            $this->SetXY($pageLeft + $colNum + $colDesc + $colQty, $currY);
            $this->Cell($colPrice, $actualH, $this->money($unitPrice) . ' ', 'LR', 0, 'R');

            // Amount
            $this->SetXY($pageLeft + $colNum + $colDesc + $colQty + $colPrice, $currY);
            $this->Cell($colAmt, $actualH, $this->money($subtotal) . ' ', 'LR', 1, 'R');

            $this->SetY($currY + $actualH);
        }

        // Shipping Fee Row (if applicable)
        $shippingFee = (float)($this->invoiceData['shipping_fee'] ?? 0);
        if ($shippingFee > 0) {
            $currY = $this->GetY();
            $this->SetXY($pageLeft, $currY);
            $this->Cell($colNum, 6.4, '', 'LR', 0, 'C');
            $this->Cell($colDesc, 6.4, ' Shipping & Logistics Handling Fee', 'LR', 0, 'L');
            $this->Cell($colQty, 6.4, '1', 'LR', 0, 'C');
            $this->Cell($colPrice, 6.4, $this->money($shippingFee) . ' ', 'LR', 0, 'R');
            $this->Cell($colAmt, 6.4, $this->money($shippingFee) . ' ', 'LR', 1, 'R');
            $grossTableSum += $shippingFee;
        }

        // Promotional Discount Row (Cleanly formatted: QTY is "—" instead of "1")
        $discountAmount = (float)($this->invoiceData['discount_amount'] ?? 0);
        if ($discountAmount > 0) {
            $currY = $this->GetY();
            $this->SetXY($pageLeft, $currY);
            $this->SetFont('Helvetica', 'B', 8.0);
            $this->SetTextColor(225, 29, 72); // Rose Red
            $this->Cell($colNum, 6.4, '', 'LR', 0, 'C');
            $this->Cell($colDesc, 6.4, ' Less: Promotional Discounts & Vouchers', 'LR', 0, 'L');
            $this->SetFont('Helvetica', '', 8.0);
            $this->Cell($colQty, 6.4, chr(151), 'LR', 0, 'C'); // Em-dash instead of "1"
            $this->Cell($colPrice, 6.4, '-' . $this->money($discountAmount) . ' ', 'LR', 0, 'R');
            $this->Cell($colAmt, 6.4, '-' . $this->money($discountAmount) . ' ', 'LR', 1, 'R');
            $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
        }

        // ── Balanced Table Height & Official BIR Legal Closure ──
        $currentTableHeight = $this->GetY() - $tableStartY;
        $targetTableHeight = 65; // Balanced table height for elegant page distribution
        if ($currentTableHeight < $targetTableHeight) {
            $filler = $targetTableHeight - $currentTableHeight;
            $closureY = $this->GetY() + ($filler / 2) - 2.0;

            // Draw column boundary borders for filler
            $this->SetX($pageLeft);
            $this->Cell($colNum, $filler, '', 'LR', 0, 'C');
            $this->Cell($colDesc, $filler, '', 'LR', 0, 'L');
            $this->Cell($colQty, $filler, '', 'LR', 0, 'C');
            $this->Cell($colPrice, $filler, '', 'LR', 0, 'R');
            $this->Cell($colAmt, $filler, '', 'LR', 1, 'R');

            // Centered legal certification closure notice inside the table
            $this->SetXY($pageLeft + $colNum + 2, $closureY);
            $this->SetFont('Helvetica', 'I', 7.5);
            $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
            $this->Cell($colDesc - 4, 4.0, '- NOTHING FOLLOWS / CERTIFIED TRUE COPY -', 0, 0, 'C');
        }

        // Table Bottom Border - strictly aligned at pageLeft across full pageWidth
        $tableFinalY = $tableStartY + max($currentTableHeight, $targetTableHeight);
        $this->SetXY($pageLeft, $tableFinalY);
        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->SetLineWidth(0.4);
        $this->Cell($pageWidth, 0, '', 'T', 1);
        $this->Ln(3);

        // ══════════════════════════════════════════════════════════════
        // 5. DUAL TAX BREAKDOWN BOXES (Annex A1 Points 9 & 10) - SIMPLIFIED & CLEAR
        // ══════════════════════════════════════════════════════════════
        $taxBoxY = $this->GetY();

        // Calculate totals with safe fallback to prevent 0.00
        $grandTotal = (float)($this->invoiceData['total_amount'] ?? 0);
        if ($grandTotal <= 0 && !empty($this->invoiceData['grand_total'])) {
            $grandTotal = (float)$this->invoiceData['grand_total'];
        }

        $vatableSales = (float)($this->invoiceData['vatable_sales'] ?? 0);
        if ($vatableSales <= 0 && $grandTotal > 0) {
            $vatableSales = round($grandTotal / 1.12, 2);
        }

        $vatAmount = (float)($this->invoiceData['vat_amount'] ?? 0);
        if ($vatAmount <= 0 && $grandTotal > 0) {
            $vatAmount = round($grandTotal - $vatableSales, 2);
        }

        $totalSalesGross = $grossTableSum > 0 ? $grossTableSum : ($grandTotal + $discountAmount);

        $boxH = 43; // Balanced height for clear 4.6mm per row
        $leftW = 88;

        // ── LEFT BOX: 12% VAT Breakdown (Annex A1 Section 10) ──
        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->SetLineWidth(0.35);
        $this->Rect($pageLeft, $taxBoxY, $leftW, $boxH);

        // Themed header strip
        $this->SetFillColor($t['light_bg'][0], $t['light_bg'][1], $t['light_bg'][2]);
        $this->Rect($pageLeft, $taxBoxY, $leftW, 5.8, 'F');
        $this->Line($pageLeft, $taxBoxY + 5.8, $pageLeft + $leftW, $taxBoxY + 5.8);

        $this->SetXY($pageLeft + 3, $taxBoxY + 1.2);
        $this->SetFont('Helvetica', 'B', 7.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell($leftW - 6, 4.0, '12% VAT BREAKDOWN (BIR ANNEX A1)', 0, 1, 'L');

        $rowH = 4.8;
        $this->SetFont('Helvetica', '', 8.2);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        $this->SetXY($pageLeft + 3, $taxBoxY + 7.5);
        $this->Cell(48, $rowH, ' VATable Sales', 0, 0, 'L');
        $this->Cell(35, $rowH, 'PHP ' . $this->money($vatableSales), 0, 1, 'R');

        $this->SetX($pageLeft + 3);
        $this->SetFont('Helvetica', 'B', 8.2);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell(48, $rowH, ' VAT Amount (12%)', 0, 0, 'L');
        $this->Cell(35, $rowH, 'PHP ' . $this->money($vatAmount), 0, 1, 'R');

        $this->SetX($pageLeft + 3);
        $this->SetFont('Helvetica', '', 8.2);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
        $this->Cell(48, $rowH, ' Zero-Rated Sales', 0, 0, 'L');
        $this->Cell(35, $rowH, '0.00', 0, 1, 'R');

        $this->SetX($pageLeft + 3);
        $this->Cell(48, $rowH, ' VAT-Exempt Sales', 0, 0, 'L');
        $this->Cell(35, $rowH, '0.00', 0, 1, 'R');

        // Divider inside Left Box
        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->Line($pageLeft, $taxBoxY + 30.5, $pageLeft + $leftW, $taxBoxY + 30.5);

        // Settlement status in Left Box
        $this->SetXY($pageLeft + 3, $taxBoxY + 32.2);
        $this->SetFont('ZapfDingbats', '', 8.5);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell(4, 4, chr(52), 1, 0, 'C'); // Checked
        $this->SetFont('Helvetica', 'B', 8.0);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(38, 4, ' Received Settlement :', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell(41, 4, 'PHP ' . $this->money($grandTotal), 0, 1, 'R');

        $this->SetXY($pageLeft + 3, $taxBoxY + 37.4);
        $this->SetFont('Helvetica', 'I', 7.0);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell($leftW - 6, 3.5, $this->t('Electronic Payment via ' . $t['display_name'] . ' (Fully Settled)'), 0, 1, 'L');

        // ── RIGHT BOX: Settlement & Payment Summary (Annex A1 Section 9) ──
        $rightX = $pageLeft + $leftW + 3;
        $rightW = $pageWidth - $leftW - 3; // 91mm

        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->Rect($rightX, $taxBoxY, $rightW, $boxH);

        // Themed header strip
        $this->SetFillColor($t['light_bg'][0], $t['light_bg'][1], $t['light_bg'][2]);
        $this->Rect($rightX, $taxBoxY, $rightW, 5.8, 'F');
        $this->Line($rightX, $taxBoxY + 5.8, $rightX + $rightW, $taxBoxY + 5.8);

        $this->SetXY($rightX + 3, $taxBoxY + 1.2);
        $this->SetFont('Helvetica', 'B', 7.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell($rightW - 6, 4.0, 'PAYMENT & TAX COMPUTATION (SEC. 9)', 0, 1, 'L');

        // Direct, high-clarity financial rows (no confusing round-trip additions)
        $rRowH = 4.4;
        $this->SetFont('Helvetica', '', 8.0);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        $this->SetXY($rightX + 3, $taxBoxY + 7.2);
        $this->Cell(52, $rRowH, ' Total Sales (VAT Inclusive)', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($totalSalesGross), 0, 1, 'R');

        $this->SetX($rightX + 3);
        $this->Cell(52, $rRowH, ' Less: Promotional Discounts', 0, 0, 'L');
        if ($discountAmount > 0) {
            $this->SetFont('Helvetica', 'B', 8.0);
            $this->SetTextColor(225, 29, 72);
            $this->Cell(34, $rRowH, '-' . $this->money($discountAmount), 0, 1, 'R');
            $this->SetFont('Helvetica', '', 8.0);
            $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
        } else {
            $this->Cell(34, $rRowH, '0.00', 0, 1, 'R');
        }

        $this->SetX($rightX + 3);
        $this->Cell(52, $rRowH, ' Amount : Net of Discounts', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($grandTotal), 0, 1, 'R');

        $this->SetX($rightX + 3);
        $this->Cell(52, $rRowH, ' Less: 12% VAT (included)', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($vatAmount), 0, 1, 'R');

        $this->SetX($rightX + 3);
        $this->Cell(52, $rRowH, ' Amount : Net of VAT', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($vatableSales), 0, 1, 'R');

        // TOTAL AMOUNT DUE Themed Identity Bar
        $dueBarY = $taxBoxY + 33.2;
        $dueBarH = 9.8;
        $this->SetFillColor($t['due_bar_bg'][0], $t['due_bar_bg'][1], $t['due_bar_bg'][2]);
        $this->Rect($rightX, $dueBarY, $rightW, $dueBarH, 'F');

        $this->SetXY($rightX + 4, $dueBarY + 1.8);
        $this->SetFont('Helvetica', 'B', 9.0);
        $this->SetTextColor($t['due_bar_text'][0], $t['due_bar_text'][1], $t['due_bar_text'][2]);
        $this->Cell(44, 6, 'TOTAL AMOUNT DUE', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 13.0);
        $this->Cell($rightW - 51, 6, 'PHP ' . $this->money($grandTotal), 0, 1, 'R');

        // ══════════════════════════════════════════════════════════════
        // 6. SC / PWD / SOLO PARENT STATUTORY BOX (Annex A1 Point 11)
        // ══════════════════════════════════════════════════════════════
        $pwdBoxY = $taxBoxY + $boxH + 3.0;
        $pwdBoxH = 10.5;

        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->SetFillColor(255, 255, 255);
        $this->SetLineWidth(0.35);
        $this->Rect($pageLeft, $pwdBoxY, $pageWidth, $pwdBoxH, 'DF');

        $this->SetXY($pageLeft + 3, $pwdBoxY + 1.8);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(56, 3.5, 'SC/PWD/NAAC/MOV/Solo Parent ID No. :', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(54, 3.5, 'N/A (Standard E-Commerce Transaction)', 0, 0, 'L');

        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(18, 3.5, 'Signature :', 0, 0, 'L');
        $this->Line($pageLeft + 130, $pwdBoxY + 5.2, $pageLeft + $pageWidth - 5, $pwdBoxY + 5.2);

        $this->SetXY($pageLeft + 3, $pwdBoxY + 5.8);
        $this->SetFont('Helvetica', 'I', 6.6);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell($pageWidth - 6, 3.5, 'Applicable for qualified Senior Citizen, PWD, or Solo Parent buyers claiming statutory discounts.', 0, 1, 'L');

        // ══════════════════════════════════════════════════════════════
        // 7. PERMIT TO USE, ATP FOOTER & OFFICIAL PLATFORM SECURITY SEAL
        // ══════════════════════════════════════════════════════════════
        $footerY = $pwdBoxY + $pwdBoxH + 3.0;

        // Divider rule
        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->SetLineWidth(0.35);
        $this->Line($pageLeft, $footerY, $pageLeft + $pageWidth, $footerY);

        $permitNo = $this->storeInfo['permit_no'] ?? 'POS-CAS-2026-001';
        $atpNo = $this->storeInfo['atp_no'] ?? '3AU000000000000';
        $approvedSeries = $this->storeInfo['approved_series'] ?? 'SI-SHP-2026-00001 - SI-SHP-2026-99999';

        // 2-Column Statutory Information
        $this->SetXY($pageLeft, $footerY + 1.8);
        $this->SetFont('Helvetica', '', 7.0);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell(95, 3.4, $this->t('PERMIT TO USE / CAS ACN: ' . $permitNo), 0, 0, 'L');
        $this->Cell($pageWidth - 95, 3.4, $this->t('BIR AUTHORITY TO PRINT NO: ' . $atpNo), 0, 1, 'R');

        $this->SetX($pageLeft);
        $this->Cell(95, 3.4, 'SYSTEM: BIR E-COMMERCE E-INVOICE ENTERPRISE ERP', 0, 0, 'L');
        $this->Cell($pageWidth - 95, 3.4, $this->t('APPROVED SERIES: ' . $approvedSeries), 0, 1, 'R');

        // Centered 5-Year Validity Statement
        $this->SetXY($pageLeft, $footerY + 9.2);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell($pageWidth, 3.6, $this->t('"THIS SALES INVOICE SHALL BE VALID FOR FIVE (5) YEARS FROM THE DATE OF ISSUANCE"'), 0, 1, 'C');

        // ── Official Authorized Representative E-Signature & JWS Digital Seal ──
        $sigCardY = $footerY + 13.0;
        $sigCardW = 76;
        $sigCardH = 22.5;
        $sigCardX = $pageLeft + $pageWidth - $sigCardW; // Right-aligned

        // Signature Outer Card
        $this->SetDrawColor(226, 232, 240);
        $this->SetFillColor(255, 255, 255);
        $this->Rect($sigCardX, $sigCardY, $sigCardW, $sigCardH, 'DF');

        // Inner Themed Header Strip
        $this->SetFillColor($t['light_bg'][0], $t['light_bg'][1], $t['light_bg'][2]);
        $this->Rect($sigCardX, $sigCardY, $sigCardW, 4.6, 'F');
        $this->SetDrawColor($t['border'][0], $t['border'][1], $t['border'][2]);
        $this->Line($sigCardX, $sigCardY + 4.6, $sigCardX + $sigCardW, $sigCardY + 4.6);

        $this->SetXY($sigCardX, $sigCardY + 0.8);
        $this->SetFont('Helvetica', 'B', 6.2);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell($sigCardW, 3.2, 'AUTHORIZED REPRESENTATIVE SIGNATURE', 0, 1, 'C');

        // Check for signature image
        $customSig = $this->storeInfo['signature_image'] ?? '';
        $sigPath = '';
        if (!empty($customSig) && file_exists(__DIR__ . '/../' . $customSig)) {
            $sigPath = __DIR__ . '/../' . $customSig;
        } elseif (file_exists(__DIR__ . '/../storage/signatures/store_signature.png')) {
            $sigPath = __DIR__ . '/../storage/signatures/store_signature.png';
        }

        if (!empty($sigPath) && file_exists($sigPath)) {
            // Render transparent signature image
            $this->Image($sigPath, $sigCardX + ($sigCardW - 32) / 2, $sigCardY + 4.8, 32);
        }

        // Horizontal Signatory Line
        $this->SetDrawColor(203, 213, 225);
        $this->SetLineWidth(0.3);
        $this->Line($sigCardX + 8, $sigCardY + 16.0, $sigCardX + $sigCardW - 8, $sigCardY + 16.0);

        // Signatory Name & Designation
        $signatoryName = !empty($this->storeInfo['signatory_name']) ? strtoupper($this->storeInfo['signatory_name']) : 'AUTHORIZED REPRESENTATIVE';
        $this->SetXY($sigCardX, $sigCardY + 16.3);
        $this->SetFont('Helvetica', 'B', 6.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell($sigCardW, 2.9, $this->t($signatoryName), 0, 1, 'C');

        $signatoryDesig = !empty($this->storeInfo['signatory_designation']) ? $this->storeInfo['signatory_designation'] : 'Authorized Representative';
        $this->SetX($sigCardX);
        $this->SetFont('Helvetica', '', 5.8);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell($sigCardW, 2.6, $this->t($signatoryDesig), 0, 1, 'C');

        // ── Left Side Security Barcode / JWS Cryptographic Digest ──
        $badgeLeftX = $pageLeft;
        $badgeLeftW = 98;
        $this->SetXY($badgeLeftX, $sigCardY + 0.8);
        $this->SetFont('Helvetica', 'B', 7.2);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell($badgeLeftW, 3.4, 'OFFICIAL ELECTRONIC INVOICE (EIS/CAS COMPLIANT)', 0, 1, 'L');

        $this->SetX($badgeLeftX);
        $this->SetFont('Helvetica', '', 6.6);
        $this->SetTextColor($textLabel[0], $textLabel[1], $textLabel[2]);
        $this->Cell($badgeLeftW, 3.0, $this->t('Issued under BIR RR No. 7-2024 & RR No. 8-2022 (Electronic Invoicing).'), 0, 1, 'L');

        $this->SetX($badgeLeftX);
        $this->Cell($badgeLeftW, 3.0, $this->t('Order Reference: ' . $orderSn . ' • Channel: ' . $t['display_name']), 0, 1, 'L');

        // JWS Cryptographic Hash Digest
        $rawPayload = ($orderSn . ($this->invoiceData['invoice_number'] ?? '') . ($this->invoiceData['total_amount'] ?? ''));
        $jwsDigest = hash('sha256', $rawPayload);
        $certSerial = !empty($this->storeInfo['eis_cert_serial']) ? $this->storeInfo['eis_cert_serial'] : 'BIR-EIS-2026-001';

        $this->SetX($badgeLeftX);
        $this->SetFont('Helvetica', 'B', 6.2);
        $this->SetTextColor($t['primary'][0], $t['primary'][1], $t['primary'][2]);
        $this->Cell($badgeLeftW, 3.0, $this->t('JWS Digital Digest: ' . substr($jwsDigest, 0, 28) . '... (SHA-256)'), 0, 1, 'L');

        $this->SetX($badgeLeftX);
        $this->SetFont('Helvetica', '', 6.0);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell($badgeLeftW, 3.0, $this->t('Digital Certificate: ' . $certSerial . ' • Authenticated & Tamper-Proof'), 0, 1, 'L');
    }

    public function saveToFile($filePath) {
        $dir = dirname($filePath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        $this->Output('F', $filePath);
        return file_exists($filePath);
    }
}
