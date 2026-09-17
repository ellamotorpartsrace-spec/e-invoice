-- database/schema.sql
-- Multi-Platform E-Commerce BIR E-Invoice Management System
-- Standard BIR Annex A1 & RR 7-2024 Compliance System

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Users Table (for portal authentication)
CREATE TABLE IF NOT EXISTS `einv_users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL DEFAULT 'Administrator',
    `email` VARCHAR(100) NULL,
    `role` VARCHAR(20) NOT NULL DEFAULT 'admin',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Admin User: admin / admin123
INSERT INTO `einv_users` (`id`, `username`, `password_hash`, `full_name`, `email`, `role`)
VALUES (1, 'admin', '$2y$10$c9F/1G.XpkUMm9ABfy2P4ePCKYiun.yAl8nSQCGRBm9iQFZDAFufW', 'Demo Administrator', 'admin@demostore.com', 'admin')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`), `password_hash` = VALUES(`password_hash`);

-- 2. System Settings Table
CREATE TABLE IF NOT EXISTS `einv_settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default BIR & Store Details (Customizable in Portal Settings)
INSERT INTO `einv_settings` (`setting_key`, `setting_value`) VALUES
('store_name', 'DEMO E-COMMERCE ENTERPRISES'),
('store_tin', '123-456-789-00000'),
('store_address', '123 Commercial Ave., Ortigas Center, Pasig City, Metro Manila 1605'),
('store_contact', '(02) 8123-4567 / 0917-000-0000'),
('vat_status', 'VAT Registered'),
('invoice_prefix', 'SI-SHP-2026-'),
('invoice_prefix_shopee', 'SI-SHP-2026-'),
('invoice_prefix_lazada', 'SI-LAZ-2026-'),
('invoice_prefix_tiktok', 'SI-TT-2026-'),
('permit_no', 'CAS-POS-2026-00001'),
('atp_no', '3AU00000000000'),
('approved_series', 'SI-SHP-2026-00001 - SI-SHP-2026-99999'),
('eis_enabled', '0'),
('eis_env', 'sandbox'),
('eis_client_id', ''),
('eis_client_secret', ''),
('eis_api_key', ''),
('eis_cert_serial', '')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- 3. Invoices Table (Sequential BIR Sales Invoices)
CREATE TABLE IF NOT EXISTS `einv_invoices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `order_sn` VARCHAR(50) NOT NULL,
    `platform_name` VARCHAR(50) DEFAULT 'Shopee',
    `buyer_name` VARCHAR(255) NOT NULL,
    `buyer_tin` VARCHAR(50) DEFAULT '000-000-000-00000',
    `buyer_address` TEXT,
    `invoice_type` VARCHAR(50) DEFAULT 'Personal',
    `order_date` DATETIME NULL,
    `issue_date` DATE NOT NULL,
    `gross_items_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `vatable_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `vat_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `vat_exempt_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `zero_rated_sales` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `items_json` LONGTEXT NOT NULL,
    `pdf_filename` VARCHAR(255) NULL,
    `status` ENUM('generated', 'cancelled') DEFAULT 'generated',
    `eis_status` ENUM('pending', 'transmitted', 'failed', 'exempt') DEFAULT 'pending',
    `eis_ack_code` VARCHAR(100) NULL,
    `eis_transmitted_at` DATETIME NULL,
    `eis_hash` VARCHAR(255) NULL,
    `created_by` VARCHAR(100) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_sn` (`order_sn`),
    INDEX `idx_invoice_number` (`invoice_number`),
    INDEX `idx_issue_date` (`issue_date`),
    INDEX `idx_eis_status` (`eis_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
