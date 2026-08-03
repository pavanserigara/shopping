-- Local Shop OS - Database Schema Migration (v7 Upgrade)
-- Database: local_shop_os

CREATE DATABASE IF NOT EXISTS `local_shop_os` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `local_shop_os`;

-- Table 1: Plans
CREATE TABLE IF NOT EXISTS `plans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) DEFAULT 0.00,
  `billing_period` VARCHAR(50) DEFAULT 'monthly',
  `product_limit` INT DEFAULT 30,
  `is_default` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: Plan Features Mapping
CREATE TABLE IF NOT EXISTS `plan_features` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `plan_id` INT NOT NULL,
  `feature_key` VARCHAR(100) NOT NULL,
  INDEX (`plan_id`),
  INDEX (`feature_key`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: Tenants (v7: added last_login_at)
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `shop_name` VARCHAR(255) NOT NULL,
  `subdomain` VARCHAR(100) NOT NULL UNIQUE,
  `whatsapp_number` VARCHAR(20) NOT NULL,
  `category` VARCHAR(100) DEFAULT 'General Store',
  `logo_url` VARCHAR(550) NULL,
  `product_limit` INT DEFAULT 30,
  `plan_status` VARCHAR(20) DEFAULT 'trial', -- active, suspended, trial, trial_expired
  `trial_ends_at` DATETIME NULL,
  `plan_id` INT NULL,
  `is_open` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME NULL,              -- v7: tenant health tracking timestamp
  `delivery_enabled` TINYINT(1) DEFAULT 1,     -- v13: master toggle for store delivery
  `delivery_fee` DECIMAL(10,2) DEFAULT 0.00,   -- v13: flat delivery charge
  `min_delivery_order` DECIMAL(10,2) DEFAULT 0.00, -- v13: minimum cart total required for delivery
  `delivery_area_note` VARCHAR(255) NULL,      -- v13: e.g. "Delivering within 3km radius"
  `order_thank_you_msg` TEXT NULL,             -- v13: custom merchant thank-you message
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`subdomain`),
  INDEX (`plan_status`),
  INDEX (`plan_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: Platform Settings (Single-Row Config Table)
CREATE TABLE IF NOT EXISTS `platform_settings` (
  `id` INT PRIMARY KEY DEFAULT 1,
  `site_name` VARCHAR(255) DEFAULT 'LocalShopOS',
  `support_contact_number` VARCHAR(50) DEFAULT '+917676446647',
  `whatsapp_contact` VARCHAR(50) DEFAULT '917676446647',
  `site_logo_url` VARCHAR(550) DEFAULT '/assets/logo.png',
  `primary_color` VARCHAR(20) DEFAULT '#f5b400',
  `accent_color` VARCHAR(20) DEFAULT '#f5b400',
  `default_trial_days` INT DEFAULT 15,
  `default_product_limit` INT DEFAULT 30,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: Products
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `stock_count` INT DEFAULT 0,
  `photo_url` VARCHAR(550) NULL,
  `category` VARCHAR(100) NOT NULL DEFAULT 'General',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`tenant_id`),
  INDEX (`tenant_id`, `is_active`),
  INDEX (`tenant_id`, `category`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: Product Images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `image_url` VARCHAR(550) NOT NULL,
  `sort_order` INT DEFAULT 0,
  `is_primary` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`product_id`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: Tenant Ads
CREATE TABLE IF NOT EXISTS `ads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `title` VARCHAR(255) NULL,
  `type` ENUM('banner', 'mid_page') NOT NULL DEFAULT 'banner',
  `image_url` VARCHAR(550) NOT NULL,
  `link_url` VARCHAR(550) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`tenant_id`),
  INDEX (`tenant_id`, `type`, `is_active`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 8: Tenant Ad View Analytics
CREATE TABLE IF NOT EXISTS `ad_views` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ad_id` INT NOT NULL,
  `tenant_id` INT NOT NULL,
  `viewed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`ad_id`),
  INDEX (`tenant_id`),
  INDEX (`viewed_at`),
  FOREIGN KEY (`ad_id`) REFERENCES `ads`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 9: Global Platform-Wide Ads
CREATE TABLE IF NOT EXISTS `global_ads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NULL,
  `image_url` VARCHAR(550) NOT NULL,
  `link_url` VARCHAR(550) NULL,
  `placement` ENUM('banner', 'mid_page') NOT NULL DEFAULT 'banner',
  `is_active` TINYINT(1) DEFAULT 1,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`placement`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 10: Global Ad Views Analytics
CREATE TABLE IF NOT EXISTS `global_ad_views` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `global_ad_id` INT NOT NULL,
  `tenant_id` INT NULL,
  `viewed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`global_ad_id`),
  INDEX (`viewed_at`),
  FOREIGN KEY (`global_ad_id`) REFERENCES `global_ads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 11: Orders (v7: added coupon and status timestamps)
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `customer_contact` VARCHAR(100) NOT NULL,
  `total` DECIMAL(10,2) NOT NULL,
  `discount_amount` DECIMAL(10,2) DEFAULT 0.00,
  `coupon_code` VARCHAR(50) NULL,
  `status` VARCHAR(20) DEFAULT 'new', -- new, accepted, preparing, completed, cancelled
  `accepted_at` DATETIME NULL,
  `preparing_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `delivery_type` VARCHAR(20) DEFAULT 'delivery', -- v13: pickup vs delivery
  `delivery_address` TEXT NULL,                    -- v13: customer delivery address
  `delivery_contact` VARCHAR(50) NULL,             -- v13: contact phone for delivery
  `payment_mode` VARCHAR(50) DEFAULT 'cod',        -- v13: cod, upi, pickup_pay
  `delivery_fee` DECIMAL(10,2) DEFAULT 0.00,       -- v13: delivery fee charged
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`tenant_id`),
  INDEX (`tenant_id`, `status`),
  INDEX (`tenant_id`, `created_at`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 12: Order Items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT NOT NULL,
  `price_at_order` DECIMAL(10,2) NOT NULL,
  INDEX (`order_id`),
  INDEX (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 13: Admin Users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NULL,
  `role` VARCHAR(20) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`tenant_id`),
  INDEX (`email`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 14: Tenant Coupon / Discount Codes (v7 New Feature)
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `discount_type` ENUM('percent', 'flat') NOT NULL DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `min_order_amount` DECIMAL(10,2) DEFAULT 0.00,
  `expires_at` DATETIME NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE INDEX (`tenant_id`, `code`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 15: Super Admin Manual Payment Invoice Log (v7 New Feature)
CREATE TABLE IF NOT EXISTS `payment_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `plan_id` INT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_date` DATE NOT NULL,
  `notes` VARCHAR(550) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`tenant_id`),
  INDEX (`plan_id`),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plan_id`) REFERENCES `plans`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 16: Admin Action Log
CREATE TABLE IF NOT EXISTS `admin_action_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `actor_admin_id` INT NOT NULL,
  `action` VARCHAR(255) NOT NULL,
  `target_tenant_id` INT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`actor_admin_id`),
  INDEX (`target_tenant_id`),
  FOREIGN KEY (`actor_admin_id`) REFERENCES `admin_users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`target_tenant_id`) REFERENCES `tenants`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

