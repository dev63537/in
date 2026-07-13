-- ============================================================
-- upgrade_products.sql — Product Management Module Upgrade
-- Run this in phpMyAdmin AFTER importing schema.sql
-- Safe to run multiple times (uses IF NOT EXISTS / IGNORE)
-- Compatible with MySQL 5.7+ and MariaDB 10.x (InfinityFree)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

-- ── BRANDS TABLE ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `brands` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(110) NOT NULL UNIQUE,
  `logo` VARCHAR(255),
  `status` ENUM('active','inactive') DEFAULT 'active',
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── SUBCATEGORIES TABLE ───────────────────────────────────
CREATE TABLE IF NOT EXISTS `subcategories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(110) NOT NULL UNIQUE,
  `status` ENUM('active','inactive') DEFAULT 'active',
  `sort_order` INT DEFAULT 0,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── PRODUCT_IMAGES TABLE (gallery management) ─────────────
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `alt_text` VARCHAR(200),
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── EXTEND PRODUCTS TABLE ─────────────────────────────────
-- Add new columns safely using IF NOT EXISTS (MySQL 5.7+ / MariaDB 10.x compatible)

ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `sku`                VARCHAR(80)  UNIQUE AFTER `id`,
  ADD COLUMN IF NOT EXISTS `brand_id`           INT UNSIGNED AFTER `category_id`,
  ADD COLUMN IF NOT EXISTS `subcategory_id`     INT UNSIGNED AFTER `brand_id`,
  ADD COLUMN IF NOT EXISTS `gender`             ENUM('men','women','kids','unisex') DEFAULT 'unisex' AFTER `subcategory_id`,
  ADD COLUMN IF NOT EXISTS `material`           VARCHAR(200) AFTER `gender`,
  ADD COLUMN IF NOT EXISTS `short_description`  TEXT         AFTER `description`,
  ADD COLUMN IF NOT EXISTS `cost_price`         DECIMAL(10,2) DEFAULT 0 AFTER `sale_price`,
  ADD COLUMN IF NOT EXISTS `low_stock_alert`    INT DEFAULT 5 AFTER `stock`,
  ADD COLUMN IF NOT EXISTS `is_trending`        TINYINT(1) DEFAULT 0 AFTER `featured`,
  ADD COLUMN IF NOT EXISTS `is_new_arrival`     TINYINT(1) DEFAULT 0 AFTER `is_trending`,
  ADD COLUMN IF NOT EXISTS `is_best_seller`     TINYINT(1) DEFAULT 0 AFTER `is_new_arrival`,
  ADD COLUMN IF NOT EXISTS `meta_title`         VARCHAR(200) AFTER `tags`,
  ADD COLUMN IF NOT EXISTS `meta_description`   VARCHAR(320) AFTER `meta_title`,
  ADD COLUMN IF NOT EXISTS `updated_at`         TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- ── RENAME featured → is_featured safely ─────────────────
ALTER TABLE `products`
  ADD COLUMN IF NOT EXISTS `is_featured` TINYINT(1) DEFAULT 0 AFTER `featured`;

-- ── FOREIGN KEYS ─────────────────────────────────────────
-- NOTE: "ADD CONSTRAINT IF NOT EXISTS" is NOT supported in MySQL < 8.0
-- We drop first (ignore error if not exists) then re-add safely.

ALTER TABLE `products`
  DROP FOREIGN KEY IF EXISTS `fk_product_brand`;

ALTER TABLE `products`
  DROP FOREIGN KEY IF EXISTS `fk_product_subcat`;

ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`) ON DELETE SET NULL;

ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_subcat` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories`(`id`) ON DELETE SET NULL;

-- ── INDEXES ──────────────────────────────────────────────
CREATE INDEX IF NOT EXISTS `idx_products_status`    ON `products`(`status`);
CREATE INDEX IF NOT EXISTS `idx_products_category`  ON `products`(`category_id`);
CREATE INDEX IF NOT EXISTS `idx_products_brand`     ON `products`(`brand_id`);
CREATE INDEX IF NOT EXISTS `idx_products_gender`    ON `products`(`gender`);
CREATE INDEX IF NOT EXISTS `idx_products_price`     ON `products`(`price`);
CREATE INDEX IF NOT EXISTS `idx_products_featured`  ON `products`(`is_featured`);
CREATE INDEX IF NOT EXISTS `idx_products_trending`  ON `products`(`is_trending`);
CREATE INDEX IF NOT EXISTS `idx_products_new`       ON `products`(`is_new_arrival`);

-- ── DEMO BRANDS ──────────────────────────────────────────
INSERT IGNORE INTO `brands` (`name`,`slug`,`status`,`sort_order`) VALUES
('Zara','zara','active',1),
('H&M','h-and-m','active',2),
('Fabindia','fabindia','active',3),
('W for Woman','w-for-woman','active',4),
('Allen Solly','allen-solly','active',5),
('Levis','levis','active',6);

SET FOREIGN_KEY_CHECKS = 1;
