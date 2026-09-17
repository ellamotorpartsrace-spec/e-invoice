<?php
// classes/ShopeeInvoicePDF.php
// Multi-Platform E-Commerce BIR Annex A1 Compliant Sales Invoice Generator
// Compatible with Shopee, Lazada, and TikTok Shop
// Output: Vector-crisp, strictly BIR-compliant, visually stunning 1-page PDF

require_once __DIR__ . '/fpdf/fpdf.php';

class ShopeeInvoicePDF extends FPDF {

    protected $storeInfo = [];
    protected $invoiceData = [];

    public function __construct($storeInfo, $invoiceData) {
        parent::__construct('P', 'mm', 'A4'); // 210 x 297 mm
        $this->storeInfo = $storeInfo;
        $this->invoiceData = $invoiceData;
        $this->SetAutoPageBreak(false); // Strictly 1-page controlled layout
        $this->SetMargins(15, 10, 15);
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

        // ── Brand & Theme Color Palette ──
        $navyDark     = [15, 23, 42];    // Deep Slate 900 (Primary Headers & Accents)
        $slateMedium  = [51, 65, 85];    // Slate 700 (Body Text)
        $brandRed     = [225, 29, 72];   // Rose / Crimson Red
        $brandCrimson = [229, 35, 25];   // Vibrant Red Accent
        $brandGold    = [245, 158, 11];  // Amber / Gold (Emblem wings)
        $textPrimary  = [30, 41, 59];    // Slate 800
        $textMuted    = [100, 116, 139]; // Slate 500
        $borderColor  = [203, 213, 225]; // Slate 300
        $tableHeader  = [15, 23, 42];    // Deep Midnight Slate
        $lightBg      = [248, 250, 252]; // Soft Slate 50
        $zebraBg      = [250, 251, 253]; // Very subtle table row stripe

        // ══════════════════════════════════════════════════════════════
        // 0. TOP BRAND ACCENT RIBBON
        // ══════════════════════════════════════════════════════════════
        $this->SetFillColor($brandCrimson[0], $brandCrimson[1], $brandCrimson[2]);
        $this->Rect(15, 10, 60, 2.5, 'F');
        $this->SetFillColor(249, 115, 22); // Flame Orange
        $this->Rect(75, 10, 40, 2.5, 'F');
        $this->SetFillColor($brandGold[0], $brandGold[1], $brandGold[2]);
        $this->Rect(115, 10, 35, 2.5, 'F');
        $this->SetFillColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Rect(150, 10, 45, 2.5, 'F');

        // ══════════════════════════════════════════════════════════════
        // 1. SELLER BRAND IDENTITY & OFFICIAL INVOICE HEADER (Y = 14 to 34)
        // ══════════════════════════════════════════════════════════════
        $platform = !empty($this->invoiceData['platform_name']) ? $this->invoiceData['platform_name'] : 'Shopee';
        $platformUpper = strtoupper($platform);

        // Logo Image (Left)
        $logoPath = __DIR__ . '/../assets/img/logo-horizontal.png';
        if (file_exists($logoPath)) {
            // High-resolution horizontal logo: width 55mm, proportional height ~16.5mm
            $this->Image($logoPath, 15, 14.5, 55);
        } else {
            // Fallback Typography
            $this->SetXY(15, 15);
            $this->SetFont('Helvetica', 'B', 14);
            $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
            $this->Cell(60, 6, $this->t($storeName), 0, 1, 'L');
        }

        // Seller Registered Business Details (Beside Logo: X = 71 to 125)
        $sellerInfoX = 71;
        $this->SetXY($sellerInfoX, 14.5);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $storeName = !empty($this->storeInfo['store_name']) ? strtoupper($this->storeInfo['store_name']) : 'DEMO E-COMMERCE ENTERPRISES';
        $this->Cell(54, 3.8, $this->t(substr($storeName, 0, 32)), 0, 1, 'L');

        $this->SetX($sellerInfoX);
        $this->SetFont('Helvetica', 'B', 7.2);
        $this->SetTextColor($brandCrimson[0], $brandCrimson[1], $brandCrimson[2]);
        $storeTagline = !empty($this->storeInfo['store_tagline']) ? strtoupper($this->storeInfo['store_tagline']) : 'E-COMMERCE OFFICIAL RETAIL STORE';
        $this->Cell(54, 3.2, $this->t($storeTagline), 0, 1, 'L');

        $this->SetX($sellerInfoX);
        $this->SetFont('Helvetica', '', 6.8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $storeTin = !empty($this->storeInfo['store_tin']) ? $this->storeInfo['store_tin'] : (!empty($this->storeInfo['store_tax_id']) ? $this->storeInfo['store_tax_id'] : '123-456-789-00000');
        $vatStatus = !empty($this->storeInfo['vat_status']) ? $this->storeInfo['vat_status'] : 'VAT Registered';
        $this->Cell(54, 3.0, $this->t('VAT REG TIN: ' . $storeTin . ' (' . $vatStatus . ')'), 0, 1, 'L');

        // Dynamic address lines
        $fullStoreAddr = !empty($this->storeInfo['store_address']) ? $this->storeInfo['store_address'] : '123 Commercial Ave., Ortigas Center, Pasig City 1605';
        $addrParts = explode(',', $fullStoreAddr);
        $addrLine1 = trim($addrParts[0] ?? $fullStoreAddr);
        $addrLine2 = trim(implode(',', array_slice($addrParts, 1)));
        if (empty($addrLine2)) {
            $addrLine2 = 'Metro Manila, Philippines';
        }

        $this->SetX($sellerInfoX);
        $this->Cell(54, 3.0, $this->t(substr($addrLine1, 0, 38)), 0, 1, 'L');

        $this->SetX($sellerInfoX);
        $this->Cell(54, 3.0, $this->t(substr($addrLine2, 0, 38)), 0, 1, 'L');

        $this->SetX($sellerInfoX);
        $storeContact = $this->storeInfo['store_contact'] ?? '(02) 8123-4567 / 0917-000-0000';
        $this->Cell(54, 3.0, $this->t('Hotline: ' . $storeContact), 0, 1, 'L');

        // ── Right Side: Official Sales Invoice Title & Serial ──
        $this->SetXY(126, 13.5);
        $this->SetFont('Helvetica', 'B', 19);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(69, 6.5, 'SALES INVOICE', 0, 1, 'R');

        $this->SetXY(126, 20);
        $this->SetFont('Helvetica', 'B', 6.8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(69, 3.2, 'BIR ANNEX A1 & RR 7-2024 COMPLIANT', 0, 1, 'R');

        // Multi-Platform Tag Badge
        $this->SetXY(126, 23.5);
        $this->SetFont('Helvetica', 'B', 6.8);
        if ($platform === 'Lazada') {
            $this->SetTextColor(2, 132, 199);
            $this->Cell(69, 3.2, '[ LAZADA VERIFIED STORE ORDER ]', 0, 1, 'R');
        } elseif ($platform === 'TikTok' || $platform === 'TikTok Shop') {
            $this->SetTextColor(15, 23, 42);
            $this->Cell(69, 3.2, '[ TIKTOK SHOP OFFICIAL ORDER ]', 0, 1, 'R');
        } else {
            $this->SetTextColor(238, 77, 45); // Shopee Orange
            $this->Cell(69, 3.2, '[ SHOPEE VERIFIED STORE ORDER ]', 0, 1, 'R');
        }

        // Invoice Number Pill Container
        $invNo = $this->invoiceData['invoice_number'] ?? 'SI-SHP-2026-00001';
        $this->SetFillColor(254, 242, 242); // Rose 50
        $this->SetDrawColor($brandRed[0], $brandRed[1], $brandRed[2]);
        $this->SetLineWidth(0.35);
        $this->Rect(127, 27.5, 68, 6.8, 'DF');
        $this->SetXY(127, 28);
        $this->SetFont('Helvetica', 'B', 9.5);
        $this->SetTextColor($brandRed[0], $brandRed[1], $brandRed[2]);
        $this->Cell(68, 5.8, $this->t('Invoice No: ' . $invNo), 0, 1, 'C');

        // ══════════════════════════════════════════════════════════════
        // 2. TRANSACTION TYPE & DATE BAR (Y = 36 to 41)
        // ══════════════════════════════════════════════════════════════
        $transY = 36;
        $this->SetY($transY);
        $this->SetX(15);

        // Checkboxes
        $this->SetFont('ZapfDingbats', '', 8.5);
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->Cell(4, 4.5, chr(52), 1, 0, 'C'); // Checked
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(25, 4.5, ' CASH SALES', 0, 0, 'L');

        $this->SetFont('ZapfDingbats', '', 8.5);
        $this->Cell(4, 4.5, '', 1, 0, 'C'); // Unchecked
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(28, 4.5, ' CHARGE SALES', 0, 0, 'L');

        // Date Line (Right Aligned)
        $issueDate = $this->invoiceData['issue_date'] ?? date('Y-m-d');
        $this->SetX(125);
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
        $this->Cell(20, 4.5, 'Date Issued :', 0, 0, 'R');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(50, 4.5, date('F d, Y', strtotime($issueDate)), 'B', 1, 'L');

        // ══════════════════════════════════════════════════════════════
        // 3. BUYER INFORMATION BOX (Annex A1 Point 7 - "SOLD TO")
        // ══════════════════════════════════════════════════════════════
        $soldToBoxY = 42.5;
        $soldToBoxHeight = 24.5;

        // Box border & clean white background
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->SetFillColor(255, 255, 255);
        $this->SetLineWidth(0.3);
        $this->Rect(15, $soldToBoxY, 180, $soldToBoxHeight, 'DF');

        // Header Strip inside Box
        $this->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $this->Rect(15, $soldToBoxY, 180, 5.5, 'F');
        $this->Line(15, $soldToBoxY + 5.5, 195, $soldToBoxY + 5.5);

        $this->SetXY(18, $soldToBoxY + 1);
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(65, 3.8, 'SOLD TO (BUYER & DELIVERY DETAILS)', 0, 0, 'L');

        // Platform Order Reference
        $orderSn = $this->invoiceData['order_sn'] ?? '';
        $this->SetFont('Helvetica', 'B', 7.2);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        if ($platform === 'Lazada') {
            $orderRefLabel = 'LAZADA ORDER NO: ' . $orderSn;
        } elseif ($platform === 'TikTok' || $platform === 'TikTok Shop') {
            $orderRefLabel = 'TIKTOK SHOP ORDER ID: ' . $orderSn;
        } else {
            $orderRefLabel = 'SHOPEE ORDER SN: ' . $orderSn;
        }
        $this->Cell(109, 3.8, $this->t($orderRefLabel), 0, 1, 'R');

        // Customer Registered Name
        $this->SetXY(18, $soldToBoxY + 6.8);
        $this->SetFont('Helvetica', '', 7.8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(30, 4.2, 'Registered Name :', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $buyerName = !empty($this->invoiceData['buyer_name']) ? strtoupper($this->invoiceData['buyer_name']) : 'CASH CUSTOMER';
        $this->Cell(82, 4.2, $this->t($buyerName), 0, 0, 'L');

        // Customer TIN
        $this->SetFont('Helvetica', '', 7.8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(12, 4.2, 'TIN :', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $buyerTin = !empty($this->invoiceData['buyer_tin']) ? $this->invoiceData['buyer_tin'] : '000-000-000-00000';
        $this->Cell(50, 4.2, $this->t($buyerTin), 0, 1, 'L');

        // Customer Business / Delivery Address
        $this->SetXY(18, $soldToBoxY + 11.8);
        $this->SetFont('Helvetica', '', 7.8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(30, 3.8, 'Delivery Address :', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 7.8);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        $buyerAddr = !empty($this->invoiceData['buyer_address']) ? $this->invoiceData['buyer_address'] : 'N/A';
        $this->MultiCell(144, 3.8, $this->t($buyerAddr), 0, 'L');

        // ══════════════════════════════════════════════════════════════
        // 4. TRANSACTION DETAILS TABLE (Annex A1 Point 8)
        // ══════════════════════════════════════════════════════════════
        $tableY = $soldToBoxY + $soldToBoxHeight + 3;
        $this->SetY($tableY);

        $colNum   = 10;
        $colDesc  = 88;
        $colQty   = 20;
        $colPrice = 31;
        $colAmt   = 31;

        // Table Header: Deep Midnight Slate 900 Fill with Crisp White Text
        $this->SetFillColor($tableHeader[0], $tableHeader[1], $tableHeader[2]);
        $this->SetDrawColor($tableHeader[0], $tableHeader[1], $tableHeader[2]);
        $this->SetFont('Helvetica', 'B', 8);
        $this->SetTextColor(255, 255, 255);

        $this->Cell($colNum, 7, '#', 1, 0, 'C', true);
        $this->Cell($colDesc, 7, '  ITEM DESCRIPTION / PRODUCT DETAILS', 1, 0, 'L', true);
        $this->Cell($colQty, 7, 'QTY', 1, 0, 'C', true);
        $this->Cell($colPrice, 7, 'UNIT PRICE (PHP)', 1, 0, 'R', true);
        $this->Cell($colAmt, 7, 'AMOUNT (PHP)  ', 1, 1, 'R', true);

        // Table Rows
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->SetFont('Helvetica', '', 7.8);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        // Support both 'items' and 'items_json' keys seamlessly
        $items = $this->invoiceData['items'] ?? $this->invoiceData['items_json'] ?? [];
        if (is_string($items)) {
            $items = json_decode($items, true) ?: [];
        }

        $tableStartY = $this->GetY();
        $totalItemsCount = 0;
        $grossTableSum = 0;
        $itemIdx = 0;

        foreach ($items as $item) {
            $itemIdx++;
            $qty = (int)($item['quantity'] ?? 1);
            $totalItemsCount += $qty;

            $desc = trim($item['product_name'] ?? 'Item');
            $varName = !empty($item['variation_name']) ? trim($item['variation_name']) : '';

            $unitPrice = (float)($item['unit_price'] ?? 0);
            $subtotal  = (float)($item['subtotal'] ?? ($unitPrice * $qty));
            $grossTableSum += $subtotal;

            $fullDesc = $desc . ($varName ? ' [' . $varName . ']' : '');

            // Pre-calculate precise height for description
            $this->SetFont('Helvetica', '', 7.6);
            $descWidth = $this->GetStringWidth($this->t($fullDesc));
            $lines = max(1, ceil($descWidth / ($colDesc - 6)));
            $actualH = max(6.5, ($lines * 3.8) + 1.8);

            $currY = $this->GetY();
            
            // Subtle zebra striping
            if ($itemIdx % 2 === 0) {
                $this->SetFillColor($zebraBg[0], $zebraBg[1], $zebraBg[2]);
                $this->Rect(15, $currY, 180, $actualH, 'F');
            }

            // Index #
            $this->SetXY(15, $currY);
            $this->SetFont('Helvetica', 'B', 7.5);
            $this->Cell($colNum, $actualH, (string)$itemIdx, 'LR', 0, 'C');

            // Description (MultiCell with matching border)
            $this->SetXY(15 + $colNum, $currY + 0.9);
            $this->SetFont('Helvetica', '', 7.6);
            $this->MultiCell($colDesc, 3.8, ' ' . $this->t($fullDesc), 0, 'L');
            
            // Outer column border for description
            $this->Rect(15 + $colNum, $currY, $colDesc, $actualH);

            // Quantity
            $this->SetXY(15 + $colNum + $colDesc, $currY);
            $this->Cell($colQty, $actualH, (string)$qty, 'LR', 0, 'C');

            // Unit Price
            $this->SetXY(15 + $colNum + $colDesc + $colQty, $currY);
            $this->Cell($colPrice, $actualH, $this->money($unitPrice) . ' ', 'LR', 0, 'R');

            // Amount
            $this->SetXY(15 + $colNum + $colDesc + $colQty + $colPrice, $currY);
            $this->Cell($colAmt, $actualH, $this->money($subtotal) . ' ', 'LR', 1, 'R');

            $this->SetY($currY + $actualH);
        }

        // Shipping Fee Row
        $shippingFee = (float)($this->invoiceData['shipping_fee'] ?? 0);
        if ($shippingFee > 0) {
            $currY = $this->GetY();
            $this->SetXY(15, $currY);
            $this->Cell($colNum, 5.5, '', 'LR', 0, 'C');
            $this->Cell($colDesc, 5.5, ' Shipping & Logistics Handling Fee', 'LR', 0, 'L');
            $this->Cell($colQty, 5.5, '1', 'LR', 0, 'C');
            $this->Cell($colPrice, 5.5, $this->money($shippingFee) . ' ', 'LR', 0, 'R');
            $this->Cell($colAmt, 5.5, $this->money($shippingFee) . ' ', 'LR', 1, 'R');
            $grossTableSum += $shippingFee;
        }

        // Voucher / Discount Row
        $discountAmount = (float)($this->invoiceData['discount_amount'] ?? 0);
        if ($discountAmount > 0) {
            $currY = $this->GetY();
            $this->SetXY(15, $currY);
            $this->SetFont('Helvetica', 'I', 7.6);
            $this->SetTextColor($brandRed[0], $brandRed[1], $brandRed[2]);
            $this->Cell($colNum, 5.5, '', 'LR', 0, 'C');
            $this->Cell($colDesc, 5.5, ' Less: Promotional Discounts & Vouchers', 'LR', 0, 'L');
            $this->SetFont('Helvetica', '', 7.8);
            $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
            $this->Cell($colQty, 5.5, '1', 'LR', 0, 'C');
            $this->SetTextColor($brandRed[0], $brandRed[1], $brandRed[2]);
            $this->Cell($colPrice, 5.5, '-' . $this->money($discountAmount) . ' ', 'LR', 0, 'R');
            $this->Cell($colAmt, 5.5, '-' . $this->money($discountAmount) . ' ', 'LR', 1, 'R');
            $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);
        }

        // Table Filler Height for consistent aesthetic
        $currentTableHeight = $this->GetY() - $tableStartY;
        $minHeight = 44;
        if ($currentTableHeight < $minHeight) {
            $filler = $minHeight - $currentTableHeight;
            $this->Cell($colNum, $filler, '', 'LR', 0, 'C');
            $this->Cell($colDesc, $filler, '', 'LR', 0, 'L');
            $this->Cell($colQty, $filler, '', 'LR', 0, 'C');
            $this->Cell($colPrice, $filler, '', 'LR', 0, 'R');
            $this->Cell($colAmt, $filler, '', 'LR', 1, 'R');
        }

        // Table Bottom Border
        $this->Cell(180, 0, '', 'T', 1);
        $this->Ln(2);

        // ══════════════════════════════════════════════════════════════
        // 5. DUAL TAX BREAKDOWN BOXES (Annex A1 Points 9 & 10)
        // ══════════════════════════════════════════════════════════════
        $taxBoxY = $this->GetY();
        $grandTotal   = (float)($this->invoiceData['total_amount'] ?? $this->invoiceData['grand_total'] ?? 0);
        $vatableSales = (float)($this->invoiceData['vatable_sales'] ?? round($grandTotal / 1.12, 2));
        $vatAmount    = (float)($this->invoiceData['vat_amount'] ?? round($grandTotal - $vatableSales, 2));

        // Fallback for gross table total to ensure it never shows 0.00
        $totalSalesGross = $grossTableSum > 0 ? $grossTableSum : ($grandTotal + $discountAmount);

        // ── LEFT BOX: VAT Classification (Annex A1 Section 10) ──
        $leftW = 86;
        $boxH = 36;
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->Rect(15, $taxBoxY, $leftW, $boxH);

        // Header line inside left box
        $this->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $this->Rect(15, $taxBoxY, $leftW, 5.5, 'F');
        $this->Line(15, $taxBoxY + 5.5, 15 + $leftW, $taxBoxY + 5.5);

        $this->SetXY(17, $taxBoxY + 1);
        $this->SetFont('Helvetica', 'B', 7.2);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(80, 3.8, '12% VAT BREAKDOWN (ANNEX A1 SEC. 10)', 0, 1, 'L');

        $rowH = 4.8;
        $this->SetFont('Helvetica', '', 7.8);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        $this->SetXY(17, $taxBoxY + 6.5);
        $this->Cell(46, $rowH, ' VATable Sales', 0, 0, 'L');
        $this->Cell(35, $rowH, 'PHP ' . $this->money($vatableSales), 0, 1, 'R');

        $this->SetX(17);
        $this->SetFont('Helvetica', 'B', 7.8);
        $this->Cell(46, $rowH, ' VAT Amount (12%)', 0, 0, 'L');
        $this->Cell(35, $rowH, 'PHP ' . $this->money($vatAmount), 0, 1, 'R');

        $this->SetX(17);
        $this->SetFont('Helvetica', '', 7.8);
        $this->Cell(46, $rowH, ' Zero-Rated Sales', 0, 0, 'L');
        $this->Cell(35, $rowH, '0.00', 0, 1, 'R');

        $this->SetX(17);
        $this->Cell(46, $rowH, ' VAT-Exempt Sales', 0, 0, 'L');
        $this->Cell(35, $rowH, '0.00', 0, 1, 'R');

        // Divider inside Left Box
        $this->Line(15, $taxBoxY + 26, 15 + $leftW, $taxBoxY + 26);

        // "Received the amount of" bottom section
        $this->SetXY(17, $taxBoxY + 27);
        $this->SetFont('ZapfDingbats', '', 8);
        $this->Cell(4, 4, chr(52), 1, 0, 'C'); // Checked
        $this->SetFont('Helvetica', 'B', 7.5);
        $this->Cell(36, 4, ' Received the amount of :', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(41, 4, 'PHP ' . $this->money($grandTotal), 0, 1, 'R');

        $this->SetXY(17, $taxBoxY + 31.5);
        $this->SetFont('Helvetica', 'I', 6.5);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(80, 3.5, $this->t('Electronic Payment via ' . $platform . ' (Fully Settled)'), 0, 1, 'L');

        // ── RIGHT BOX: Settlement Summary (Annex A1 Section 9) ──
        $rightX = 104;
        $rightW = 91;
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->Rect($rightX, $taxBoxY, $rightW, $boxH);

        // Header line inside right box
        $this->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $this->Rect($rightX, $taxBoxY, $rightW, 5.5, 'F');
        $this->Line($rightX, $taxBoxY + 5.5, $rightX + $rightW, $taxBoxY + 5.5);

        $this->SetXY($rightX + 2, $taxBoxY + 1);
        $this->SetFont('Helvetica', 'B', 7.2);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Cell(85, 3.8, 'PAYMENT & TAX COMPUTATION (SEC. 9)', 0, 1, 'L');

        $this->SetFont('Helvetica', '', 7.5);
        $this->SetTextColor($textPrimary[0], $textPrimary[1], $textPrimary[2]);

        $rRowH = 3.6;
        $this->SetXY($rightX + 2, $taxBoxY + 6.2);
        $this->Cell(52, $rRowH, ' Total Sales (VAT Inclusive)', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($totalSalesGross), 0, 1, 'R');

        $this->SetX($rightX + 2);
        $this->Cell(52, $rRowH, ' Less: 12% VAT', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($vatAmount), 0, 1, 'R');

        $this->SetX($rightX + 2);
        $this->Cell(52, $rRowH, ' Amount : Net of VAT', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($vatableSales), 0, 1, 'R');

        $this->SetX($rightX + 2);
        $this->Cell(52, $rRowH, ' Less: Discount (SC/PWD/Voucher)', 0, 0, 'L');
        $this->Cell(34, $rRowH, ($discountAmount > 0 ? '-' . $this->money($discountAmount) : '0.00'), 0, 1, 'R');

        $this->SetX($rightX + 2);
        $this->Cell(52, $rRowH, ' Add: 12% VAT', 0, 0, 'L');
        $this->Cell(34, $rRowH, 'PHP ' . $this->money($vatAmount), 0, 1, 'R');

        $this->SetX($rightX + 2);
        $this->Cell(52, $rRowH, ' Less: Withholding Tax', 0, 0, 'L');
        $this->Cell(34, $rRowH, '0.00', 0, 1, 'R');

        // TOTAL AMOUNT DUE Dark Navy Highlight Bar
        $this->SetXY($rightX, $taxBoxY + 28);
        $this->SetFillColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $this->Rect($rightX, $taxBoxY + 28, $rightW, 8, 'F');

        $this->SetXY($rightX + 3, $taxBoxY + 29);
        $this->SetFont('Helvetica', 'B', 8.5);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(45, 6, 'TOTAL AMOUNT DUE', 0, 0, 'L');
        $this->SetFont('Helvetica', 'B', 10.5);
        $this->Cell(40, 6, 'PHP ' . $this->money($grandTotal), 0, 1, 'R');

        // ══════════════════════════════════════════════════════════════
        // 6. SC / PWD / SOLO PARENT BOX (Annex A1 Point 11)
        // ══════════════════════════════════════════════════════════════
        $pwdBoxY = $taxBoxY + $boxH + 2.5;
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->SetFillColor(255, 255, 255);
        $this->Rect(15, $pwdBoxY, 180, 9.5, 'DF');

        $this->SetXY(18, $pwdBoxY + 1.2);
        $this->SetFont('Helvetica', 'B', 7);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell(52, 3.5, 'SC/PWD/NAAC/MOV/Solo Parent ID No. :', 0, 0, 'L');
        $this->SetFont('Helvetica', '', 7);
        $this->Cell(55, 3.5, 'N/A (Standard E-Commerce Transaction)', 0, 0, 'L');

        $this->SetFont('Helvetica', 'B', 7);
        $this->Cell(18, 3.5, 'Signature :', 0, 0, 'L');
        $this->Line(148, $pwdBoxY + 4.5, 190, $pwdBoxY + 4.5);

        $this->SetXY(18, $pwdBoxY + 5);
        $this->SetFont('Helvetica', 'I', 6.2);
        $this->Cell(174, 3.5, 'Applicable for qualified Senior Citizen, PWD, or Solo Parent buyers claiming statutory discounts.', 0, 1, 'L');

        // ══════════════════════════════════════════════════════════════
        // 7. PERMIT TO USE, ATP FOOTER & OFFICIAL SECURITY SEAL
        // ══════════════════════════════════════════════════════════════
        $footerY = $pwdBoxY + 12;
        $this->SetDrawColor($borderColor[0], $borderColor[1], $borderColor[2]);
        $this->Line(15, $footerY, 195, $footerY);

        $this->SetXY(15, $footerY + 1.5);
        $this->SetFont('Helvetica', '', 6.5);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);

        $permitNo = $this->storeInfo['permit_no'] ?? 'POS-CAS-2026-001';
        $atpNo = $this->storeInfo['atp_no'] ?? '3AU00000605922';
        $approvedSeries = $this->storeInfo['approved_series'] ?? 'SI-SHP-2026-00001 - SI-SHP-2026-99999';

        // Left Column: Permit Details
        $this->Cell(95, 3.2, $this->t('PERMIT TO USE / CAS ACN: ' . $permitNo), 0, 0, 'L');
        // Right Column: ATP No
        $this->Cell(85, 3.2, $this->t('BIR AUTHORITY TO PRINT NO: ' . $atpNo), 0, 1, 'R');

        $this->SetX(15);
        $this->Cell(95, 3.2, 'SYSTEM: BIR E-COMMERCE E-INVOICE ENTERPRISE ERP', 0, 0, 'L');
        $this->Cell(85, 3.2, $this->t('APPROVED SERIES: ' . $approvedSeries), 0, 1, 'R');

        $this->SetX(15);
        $this->SetFont('Helvetica', 'B', 6.5);
        $this->Cell(180, 3.5, $this->t('"THIS SALES INVOICE SHALL BE VALID FOR FIVE (5) YEARS FROM THE DATE OF ISSUANCE"'), 0, 1, 'C');

        // ── Official Security Seal (Bottom Right) ──
        $stampX = 126;
        $stampY = $footerY + 11.5;
        $stampW = 69;
        $stampH = 15;

        $this->SetDrawColor(226, 232, 240);
        $this->SetFillColor(250, 250, 250);
        $this->Rect($stampX, $stampY, $stampW, $stampH, 'DF');

        // Inner seal border
        $this->SetDrawColor($brandRed[0], $brandRed[1], $brandRed[2]);
        $this->SetLineWidth(0.25);
        $this->Rect($stampX + 1, $stampY + 1, $stampW - 2, $stampH - 2);

        // Logo Mark inside Seal
        $markPath = __DIR__ . '/../assets/img/logo-mark.png';
        $stampTextX = $stampX + 3;
        if (file_exists($markPath)) {
            $this->Image($markPath, $stampX + 2.5, $stampY + 2.5, 10);
            $stampTextX = $stampX + 14;
        }

        $this->SetXY($stampTextX, $stampY + 2.2);
        $this->SetFont('Helvetica', 'B', 6.8);
        $this->SetTextColor($navyDark[0], $navyDark[1], $navyDark[2]);
        $sealTitle = !empty($this->storeInfo['store_name']) ? strtoupper($this->storeInfo['store_name']) : 'OFFICIAL VERIFIED MERCHANT';
        $this->Cell($stampW - ($stampTextX - $stampX) - 2, 3.2, $this->t(substr($sealTitle, 0, 28)), 0, 1, 'L');

        $this->SetX($stampTextX);
        $this->SetFont('Helvetica', 'B', 6.2);
        $this->SetTextColor($brandCrimson[0], $brandCrimson[1], $brandCrimson[2]);
        $this->Cell($stampW - ($stampTextX - $stampX) - 2, 3, 'OFFICIAL E-INVOICE * VALID & ISSUED', 0, 1, 'L');

        $this->SetX($stampTextX);
        $this->SetFont('Helvetica', '', 5.8);
        $this->SetTextColor($textMuted[0], $textMuted[1], $textMuted[2]);
        $this->Cell($stampW - ($stampTextX - $stampX) - 2, 2.8, 'Authorized Digital Signature * BIR Compliant', 0, 1, 'L');
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
