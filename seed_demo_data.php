<?php
/**
 * LocalShopOS Comprehensive Demo Data Seeder
 * Populates 10 diverse local shops with products, ads, coupons, and orders.
 * All merchant user passwords are set to 'admin123'.
 */

require_once __DIR__ . '/config/database.php';

$pdo = getDBConnection();
$passHash = password_hash('admin123', PASSWORD_DEFAULT);

// Ensure Super Admin User exists
$superAdmin = $pdo->query("SELECT id FROM admin_users WHERE role = 'super_admin'")->fetchColumn();
if (!$superAdmin) {
    $stmt = $pdo->prepare("INSERT INTO admin_users (tenant_id, role, email, password_hash, is_active) VALUES (NULL, 'super_admin', 'admin@localshopos.com', ?, 1)");
    $stmt->execute([$passHash]);
}

$shopsData = [
    [
        'shop_name' => 'Laxmi General Store',
        'subdomain' => 'laxmi-kirana',
        'email' => 'ramesh@kirana.com',
        'category' => 'Grocery & Staples',
        'whatsapp_number' => '919876543210',
        'logo_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 30.00,
        'min_delivery_order' => 200.00,
        'products' => [
            ['name' => 'Fortune Sunlite Refined Sunflower Oil 1L', 'price' => 145.00, 'stock' => 50, 'cat' => 'Oils & Ghee', 'img' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Aashirvaad Shuddh Chakki Atta 5kg', 'price' => 260.00, 'stock' => 40, 'cat' => 'Atta & Flours', 'img' => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Tata Salt Vacuum Evaporated 1kg', 'price' => 28.00, 'stock' => 100, 'cat' => 'Salt & Sugar', 'img' => 'https://images.unsplash.com/photo-1518110168401-f28404f0cf4b?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'India Gate Basmati Rice Feast Rozzana 1kg', 'price' => 110.00, 'stock' => 35, 'cat' => 'Rice & Grains', 'img' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Toor Dal Premium Cleaned 1kg', 'price' => 165.00, 'stock' => 30, 'cat' => 'Pulses & Dals', 'img' => 'https://images.unsplash.com/photo-1515543237350-b3eea1ec8082?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Amul Butter Pasteurized 500g', 'price' => 275.00, 'stock' => 25, 'cat' => 'Dairy Products', 'img' => 'https://images.unsplash.com/photo-1589985270826-4b7bb135bc9d?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Weekly Grocery Savings Deal', 'img' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Fresh Atta & Rice Special Discount', 'img' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Fresh Farm Organics',
        'subdomain' => 'fresh-fruits',
        'email' => 'contact@freshfarm.com',
        'category' => 'Organic Produce',
        'whatsapp_number' => '919812345678',
        'logo_url' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 25.00,
        'min_delivery_order' => 150.00,
        'products' => [
            ['name' => 'Fresh Shimla Red Apples (1kg)', 'price' => 180.00, 'stock' => 45, 'cat' => 'Fresh Fruits', 'img' => 'https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Organic Robusta Bananas (1 Dozen)', 'price' => 60.00, 'stock' => 60, 'cat' => 'Fresh Fruits', 'img' => 'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Farm Fresh Tomatoes (1kg)', 'price' => 40.00, 'stock' => 80, 'cat' => 'Fresh Vegetables', 'img' => 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Organic Spinach / Palak Bunch', 'price' => 25.00, 'stock' => 30, 'cat' => 'Leafy Greens', 'img' => 'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Sweet Alphonso Mangoes (1kg)', 'price' => 450.00, 'stock' => 20, 'cat' => 'Seasonal Fruits', 'img' => 'https://images.unsplash.com/photo-1553279768-865429fa0078?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => '100% Certified Organic Harvest', 'img' => 'https://images.unsplash.com/photo-1610832958506-aa56368176cf?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Fresh Green Leafy Veggies Special', 'img' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Gupta Bakery & Sweets',
        'subdomain' => 'gupta-bakery',
        'email' => 'info@guptabakery.com',
        'category' => 'Sweets & Snacks',
        'whatsapp_number' => '919876501234',
        'logo_url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 40.00,
        'min_delivery_order' => 250.00,
        'products' => [
            ['name' => 'Pure Desi Ghee Kaju Katli (500g)', 'price' => 520.00, 'stock' => 25, 'cat' => 'Traditional Sweets', 'img' => 'https://images.unsplash.com/photo-1599785209707-a456fc1337bb?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Fresh Belgian Chocolate Cake 1kg', 'price' => 650.00, 'stock' => 15, 'cat' => 'Cakes & Pastries', 'img' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Crispy Butter Khari Biscuit 250g', 'price' => 90.00, 'stock' => 40, 'cat' => 'Bakery Snacks', 'img' => 'https://images.unsplash.com/photo-1558961363-fa8fdf82db35?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Hot Gulab Jamun Box (12 Pcs)', 'price' => 240.00, 'stock' => 30, 'cat' => 'Traditional Sweets', 'img' => 'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Artisanal Garlic Bread Loaf', 'price' => 85.00, 'stock' => 20, 'cat' => 'Fresh Breads', 'img' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Freshly Baked Daily Delights', 'img' => 'https://images.unsplash.com/photo-1517433670267-08bbd4be890f?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Desi Ghee Sweets Festival Offer', 'img' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Apex Electronics & Mobile',
        'subdomain' => 'apex-electronics',
        'email' => 'sales@apexelectronics.com',
        'category' => 'Electronics & Mobile',
        'whatsapp_number' => '919988776655',
        'logo_url' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 0.00,
        'min_delivery_order' => 500.00,
        'products' => [
            ['name' => 'Wireless Bluetooth Earbuds TWS i12', 'price' => 899.00, 'stock' => 30, 'cat' => 'Audio Accessories', 'img' => 'https://images.unsplash.com/photo-1590658268037-6bf12165a8df?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Fast Charging Power Bank 20000mAh', 'price' => 1499.00, 'stock' => 20, 'cat' => 'Power & Charging', 'img' => 'https://images.unsplash.com/photo-1609592424074-88482436f54c?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Smart Fitness Band Watch Active HD', 'price' => 1999.00, 'stock' => 15, 'cat' => 'Wearables', 'img' => 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Heavy Duty Braided Type-C Cable 1.5m', 'price' => 299.00, 'stock' => 100, 'cat' => 'Cables & Adaptors', 'img' => 'https://images.unsplash.com/photo-1583863788434-e58a36330cf0?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Portable RGB Bluetooth Speaker 10W', 'price' => 1250.00, 'stock' => 25, 'cat' => 'Audio Accessories', 'img' => 'https://images.unsplash.com/photo-1545454675-3531b543be5d?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Upgrade Your Tech Accessories Today', 'img' => 'https://images.unsplash.com/photo-1498049860654-af1a5c57abf3?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Flat 20% Off on TWS Earbuds & Speakers', 'img' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Vogue Trends Apparel',
        'subdomain' => 'vogue-trends',
        'email' => 'support@voguetrends.in',
        'category' => 'Fashion & Clothing',
        'whatsapp_number' => '919765432109',
        'logo_url' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 50.00,
        'min_delivery_order' => 499.00,
        'products' => [
            ['name' => 'Pure Cotton Oversized Printed T-Shirt', 'price' => 499.00, 'stock' => 35, 'cat' => 'Men Casual Wear', 'img' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Floral Printed Anarkali Kurti Set', 'price' => 1299.00, 'stock' => 20, 'cat' => 'Women Ethnic Wear', 'img' => 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Classic Slim Fit Denim Jeans Blue', 'price' => 1499.00, 'stock' => 25, 'cat' => 'Bottom Wear', 'img' => 'https://images.unsplash.com/photo-1542272604-780c96856592?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Casual Linen Button-Down Shirt', 'price' => 899.00, 'stock' => 30, 'cat' => 'Men Casual Wear', 'img' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'New Festive Fashion Collection 2026', 'img' => 'https://images.unsplash.com/photo-1445205170230-053b83016050?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Buy 2 Get 1 Free on Cotton Tees', 'img' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Green Leaf Wellness Pharmacy',
        'subdomain' => 'greenleaf-meds',
        'email' => 'care@greenleafmeds.com',
        'category' => 'Healthcare & Pharmacy',
        'whatsapp_number' => '919833445566',
        'logo_url' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 20.00,
        'min_delivery_order' => 100.00,
        'products' => [
            ['name' => 'Chyawanprash Special Immune Booster 1kg', 'price' => 380.00, 'stock' => 40, 'cat' => 'Ayurveda & Health', 'img' => 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Multivitamin Daily Minerals Tablets 60s', 'price' => 450.00, 'stock' => 50, 'cat' => 'Supplements', 'img' => 'https://images.unsplash.com/photo-1550572017-edd951aa8f72?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Non-Contact Infrared Forehead Thermometer', 'price' => 850.00, 'stock' => 15, 'cat' => 'Medical Devices', 'img' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'N95 Respirator Masks Pack of 5', 'price' => 150.00, 'stock' => 100, 'cat' => 'First Aid & Masks', 'img' => 'https://images.unsplash.com/photo-1584634731339-252c581abfc5?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Essential Health & Immunity Essentials', 'img' => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Flat 15% Off Health Supplements', 'img' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Royal Footwear & Accessories',
        'subdomain' => 'royal-footwear',
        'email' => 'sales@royalfootwear.in',
        'category' => 'Footwear & Bags',
        'whatsapp_number' => '919811223344',
        'logo_url' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 45.00,
        'min_delivery_order' => 399.00,
        'products' => [
            ['name' => 'Men Leather Official Oxford Shoes', 'price' => 1899.00, 'stock' => 20, 'cat' => 'Men Footwear', 'img' => 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Breathable Lightweight Running Sneakers', 'price' => 1299.00, 'stock' => 35, 'cat' => 'Sports Shoes', 'img' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Women Elegant Block Heel Sandals', 'price' => 999.00, 'stock' => 25, 'cat' => 'Women Footwear', 'img' => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Genuine Leather Men Wallet Dark Brown', 'price' => 499.00, 'stock' => 40, 'cat' => 'Accessories', 'img' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Step Up In Style — Premium Footwear', 'img' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Flat ₹300 Cashback on Sneakers', 'img' => 'https://images.unsplash.com/photo-1552346154-21d32810aba3?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Spice Garden Organic Spices',
        'subdomain' => 'spice-garden',
        'email' => 'hello@spicegarden.com',
        'category' => 'Spices & Condiments',
        'whatsapp_number' => '919855667788',
        'logo_url' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 30.00,
        'min_delivery_order' => 250.00,
        'products' => [
            ['name' => 'Organic Whole Green Cardamom / Elaichi 100g', 'price' => 320.00, 'stock' => 30, 'cat' => 'Whole Spices', 'img' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Kashmiri Red Chilli Powder 250g', 'price' => 160.00, 'stock' => 50, 'cat' => 'Ground Spices', 'img' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Pure Organic Turmeric / Haldi Powder 500g', 'price' => 140.00, 'stock' => 45, 'cat' => 'Ground Spices', 'img' => 'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Assam Black Orthodox Loose Tea 500g', 'price' => 280.00, 'stock' => 35, 'cat' => 'Teas & Coffee', 'img' => 'https://images.unsplash.com/photo-1576092768241-dec231879fc3?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Pure Organic Spices Straight From Farms', 'img' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Special Combo Offer on Kitchen Spices', 'img' => 'https://images.unsplash.com/photo-1509358271058-acd02cc93898?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Urban Nest Home Decor',
        'subdomain' => 'urban-nest',
        'email' => 'sales@urbannest.in',
        'category' => 'Home & Living',
        'whatsapp_number' => '919866778899',
        'logo_url' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 75.00,
        'min_delivery_order' => 600.00,
        'products' => [
            ['name' => 'Handcrafted Ceramic Flower Vase Set', 'price' => 799.00, 'stock' => 20, 'cat' => 'Decor Artifacts', 'img' => 'https://images.unsplash.com/photo-1612196808214-b7e239e5f6b7?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Warm LED Bedside Table Lamp Nordic', 'price' => 1299.00, 'stock' => 15, 'cat' => 'Lighting', 'img' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500&auto=format&fit=crop&q=80'],
            ['name' => '100% Cotton King Size Bedsheet With Pillow Covers', 'price' => 999.00, 'stock' => 30, 'cat' => 'Bedding & Linens', 'img' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Aromatic Scented Candle Lavender 200g', 'price' => 349.00, 'stock' => 50, 'cat' => 'Home Fragrance', 'img' => 'https://images.unsplash.com/photo-1603006905003-be475563bc59?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Transform Your Living Space With Modern Decor', 'img' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Flat 25% Off on Nordic Lighting Range', 'img' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=1000&auto=format&fit=crop&q=80']
    ],
    [
        'shop_name' => 'Pet Joy Care Shop',
        'subdomain' => 'pet-joy-care',
        'email' => 'hello@petjoycare.com',
        'category' => 'Pet Supplies',
        'whatsapp_number' => '919877889900',
        'logo_url' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=200&auto=format&fit=crop&q=80',
        'delivery_fee' => 35.00,
        'min_delivery_order' => 300.00,
        'products' => [
            ['name' => 'Pedigree Adult Dog Food Chicken & Vegetables 3kg', 'price' => 750.00, 'stock' => 25, 'cat' => 'Dog Food', 'img' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Whiskas Ocean Fish Dry Cat Food 1.2kg', 'price' => 420.00, 'stock' => 30, 'cat' => 'Cat Food', 'img' => 'https://images.unsplash.com/photo-1568640347023-a616a30bc3bd?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Durable Rubber Dog Chew Toy Ball', 'price' => 199.00, 'stock' => 50, 'cat' => 'Pet Toys', 'img' => 'https://images.unsplash.com/photo-1535294435445-d7249524ef2e?w=500&auto=format&fit=crop&q=80'],
            ['name' => 'Padded Adjustable Dog Harness & Leash Set', 'price' => 599.00, 'stock' => 20, 'cat' => 'Pet Accessories', 'img' => 'https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=500&auto=format&fit=crop&q=80']
        ],
        'banner_ad' => ['title' => 'Everything Your Happy Pets Deserve', 'img' => 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=1000&auto=format&fit=crop&q=80'],
        'mid_ad' => ['title' => 'Flat 20% Off Premium Pet Foods', 'img' => 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?w=1000&auto=format&fit=crop&q=80']
    ]
];

echo "Starting seeding for 10 LocalShopOS demo shops..." . PHP_EOL;

foreach ($shopsData as $idx => $s) {
    // Check if tenant exists
    $tenantStmt = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = ?");
    $tenantStmt->execute([$s['subdomain']]);
    $tId = $tenantStmt->fetchColumn();

    if (!$tId) {
        $stmt = $pdo->prepare("
            INSERT INTO tenants (shop_name, subdomain, whatsapp_number, category, logo_url, product_limit, plan_status, delivery_enabled, delivery_fee, min_delivery_order, delivery_area_note, order_thank_you_msg, is_open) 
            VALUES (?, ?, ?, ?, ?, 50, 'active', 1, ?, ?, 'Standard local delivery within 5km radius.', 'Thank you for shopping with us! We are preparing your order.', 1)
        ");
        $stmt->execute([
            $s['shop_name'],
            $s['subdomain'],
            $s['whatsapp_number'],
            $s['category'],
            $s['logo_url'],
            $s['delivery_fee'],
            $s['min_delivery_order']
        ]);
        $tId = (int)$pdo->lastInsertId();
    } else {
        $tId = (int)$tId;
        $pdo->prepare("UPDATE tenants SET shop_name = ?, whatsapp_number = ?, category = ?, logo_url = ?, plan_status = 'active', is_open = 1 WHERE id = ?")
            ->execute([$s['shop_name'], $s['whatsapp_number'], $s['category'], $s['logo_url'], $tId]);
    }

    // Check if user exists
    $uStmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
    $uStmt->execute([$s['email']]);
    $uId = $uStmt->fetchColumn();

    if (!$uId) {
        $pdo->prepare("INSERT INTO admin_users (tenant_id, role, email, password_hash, is_active) VALUES (?, 'tenant_admin', ?, ?, 1)")
            ->execute([$tId, $s['email'], $passHash]);
    } else {
        $pdo->prepare("UPDATE admin_users SET tenant_id = ?, password_hash = ?, is_active = 1 WHERE id = ?")
            ->execute([$tId, $passHash, $uId]);
    }

    // Seed Products
    foreach ($s['products'] as $p) {
        $pCheck = $pdo->prepare("SELECT id FROM products WHERE tenant_id = ? AND name = ?");
        $pCheck->execute([$tId, $p['name']]);
        $existProdId = $pCheck->fetchColumn();

        if (!$existProdId) {
            $pdo->prepare("INSERT INTO products (tenant_id, name, price, stock_count, category, photo_url, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)")
                ->execute([$tId, $p['name'], $p['price'], $p['stock'], $p['cat'], $p['img']]);
        }
    }

    // Seed Tenant Banner Ad
    if (!empty($s['banner_ad'])) {
        $adCheck = $pdo->prepare("SELECT id FROM ads WHERE tenant_id = ? AND type = 'banner'");
        $adCheck->execute([$tId]);
        if (!$adCheck->fetchColumn()) {
            $pdo->prepare("INSERT INTO ads (tenant_id, title, type, image_url, is_active) VALUES (?, ?, 'banner', ?, 1)")
                ->execute([$tId, $s['banner_ad']['title'], $s['banner_ad']['img']]);
        }
    }

    // Seed Tenant Mid Page Ad
    if (!empty($s['mid_ad'])) {
        $adCheck = $pdo->prepare("SELECT id FROM ads WHERE tenant_id = ? AND type = 'mid_page'");
        $adCheck->execute([$tId]);
        if (!$adCheck->fetchColumn()) {
            $pdo->prepare("INSERT INTO ads (tenant_id, title, type, image_url, is_active) VALUES (?, ?, 'mid_page', ?, 1)")
                ->execute([$tId, $s['mid_ad']['title'], $s['mid_ad']['img']]);
        }
    }

    // Seed Coupons
    $cCheck = $pdo->prepare("SELECT id FROM coupons WHERE tenant_id = ? AND code = 'WELCOME10'");
    $cCheck->execute([$tId]);
    if (!$cCheck->fetchColumn()) {
        $pdo->prepare("INSERT INTO coupons (tenant_id, code, discount_type, discount_value, min_order_amount) VALUES (?, 'WELCOME10', 'percent', 10.00, 200.00)")
            ->execute([$tId]);
    }

    // Seed Orders
    $oCheck = $pdo->prepare("SELECT id FROM orders WHERE tenant_id = ?");
    $oCheck->execute([$tId]);
    if (!$oCheck->fetchColumn()) {
        // Fetch 1-2 product ids for tenant
        $prods = $pdo->query("SELECT id, name, price FROM products WHERE tenant_id = {$tId} LIMIT 2")->fetchAll();
        if (!empty($prods)) {
            $tot = 0;
            foreach ($prods as $pr) { $tot += $pr['price']; }
            $pdo->prepare("INSERT INTO orders (tenant_id, customer_contact, total, status, delivery_type, payment_mode) VALUES (?, '919876543210', ?, 'completed', 'delivery', 'cod')")
                ->execute([$tId, $tot]);
            $orderId = (int)$pdo->lastInsertId();
            foreach ($prods as $pr) {
                $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_order) VALUES (?, ?, 1, ?)")
                    ->execute([$orderId, $pr['id'], $pr['price']]);
            }
        }
    }

    echo "✔ Seeded Shop #" . ($idx + 1) . ": {$s['shop_name']} ({$s['subdomain']}) [User: {$s['email']}]" . PHP_EOL;
}

// Seed Global Platform Ads
$globalAdsData = [
    ['title' => 'Platform Super Sale 2026', 'placement' => 'banner', 'img' => 'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?w=1000&auto=format&fit=crop&q=80', 'link' => '/shops.php'],
    ['title' => 'LocalShopOS Instant Cashback Deal', 'placement' => 'mid_page', 'img' => 'https://images.unsplash.com/photo-1556742049-0a670f4a4591?w=1000&auto=format&fit=crop&q=80', 'link' => '/shops.php']
];

foreach ($globalAdsData as $g) {
    $gCheck = $pdo->prepare("SELECT id FROM global_ads WHERE placement = ? AND title = ?");
    $gCheck->execute([$g['placement'], $g['title']]);
    if (!$gCheck->fetchColumn()) {
        $pdo->prepare("INSERT INTO global_ads (title, placement, image_url, link_url, is_active) VALUES (?, ?, ?, ?, 1)")
            ->execute([$g['title'], $g['placement'], $g['img'], $g['link']]);
    }
}

echo "✔ Global platform ads seeded." . PHP_EOL;
echo "🎉 Comprehensive Demo Data Seeding Completed Successfully!" . PHP_EOL;
