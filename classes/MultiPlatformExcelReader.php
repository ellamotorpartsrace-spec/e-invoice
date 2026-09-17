<?php
// classes/MultiPlatformExcelReader.php
// Unified E-Commerce Spreadsheet Parser for Shopee, Lazada, and TikTok Shop
// Fully compliant with BIR Annex A1 and RR 7-2024 E-Commerce regulations

class MultiPlatformExcelReader {

    /**
     * Parse an order export spreadsheet (.xlsx) from Shopee, Lazada, or TikTok Shop.
     *
     * @param string $filePath Full path to the uploaded .xlsx file
     * @param string $platformHint Optional hint: 'Shopee', 'Lazada', 'TikTok Shop'
     * @return array ['success' => bool, 'platform' => string, 'orders' => array, 'total_rows' => int, 'error' => string]
     */
    public static function parse($filePath, $platformHint = '') {
        if (!file_exists($filePath)) {
            return ['success' => false, 'error' => 'File does not exist: ' . $filePath];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return ['success' => false, 'error' => 'Unable to open XLSX file as a zip archive.'];
        }

        try {
            // 1. Read shared strings
            $sharedStrings = [];
            if (($sIndex = $zip->locateName('xl/sharedStrings.xml')) !== false) {
                $sXml = simplexml_load_string($zip->getFromIndex($sIndex));
                if ($sXml && isset($sXml->si)) {
                    foreach ($sXml->si as $si) {
                        $text = (string)($si->t ?? '');
                        if (isset($si->r)) {
                            $text = '';
                            foreach ($si->r as $r) {
                                $text .= (string)($r->t ?? '');
                            }
                        }
                        $sharedStrings[] = $text;
                    }
                }
            }

            // 2. Locate worksheet XML
            $sheetXmlContent = null;
            $wbIndex = $zip->locateName('xl/workbook.xml');
            $relsIndex = $zip->locateName('xl/_rels/workbook.xml.rels');
            if ($wbIndex !== false && $relsIndex !== false) {
                $wbXml = simplexml_load_string($zip->getFromIndex($wbIndex));
                $relsXml = simplexml_load_string($zip->getFromIndex($relsIndex));
                $relMap = [];
                if ($relsXml) {
                    foreach ($relsXml->Relationship as $rel) {
                        $relMap[(string)$rel['Id']] = (string)$rel['Target'];
                    }
                }
                if ($wbXml && isset($wbXml->sheets->sheet[0])) {
                    $firstRId = (string)$wbXml->sheets->sheet[0]->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                    if (isset($relMap[$firstRId])) {
                        $targetPath = 'xl/' . ltrim($relMap[$firstRId], '/xl/');
                        if ($zip->locateName($targetPath) !== false) {
                            $sheetXmlContent = $zip->getFromName($targetPath);
                        }
                    }
                }
            }

            if (!$sheetXmlContent) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $name)) {
                        $sheetXmlContent = $zip->getFromIndex($i);
                        break;
                    }
                }
            }

            if (!$sheetXmlContent) {
                $zip->close();
                return ['success' => false, 'error' => 'Worksheet not found inside the XLSX file.'];
            }

            $xml = simplexml_load_string($sheetXmlContent);
            $zip->close();

            if (!$xml || !isset($xml->sheetData->row)) {
                return ['success' => false, 'error' => 'No rows found in the worksheet.'];
            }

            // 3. Group cells by explicit row index (Essential for TikTok chunked row exports)
            $rows = [];
            foreach ($xml->sheetData->row as $row) {
                $rNum = (int)$row['r'];
                if (!isset($rows[$rNum])) {
                    $rows[$rNum] = [];
                }
                foreach ($row->c as $c) {
                    $ref = (string)$c['r'];
                    $colLetter = preg_replace('/[0-9]/', '', $ref);
                    $val = '';
                    if (isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    } elseif (isset($c->v)) {
                        $rawV = (string)$c->v;
                        $tAttr = (string)($c['t'] ?? '');
                        if ($tAttr === 's') {
                            $val = $sharedStrings[(int)$rawV] ?? $rawV;
                        } else {
                            $val = $rawV;
                        }
                    }
                    $rows[$rNum][$colLetter] = trim($val);
                }
            }
            ksort($rows);

            if (empty($rows)) {
                return ['success' => false, 'error' => 'Spreadsheet contains no data rows.'];
            }

            // 4. Map Column Headers from Row 1
            $headerRow = reset($rows);
            $headerMap = [];
            foreach ($headerRow as $col => $headerText) {
                $clean = strtolower(trim($headerText));
                $headerMap[$clean] = $col;
            }

            // 5. Detect Platform (Lazada, TikTok Shop, or Shopee)
            $detectedPlatform = self::detectPlatform($headerMap, $platformHint);

            // 6. Parse Orders by Platform
            if ($detectedPlatform === 'Lazada') {
                $result = self::parseLazada($rows, $headerMap);
            } elseif ($detectedPlatform === 'TikTok Shop') {
                $result = self::parseTikTok($rows, $headerMap);
            } else {
                $result = self::parseShopee($rows, $headerMap);
            }

            $result['platform'] = $detectedPlatform;
            $result['total_rows'] = count($rows) - 1;
            return $result;

        } catch (Exception $e) {
            if (isset($zip)) @$zip->close();
            return ['success' => false, 'error' => 'Error parsing Excel: ' . $e->getMessage()];
        }
    }

    /**
     * Auto-detect the e-commerce platform from the header signatures
     */
    private static function detectPlatform($headerMap, $platformHint = '') {
        // Lazada signature keys
        if (isset($headerMap['orderitemid']) || isset($headerMap['lazadaid']) || isset($headerMap['lazadasku'])) {
            return 'Lazada';
        }

        // TikTok signature keys
        if (isset($headerMap['sku subtotal before discount']) || 
            isset($headerMap['tax info - buyer tax id']) || 
            isset($headerMap['normal or pre-order']) ||
            isset($headerMap['sku platform discount'])) {
            return 'TikTok Shop';
        }

        // Shopee signature keys
        if (isset($headerMap['buyer paid shipping fee']) || 
            isset($headerMap['order creation date']) || 
            isset($headerMap['deal price']) ||
            isset($headerMap['invoice request type'])) {
            return 'Shopee';
        }

        // Use hint if provided
        if (!empty($platformHint)) {
            if (stripos($platformHint, 'laz') !== false) return 'Lazada';
            if (stripos($platformHint, 'tik') !== false) return 'TikTok Shop';
            if (stripos($platformHint, 'shop') !== false) return 'Shopee';
        }

        return 'Shopee';
    }

    /**
     * Helper to retrieve column values using multiple keyword candidates or fallback column letter
     */
    private static function getVal($row, $headerMap, $keywords, $fallbackCol) {
        if (!is_array($keywords)) $keywords = [$keywords];
        foreach ($keywords as $kw) {
            $kw = strtolower($kw);
            if (isset($headerMap[$kw]) && isset($row[$headerMap[$kw]])) {
                return $row[$headerMap[$kw]];
            }
        }
        return $row[$fallbackCol] ?? '';
    }

    /**
     * Parse Shopee Orders
     */
    private static function parseShopee($rows, $headerMap) {
        $orders = [];
        $first = true;

        foreach ($rows as $rNum => $row) {
            if ($first) { $first = false; continue; }

            $orderId = trim(self::getVal($row, $headerMap, ['order id', 'order number', 'order no', 'ordernumber', 'order_sn'], 'A'));
            if (empty($orderId)) continue;

            $orderStatus = trim(self::getVal($row, $headerMap, ['order status', 'status', 'order status(main)', 'order_status'], 'B'));
            $orderDate   = trim(self::getVal($row, $headerMap, ['order creation date', 'create time', 'created at', 'created time', 'order date'], 'K'));
            $paidDate    = trim(self::getVal($row, $headerMap, ['order paid time', 'paid time', 'pay time'], 'L'));
            $productName = trim(self::getVal($row, $headerMap, ['product name', 'item name', 'itemname', 'product title', 'item title'], 'N'));
            $variation   = trim(self::getVal($row, $headerMap, ['variation name', 'sku', 'seller sku', 'variation', 'item variation'], 'P'));
            $origPrice   = (float)str_replace(',', '', self::getVal($row, $headerMap, ['original price', 'unit price', 'sku unit original price'], 'Q'));
            $dealPrice   = (float)str_replace(',', '', self::getVal($row, $headerMap, ['deal price', 'unit price', 'paid price', 'item price'], 'R'));
            if ($dealPrice <= 0) $dealPrice = $origPrice;
            $qty         = (int)self::getVal($row, $headerMap, ['quantity', 'qty', 'item quantity'], 'S');
            if ($qty <= 0) $qty = 1;
            $subtotal    = (float)str_replace(',', '', self::getVal($row, $headerMap, ['product subtotal', 'subtotal', 'item subtotal', 'sku subtotal after discount'], 'U'));
            if ($subtotal <= 0) $subtotal = $dealPrice * $qty;

            $buyerShipping = (float)str_replace(',', '', self::getVal($row, $headerMap, ['buyer paid shipping fee', 'shipping fee', 'shipping fee paid by buyer', 'shipping amount'], 'AK'));
            $grandTotal    = (float)str_replace(',', '', self::getVal($row, $headerMap, ['grand total', 'total amount', 'order amount', 'total paid by buyer', 'order grand total', 'paid price'], 'AO'));

            $buyerUsername = trim(self::getVal($row, $headerMap, ['username (buyer)', 'buyer username', 'customer username', 'customer name', 'buyer name'], 'AQ'));
            $receiverName  = trim(self::getVal($row, $headerMap, ['receiver name', 'recipient', 'recipient name', 'shipping name', 'consignee'], 'AR'));
            $deliveryAddr  = trim(self::getVal($row, $headerMap, ['delivery address', 'shipping address', 'shipping address detail', 'detailed address', 'address line 1'], 'AT'));

            $invoiceReqType = trim(self::getVal($row, $headerMap, ['invoice request type'], 'BD'));
            $invoiceType    = trim(self::getVal($row, $headerMap, ['invoice type'], 'BE'));
            $invoiceName    = trim(self::getVal($row, $headerMap, ['name', 'billing name', 'tax buyer name', 'company name'], 'BF'));
            $invoiceTin     = trim(self::getVal($row, $headerMap, ['business number', 'tax id', 'tin', 'buyer tin', 'tax number'], 'BG'));
            $invoiceAddr    = trim(self::getVal($row, $headerMap, ['address', 'billing address', 'tax address'], 'BH'));

            // Full buyer name
            $finalBuyerName = !empty($invoiceName) ? $invoiceName : (!empty($receiverName) && strpos($receiverName, '*') === false ? $receiverName : (!empty($buyerUsername) ? $buyerUsername : 'Online Buyer'));
            $finalTin = self::cleanTin($invoiceTin);
            $finalAddress = !empty($invoiceAddr) && trim($invoiceAddr) !== '' ? $invoiceAddr : $deliveryAddr;

            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'order_sn'             => $orderId,
                    'platform_name'        => 'Shopee',
                    'order_status'         => $orderStatus ?: 'Completed',
                    'order_date'           => !empty($orderDate) ? date('Y-m-d H:i:s', strtotime($orderDate)) : date('Y-m-d H:i:s'),
                    'paid_date'            => !empty($paidDate) ? date('Y-m-d H:i:s', strtotime($paidDate)) : null,
                    'buyer_username'       => $buyerUsername,
                    'buyer_name'           => $finalBuyerName,
                    'buyer_tin'            => $finalTin,
                    'buyer_address'        => $finalAddress,
                    'invoice_request_type' => !empty($invoiceReqType) ? $invoiceReqType : 'Non Requested Invoice',
                    'invoice_type'         => !empty($invoiceType) ? $invoiceType : 'Personal',
                    'shipping_fee'         => $buyerShipping,
                    'grand_total'          => $grandTotal,
                    'gross_items_total'    => 0,
                    'discount_amount'      => 0,
                    'items'                => []
                ];
            }

            $orders[$orderId]['items'][] = [
                'product_name'   => $productName,
                'variation_name' => $variation,
                'unit_price'     => $dealPrice,
                'quantity'       => $qty,
                'subtotal'       => $subtotal
            ];
            $orders[$orderId]['gross_items_total'] += $subtotal;
        }

        // Calculate discount and VAT
        foreach ($orders as &$ord) {
            if ($ord['grand_total'] <= 0) {
                $ord['grand_total'] = $ord['gross_items_total'] + $ord['shipping_fee'];
            }
            $expectedTotal = $ord['gross_items_total'] + $ord['shipping_fee'];
            $discount = round($expectedTotal - $ord['grand_total'], 2);
            $ord['discount_amount'] = max(0, $discount);

            $totalAmt = (float)$ord['grand_total'];
            $vatableSales = round($totalAmt / 1.12, 2);
            $vatAmount = round($totalAmt - $vatableSales, 2);

            $ord['vatable_sales']    = $vatableSales;
            $ord['vat_amount']       = $vatAmount;
            $ord['vat_exempt_sales'] = 0.00;
            $ord['zero_rated_sales'] = 0.00;
        }
        unset($ord);

        return ['success' => true, 'orders' => array_values($orders)];
    }

    /**
     * Parse Lazada Orders
     * Multi-item orders share the same orderNumber (Col M), with individual orderItemId per row
     */
    private static function parseLazada($rows, $headerMap) {
        $orders = [];
        $first = true;

        foreach ($rows as $rNum => $row) {
            if ($first) { $first = false; continue; }

            // Lazada Order Number is in Col M ('ordernumber')
            $orderId = trim(self::getVal($row, $headerMap, ['ordernumber', 'order number', 'order id'], 'M'));
            if (empty($orderId)) continue;

            $status        = trim(self::getVal($row, $headerMap, ['status'], 'BO'));
            $createTime    = trim(self::getVal($row, $headerMap, ['createtime', 'create time'], 'I'));
            $deliveredDate = trim(self::getVal($row, $headerMap, ['delivereddate', 'delivered date'], 'P'));
            
            $customerName  = trim(self::getVal($row, $headerMap, ['customername', 'customer name'], 'Q'));
            $billingName   = trim(self::getVal($row, $headerMap, ['billingname', 'billing name'], 'AF'));
            $shippingName  = trim(self::getVal($row, $headerMap, ['shippingname', 'shipping name'], 'T'));
            $buyerName     = !empty($billingName) ? $billingName : (!empty($customerName) ? $customerName : (!empty($shippingName) ? $shippingName : 'Lazada Buyer'));

            // Addresses
            $billAddr1   = trim(self::getVal($row, $headerMap, ['billingaddr', 'billing address'], 'AG'));
            $billFullReg = trim(self::getVal($row, $headerMap, ['billingaddr5'], 'AK'));
            $billCity    = trim(self::getVal($row, $headerMap, ['billingcity', 'billing city'], 'AN'));

            $shipAddr1   = trim(self::getVal($row, $headerMap, ['shippingaddress', 'shipping address'], 'U'));
            $shipProv    = trim(self::getVal($row, $headerMap, ['shippingaddress3'], 'W'));
            $shipCity    = trim(self::getVal($row, $headerMap, ['shippingcity', 'shippingaddress4'], 'X'));
            $shipBrgy    = trim(self::getVal($row, $headerMap, ['shippingaddress5'], 'Y'));
            $shipPost    = trim(self::getVal($row, $headerMap, ['shippingpostcode'], 'AC'));

            if (!empty($billAddr1) && !empty($billFullReg)) {
                $fullAddress = $billAddr1 . ', ' . $billFullReg;
            } elseif (!empty($billAddr1)) {
                $fullAddress = $billAddr1 . (!empty($billCity) ? ', ' . $billCity : '');
            } elseif (!empty($shipAddr1)) {
                $parts = array_filter([$shipAddr1, $shipBrgy, $shipCity, $shipProv, $shipPost]);
                $fullAddress = implode(', ', $parts);
            } else {
                $fullAddress = 'Philippines';
            }

            // Tax Info
            $taxCode   = trim(self::getVal($row, $headerMap, ['taxcode', 'tax code', 'tin'], 'AQ'));
            $branchNo  = trim(self::getVal($row, $headerMap, ['branchnumber', 'branch number'], 'AR'));
            $taxReq    = trim(self::getVal($row, $headerMap, ['taxinvoicerequested', 'tax invoice requested'], 'AS'));

            $rawTin = $taxCode . ($branchNo ? '-' . $branchNo : '');
            $finalTin = self::cleanTin($rawTin);

            // Item Details
            $itemName     = trim(self::getVal($row, $headerMap, ['itemname', 'item name'], 'BA'));
            $variation    = trim(self::getVal($row, $headerMap, ['variation'], 'BB'));
            $unitPrice    = (float)str_replace(',', '', self::getVal($row, $headerMap, ['unitprice', 'unit price'], 'AV'));
            $paidPrice    = (float)str_replace(',', '', self::getVal($row, $headerMap, ['paidprice', 'paid price'], 'AU'));
            $shippingFee  = (float)str_replace(',', '', self::getVal($row, $headerMap, ['shippingfee', 'shipping fee'], 'AY'));
            $sellerDisc   = abs((float)str_replace(',', '', self::getVal($row, $headerMap, ['sellerdiscounttotal', 'seller discount total'], 'AW')));
            $platformDisc = abs((float)str_replace(',', '', self::getVal($row, $headerMap, ['platformdiscounttotal', 'platform discount total'], 'AX')));

            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'order_sn'             => $orderId,
                    'platform_name'        => 'Lazada',
                    'order_status'         => $status ?: 'Completed',
                    'order_date'           => !empty($createTime) ? date('Y-m-d H:i:s', strtotime($createTime)) : date('Y-m-d H:i:s'),
                    'paid_date'            => !empty($deliveredDate) ? date('Y-m-d H:i:s', strtotime($deliveredDate)) : null,
                    'buyer_username'       => $customerName,
                    'buyer_name'           => $buyerName,
                    'buyer_tin'            => $finalTin,
                    'buyer_address'        => $fullAddress,
                    'invoice_request_type' => (strtolower($taxReq) === 'true' || strtolower($taxReq) === 'yes') ? 'Requested Invoice' : 'Non Requested Invoice',
                    'invoice_type'         => (!empty($taxCode) && $taxCode !== 'NA') ? 'Business' : 'Personal',
                    'shipping_fee'         => 0,
                    'grand_total'          => 0,
                    'gross_items_total'    => 0,
                    'discount_amount'      => 0,
                    'items'                => []
                ];
            }

            // In Lazada exports, each row is 1 unit of that item
            $orders[$orderId]['items'][] = [
                'product_name'   => $itemName,
                'variation_name' => $variation,
                'unit_price'     => $unitPrice > 0 ? $unitPrice : $paidPrice,
                'quantity'       => 1,
                'subtotal'       => $unitPrice > 0 ? $unitPrice : $paidPrice
            ];

            $orders[$orderId]['gross_items_total'] += ($unitPrice > 0 ? $unitPrice : $paidPrice);
            $orders[$orderId]['shipping_fee']      += $shippingFee;
            $orders[$orderId]['grand_total']       += $paidPrice;
            $orders[$orderId]['discount_amount']   += ($sellerDisc + $platformDisc);
        }

        // Finalize order calculations
        foreach ($orders as &$ord) {
            if ($ord['grand_total'] <= 0) {
                $ord['grand_total'] = max(0, $ord['gross_items_total'] + $ord['shipping_fee'] - $ord['discount_amount']);
            }
            $expected = $ord['gross_items_total'] + $ord['shipping_fee'];
            if ($expected > $ord['grand_total']) {
                $ord['discount_amount'] = round($expected - $ord['grand_total'], 2);
            }

            $totalAmt = (float)$ord['grand_total'];
            $vatableSales = round($totalAmt / 1.12, 2);
            $vatAmount = round($totalAmt - $vatableSales, 2);

            $ord['vatable_sales']    = $vatableSales;
            $ord['vat_amount']       = $vatAmount;
            $ord['vat_exempt_sales'] = 0.00;
            $ord['zero_rated_sales'] = 0.00;
        }
        unset($ord);

        return ['success' => true, 'orders' => array_values($orders)];
    }

    /**
     * Parse TikTok Shop Orders
     * Handles chunked row cells and skips TikTok description headers on Row 2
     */
    private static function parseTikTok($rows, $headerMap) {
        $orders = [];
        $first = true;

        foreach ($rows as $rNum => $row) {
            if ($first) { $first = false; continue; }

            // Skip TikTok column description row (Row 2 in standard export)
            $colA = trim($row['A'] ?? '');
            if (stripos($colA, 'platform unique') !== false || stripos($colA, 'order id') !== false) {
                continue;
            }

            $orderId = trim(self::getVal($row, $headerMap, ['order id', 'order number'], 'A'));
            if (empty($orderId)) continue;

            $status     = trim(self::getVal($row, $headerMap, ['order status', 'status'], 'B'));
            $createTime = trim(self::getVal($row, $headerMap, ['created time', 'create time'], 'Y'));
            $paidTime   = trim(self::getVal($row, $headerMap, ['paid time', 'pay time'], 'Z'));

            // Buyer / Recipient
            $buyerUsername = trim(self::getVal($row, $headerMap, ['buyer username', 'username'], 'AM'));
            $recipient     = trim(self::getVal($row, $headerMap, ['recipient', 'customer name', 'receiver name'], 'AN'));
            
            // Tax Invoice Fields from TikTok
            $reqTaxInv    = trim(self::getVal($row, $headerMap, ['request tax invoice'], 'BF'));
            $taxType      = trim(self::getVal($row, $headerMap, ['tax info - type'], 'BG'));
            $taxBuyerTin  = trim(self::getVal($row, $headerMap, ['tax info - buyer tax id', 'buyer tax id'], 'BH'));
            $taxBuyerName = trim(self::getVal($row, $headerMap, ['tax info - full name of buyer', 'full name of buyer'], 'BI'));
            $taxBuyerAddr = trim(self::getVal($row, $headerMap, ['tax info - registered address', 'registered address'], 'BM'));

            $finalBuyerName = !empty($taxBuyerName) ? $taxBuyerName : (!empty($recipient) && strpos($recipient, '*') === false ? $recipient : (!empty($buyerUsername) ? $buyerUsername : 'TikTok Buyer'));
            $finalTin = self::cleanTin($taxBuyerTin);

            // Delivery address
            $detailAddr = trim(self::getVal($row, $headerMap, ['detail address', 'address'], 'AU'));
            $brgy       = trim(self::getVal($row, $headerMap, ['barangay'], 'AT'));
            $muni       = trim(self::getVal($row, $headerMap, ['municipality', 'city'], 'AS'));
            $prov       = trim(self::getVal($row, $headerMap, ['province', 'state'], 'AR'));
            $region     = trim(self::getVal($row, $headerMap, ['region'], 'AQ'));

            $combinedAddr = array_filter([$detailAddr, $brgy, $muni, $prov, $region]);
            $deliveryAddr = implode(', ', $combinedAddr);
            $finalAddress = !empty($taxBuyerAddr) ? $taxBuyerAddr : (!empty($deliveryAddr) ? $deliveryAddr : 'Philippines');

            // Item
            $productName = trim(self::getVal($row, $headerMap, ['product name', 'item name'], 'H'));
            $variation   = trim(self::getVal($row, $headerMap, ['variation', 'sku'], 'I'));
            $qty         = (int)self::getVal($row, $headerMap, ['quantity', 'qty'], 'J');
            if ($qty <= 0) $qty = 1;
            $unitOrigPrice  = (float)str_replace(',', '', self::getVal($row, $headerMap, ['sku unit original price', 'unit price'], 'L'));
            $subtotalBefore = (float)str_replace(',', '', self::getVal($row, $headerMap, ['sku subtotal before discount'], 'M'));
            if ($subtotalBefore <= 0) $subtotalBefore = $unitOrigPrice * $qty;

            // Shipping & Grand Total
            $shippingAfterDisc = (float)str_replace(',', '', self::getVal($row, $headerMap, ['shipping fee after discount'], 'Q'));
            $orderAmount       = (float)str_replace(',', '', self::getVal($row, $headerMap, ['order amount', 'total paid by buyer'], 'W'));

            if (!isset($orders[$orderId])) {
                $orders[$orderId] = [
                    'order_sn'             => $orderId,
                    'platform_name'        => 'TikTok Shop',
                    'order_status'         => $status ?: 'Completed',
                    'order_date'           => !empty($createTime) ? date('Y-m-d H:i:s', strtotime($createTime)) : date('Y-m-d H:i:s'),
                    'paid_date'            => !empty($paidTime) ? date('Y-m-d H:i:s', strtotime($paidTime)) : null,
                    'buyer_username'       => $buyerUsername,
                    'buyer_name'           => $finalBuyerName,
                    'buyer_tin'            => $finalTin,
                    'buyer_address'        => $finalAddress,
                    'invoice_request_type' => (!empty($reqTaxInv) && strtolower($reqTaxInv) !== 'no') ? 'Requested Invoice' : 'Non Requested Invoice',
                    'invoice_type'         => !empty($taxType) ? $taxType : 'Personal',
                    'shipping_fee'         => $shippingAfterDisc,
                    'grand_total'          => $orderAmount,
                    'gross_items_total'    => 0,
                    'discount_amount'      => 0,
                    'items'                => []
                ];
            }

            $orders[$orderId]['items'][] = [
                'product_name'   => $productName,
                'variation_name' => $variation,
                'unit_price'     => $unitOrigPrice,
                'quantity'       => $qty,
                'subtotal'       => $subtotalBefore
            ];
            $orders[$orderId]['gross_items_total'] += $subtotalBefore;
        }

        // Compute TikTok discounts and VAT
        foreach ($orders as &$ord) {
            if ($ord['grand_total'] <= 0) {
                $ord['grand_total'] = $ord['gross_items_total'] + $ord['shipping_fee'];
            }
            $expected = $ord['gross_items_total'] + $ord['shipping_fee'];
            $discount = round($expected - $ord['grand_total'], 2);
            $ord['discount_amount'] = max(0, $discount);

            $totalAmt = (float)$ord['grand_total'];
            $vatableSales = round($totalAmt / 1.12, 2);
            $vatAmount = round($totalAmt - $vatableSales, 2);

            $ord['vatable_sales']    = $vatableSales;
            $ord['vat_amount']       = $vatAmount;
            $ord['vat_exempt_sales'] = 0.00;
            $ord['zero_rated_sales'] = 0.00;
        }
        unset($ord);

        return ['success' => true, 'orders' => array_values($orders)];
    }

    /**
     * Clean and format BIR Tax Identification Number (TIN)
     * Annex A1 format: 000-000-000-00000
     */
    public static function cleanTin($tin) {
        $cleanTin = preg_replace('/[^0-9]/', '', (string)$tin);
        if (empty($cleanTin) || strlen($cleanTin) < 9) {
            return !empty($tin) && trim($tin) !== '' && strtoupper(trim($tin)) !== 'NA' ? trim($tin) : '000-000-000-00000';
        }
        if (strlen($cleanTin) === 9) {
            return substr($cleanTin, 0, 3) . '-' . substr($cleanTin, 3, 3) . '-' . substr($cleanTin, 6, 3) . '-00000';
        } elseif (strlen($cleanTin) >= 12) {
            return substr($cleanTin, 0, 3) . '-' . substr($cleanTin, 3, 3) . '-' . substr($cleanTin, 6, 3) . '-' . substr($cleanTin, 9);
        }
        return $tin;
    }
}
