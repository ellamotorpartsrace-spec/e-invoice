<?php
// classes/ShopeeExcelReader.php
// Wrapper for MultiPlatformExcelReader for backward compatibility

require_once __DIR__ . '/MultiPlatformExcelReader.php';

class ShopeeExcelReader {

    /**
     * Parse a Shopee, Lazada, or TikTok order export XLSX file.
     * 
     * @param string $filePath Full path to the .xlsx file
     * @param string $platformHint Optional platform hint ('Shopee', 'Lazada', 'TikTok Shop')
     * @return array ['success' => bool, 'platform' => string, 'orders' => array, 'total_rows' => int, 'error' => string]
     */
    public static function parse($filePath, $platformHint = 'Shopee') {
        return MultiPlatformExcelReader::parse($filePath, $platformHint);
    }
}
