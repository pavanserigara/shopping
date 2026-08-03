<?php
// LocalShopOS v7 Seeder
require_once __DIR__ . '/config/database.php';

try {
    $pdo = getDBConnection();
    echo "Connecting to MySQL server...\n";

    // Re-create tables from schema.sql cleanly
    $rawSql = file_get_contents(__DIR__ . '/schema.sql');
    $queries = array_filter(array_map('trim', explode(';', $rawSql)));
    foreach ($queries as $q) {
        if (!empty($q)) {
            try { $pdo->exec($q); } catch (Exception $e) {}
        }
    }
    echo "Database schema verified.\n";

    // Safe ALTER for v7 columns on existing databases
    $colsToEnsure = [
        "ALTER TABLE `tenants` ADD COLUMN `last_login_at` DATETIME NULL",
        "ALTER TABLE `orders` ADD COLUMN `discount_amount` DECIMAL(10,2) DEFAULT 0.00",
        "ALTER TABLE `orders` ADD COLUMN `coupon_code` VARCHAR(50) NULL",
        "ALTER TABLE `orders` ADD COLUMN `accepted_at` DATETIME NULL",
        "ALTER TABLE `orders` ADD COLUMN `preparing_at` DATETIME NULL",
        "ALTER TABLE `orders` ADD COLUMN `completed_at` DATETIME NULL",
        "ALTER TABLE `tenants` ADD COLUMN `delivery_enabled` TINYINT(1) DEFAULT 1",
        "ALTER TABLE `tenants` ADD COLUMN `delivery_fee` DECIMAL(10,2) DEFAULT 0.00",
        "ALTER TABLE `tenants` ADD COLUMN `min_delivery_order` DECIMAL(10,2) DEFAULT 0.00",
        "ALTER TABLE `tenants` ADD COLUMN `delivery_area_note` VARCHAR(255) NULL",
        "ALTER TABLE `tenants` ADD COLUMN `order_thank_you_msg` TEXT NULL",
        "ALTER TABLE `orders` ADD COLUMN `delivery_type` VARCHAR(20) DEFAULT 'delivery'",
        "ALTER TABLE `orders` ADD COLUMN `delivery_address` TEXT NULL",
        "ALTER TABLE `orders` ADD COLUMN `delivery_contact` VARCHAR(50) NULL",
        "ALTER TABLE `orders` ADD COLUMN `payment_mode` VARCHAR(50) DEFAULT 'cod'",
        "ALTER TABLE `orders` ADD COLUMN `delivery_fee` DECIMAL(10,2) DEFAULT 0.00"
    ];
    foreach ($colsToEnsure as $altSql) {
        try {
            $pdo->exec($altSql);
        } catch (Exception $e) {
            // Column already exists, ignore
        }
    }

    // 1. Seed Subscription Plans (v6/v7)
    $plansCount = (int)$pdo->query("SELECT COUNT(*) FROM plans")->fetchColumn();
    if ($plansCount === 0) {
        $pdo->exec("
            INSERT INTO plans (id, name, price, billing_period, product_limit, is_default) VALUES
            (1, 'Free Starter', 0.00, 'monthly', 30, 1),
            (2, 'Pro Merchant', 499.00, 'monthly', 100, 0),
            (3, 'Gold VIP Store', 999.00, 'monthly', 500, 0);
        ");

        $pdo->exec("
            INSERT INTO plan_features (plan_id, feature_key) VALUES
            (1, 'product_management'),
            (1, 'order_management'),
            
            (2, 'product_management'),
            (2, 'product_image_gallery'),
            (2, 'order_management'),
            (2, 'sales_reports'),
            (2, 'shop_ads'),
            
            (3, 'product_management'),
            (3, 'product_image_gallery'),
            (3, 'order_management'),
            (3, 'sales_reports'),
            (3, 'shop_ads'),
            (3, 'ad_analytics'),
            (3, 'shop_logo_upload');
        ");
        echo "Default subscription plans seeded.\n";
    }

    // Insert Default Platform Settings (Single Row Config)
    $pdo->exec("
        INSERT INTO platform_settings (id, site_name, support_contact_number, whatsapp_contact, site_logo_url, primary_color, accent_color, default_trial_days, default_product_limit)
        VALUES (1, 'LocalShopOS', '+917676446647', '917676446647', '/assets/logo.png', '#f5b400', '#f5b400', 15, 30)
        ON DUPLICATE KEY UPDATE site_name = VALUES(site_name);
    ");


    // 2. Seed Admin Users
    $adminCount = (int)$pdo->query("SELECT COUNT(*) FROM admin_users WHERE role = 'super_admin'")->fetchColumn();
    if ($adminCount === 0) {
        $superPass = password_hash('adminpassword123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (role, email, password_hash) VALUES ('super_admin', 'admin@localshopos.com', ?)");
        $stmt->execute([$superPass]);
        echo "Super admin seeded: admin@localshopos.com / adminpassword123\n";
    }

    // 3. Seed Demo Tenants
    $tenantCount = (int)$pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
    if ($tenantCount === 0) {
        $pdo->exec("
            INSERT INTO tenants (id, shop_name, subdomain, whatsapp_number, category, product_limit, plan_status, trial_ends_at, plan_id, is_open, last_login_at) VALUES
            (1, 'Laxmi General Store', 'laxmi-kirana', '9876543210', 'Kirana & Grocery', 100, 'active', DATE_ADD(NOW(), INTERVAL 14 DAY), 2, 1, NOW()),
            (2, 'Fresh Fruits & Veggies', 'fresh-fruits', '9876543211', 'Fruits & Vegetables', 30, 'trial', DATE_ADD(NOW(), INTERVAL 7 DAY), 1, 1, NOW()),
            (3, 'Gupta Bakery & Sweets', 'gupta-bakery', '9876543212', 'Snacks & Bakery', 500, 'active', NULL, 3, 1, NOW());
        ");
        echo "Demo tenants seeded.\n";

        // Seed products for Tenant 1
        $pdo->exec("
            INSERT INTO products (tenant_id, name, price, stock_count, category, photo_url, is_active) VALUES
            (1, 'Fortune Chakki Fresh Atta 5kg', 245.00, 25, 'Groceries', 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=400', 1),
            (1, 'Amul Taaza Toned Milk 1L', 54.00, 40, 'Dairy & Milk', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400', 1),
            (1, 'Tata Salt Vacuum Evaporated 1kg', 28.00, 15, 'Groceries', 'https://images.unsplash.com/photo-1518110168401-f2844efde3fc?w=400', 1),
            (1, 'Surf Excel Easy Wash Powder 1kg', 140.00, 2, 'Household Essentials', 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?w=400', 1),
            (1, 'Maggi 2-Minute Masala Noodles 280g', 48.00, 0, 'Snacks & Bakery', 'https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400', 1);
        ");

        // Seed products for Tenant 2
        $pdo->exec("
            INSERT INTO products (tenant_id, name, price, stock_count, category, photo_url, is_active) VALUES
            (2, 'Shimla Red Apples 1kg', 180.00, 12, 'Fruits & Vegetables', 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=400', 1),
            (2, 'Organic Robusta Bananas 1 Dozen', 60.00, 18, 'Fruits & Vegetables', 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=400', 1),
            (2, 'Fresh Tomatoes 1kg', 35.00, 3, 'Fruits & Vegetables', 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=400', 1);
        ");

        // Seed Coupons
        $pdo->exec("
            INSERT INTO coupons (tenant_id, code, discount_type, discount_value, min_order_amount, is_active) VALUES
            (1, 'WELCOME10', 'percent', 10.00, 200.00, 1),
            (1, 'SAVE50', 'flat', 50.00, 500.00, 1);
        ");

        // Seed Payment Log for Super Admin Revenue
        $pdo->exec("
            INSERT INTO payment_log (tenant_id, plan_id, amount, payment_date, notes) VALUES
            (1, 2, 499.00, CURDATE(), 'Monthly subscription renewal via GPay'),
            (3, 3, 999.00, CURDATE(), 'Annual subscription upfront payment');
        ");

        echo "Products, coupons, and payment logs seeded.\n";
    }

    echo "v7 Seeding Complete Successfully!\n";

} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
