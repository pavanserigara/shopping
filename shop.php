<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$subdomain = trim($_GET['subdomain'] ?? '');

if (empty($subdomain)) {
    header("Location: /shops.php");
    exit;
}

$pdo = getDBConnection();

// Fetch Tenant Details by Subdomain
$tenantStmt = $pdo->prepare("SELECT * FROM tenants WHERE subdomain = ?");
$tenantStmt->execute([$subdomain]);
$tenant = $tenantStmt->fetch();

if (!$tenant) {
    http_response_code(404);
    die("<div style='font-family:sans-serif; text-align:center; padding:50px;'><h1>404 Shop Not Found</h1><p>The store subdomain <strong>" . htmlspecialchars($subdomain) . "</strong> does not exist on LocalShopOS.</p><a href='/shops.php'>&larr; Browse All Shops</a></div>");
}

$tenantId       = (int)$tenant['id'];
$shopName       = $tenant['shop_name'];
$whatsappNumber = $tenant['whatsapp_number'];
$category       = $tenant['category'];
$logoUrl        = $tenant['logo_url'];
$isOpen         = (int)$tenant['is_open'];
$planStatus     = $tenant['plan_status'];
$trialEndsAt    = $tenant['trial_ends_at'];

// Tenant Delivery & Fulfillment Settings
$deliveryEnabled  = (int)($tenant['delivery_enabled'] ?? 1);
$deliveryFee      = (float)($tenant['delivery_fee'] ?? 0.00);
$minDeliveryOrder = (float)($tenant['min_delivery_order'] ?? 0.00);
$deliveryAreaNote = $tenant['delivery_area_note'] ?? '';
$thankYouMsg      = $tenant['order_thank_you_msg'] ?? '';

// Trial & Plan Expiry Logic
$isTrialExpired = false;
if ($planStatus === 'trial_expired' || ($planStatus === 'trial' && !empty($trialEndsAt) && strtotime($trialEndsAt) < time())) {
    $isTrialExpired = true;
}

$isStoreActive = ($planStatus === 'active' || ($planStatus === 'trial' && !$isTrialExpired)) && ($planStatus !== 'suspended');

// Fetch Global Platform Settings for Footer
$siteName = 'LocalShopOS';
try {
    $val = $pdo->query("SELECT site_name FROM platform_settings WHERE id = 1")->fetchColumn();
    if ($val) $siteName = $val;
} catch (Exception $e) {}

// Fetch Tenant Banner Ads (Top Carousel)
$bannerAdsStmt = $pdo->prepare("
    SELECT id, title, image_url, link_url, 0 as is_global FROM ads 
    WHERE tenant_id = ? AND type = 'banner' AND is_active = 1 
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date IS NULL OR end_date >= CURDATE())
    ORDER BY id DESC
");
$bannerAdsStmt->execute([$tenantId]);
$tenantBannerAds = $bannerAdsStmt->fetchAll();

// Fetch Platform Global Banner Ads
$globalBannersStmt = $pdo->query("
    SELECT id, title, image_url, link_url, 1 as is_global FROM global_ads 
    WHERE placement = 'banner' AND is_active = 1 
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date IS NULL OR end_date >= CURDATE())
    ORDER BY id DESC
");
$globalBannerAds = $globalBannersStmt->fetchAll();

// Combined Banners Array
$allBannerAds = array_merge($globalBannerAds, $tenantBannerAds);

// Fetch Tenant Mid-Page Ads
$midAdsStmt = $pdo->prepare("
    SELECT id, title, image_url, link_url, 0 as is_global FROM ads 
    WHERE tenant_id = ? AND type = 'mid_page' AND is_active = 1 
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date IS NULL OR end_date >= CURDATE())
    ORDER BY id DESC
");
$midAdsStmt->execute([$tenantId]);
$tenantMidAds = $midAdsStmt->fetchAll();

// Fetch Platform Global Mid-Page Ads
$globalMidStmt = $pdo->query("
    SELECT id, title, image_url, link_url, 1 as is_global FROM global_ads 
    WHERE placement = 'mid_page' AND is_active = 1 
      AND (start_date IS NULL OR start_date <= CURDATE())
      AND (end_date IS NULL OR end_date >= CURDATE())
    ORDER BY id DESC
");
$globalMidAds = $globalMidStmt->fetchAll();

$allMidAds = array_merge($globalMidAds, $tenantMidAds);

// Fetch Active Products for Tenant
$productsStmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE tenant_id = ? AND is_active = 1 
    ORDER BY category ASC, name ASC
");
$productsStmt->execute([$tenantId]);
$products = $productsStmt->fetchAll();

// Fetch All Gallery Images for Tenant Products
$productIds = array_column($products, 'id');
$productGalleryMap = [];
if (!empty($productIds)) {
    $inClause = implode(',', array_fill(0, count($productIds), '?'));
    $galleryStmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id IN ($inClause) ORDER BY is_primary DESC, id ASC");
    $galleryStmt->execute($productIds);
    $allImages = $galleryStmt->fetchAll();
    foreach ($allImages as $img) {
        $productGalleryMap[$img['product_id']][] = $img['image_url'];
    }
}

// Map products data to JS array
$productsJson = json_encode(array_map(function($p) use ($productGalleryMap) {
    $gallery = $productGalleryMap[$p['id']] ?? [];
    if (empty($gallery) && !empty($p['photo_url'])) {
        $gallery = [$p['photo_url']];
    }
    return [
        'id'          => (int)$p['id'],
        'name'        => $p['name'],
        'price'       => (float)$p['price'],
        'stock'       => (int)$p['stock_count'],
        'category'    => $p['category'],
        'photo_url'   => $p['photo_url'],
        'gallery'     => $gallery
    ];
}, $products));

// Get Unique Categories
$categories = array_values(array_unique(array_column($products, 'category')));
?>
<!DOCTYPE html>
<html lang="en" class="h-full font-sans antialiased bg-[#FFFDF7] text-slate-900">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= htmlspecialchars($shopName) ?> — Online Storefront</title>
  <!-- Favicon -->
  <link rel="icon" type="image/svg+xml" href="/assets/logo.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwindcss = {
      theme: {
        extend: {
          colors: {
            brand: { 50: '#fefce8', 100: '#fef9c3', 200: '#fef08a', 300: '#fde047', 400: '#facc15', 500: '#f5b400', 600: '#d97f00', 700: '#b45309' }
          },
          fontFamily: { sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'] }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF7; color: #0F172A; overflow-x: hidden; }
    .app-card { background-color: #FFFFFF; border: 1px solid #F1F5F9; box-shadow: 0 2px 10px -2px rgba(245, 180, 0, 0.06); border-radius: 1rem; }
    .btn-cta { background-color: #D97F00; color: #FFFFFF; font-weight: 800; }
    .btn-cta:hover { background-color: #B45309; }
    .touch-target { min-height: 44px; }
  </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-[#FFFDF7] text-slate-900 pb-28 md:pb-12 overflow-x-hidden">

<!-- Store Header Bar -->
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-brand-200 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      
      <!-- Store Branding -->
      <div class="flex items-center space-x-3 shrink-0">
        <a href="/" title="LocalShopOS Platform" class="shrink-0">
          <img src="/assets/logo.png" alt="LocalShopOS" class="w-8 h-8">
        </a>

        <div class="w-9 h-9 rounded-xl bg-brand-500 text-slate-950 font-black text-lg flex items-center justify-center shrink-0 overflow-hidden border border-brand-300 shadow-sm">
          <?php if (!empty($logoUrl)): ?>
            <img src="<?= htmlspecialchars($logoUrl) ?>" alt="Store Logo" class="w-full h-full object-cover">
          <?php else: ?>
            <?= mb_substr(htmlspecialchars($shopName), 0, 1) ?>
          <?php endif; ?>
        </div>

        <div class="truncate max-w-[150px] sm:max-w-xs">
          <h1 class="font-black text-sm sm:text-base text-slate-900 leading-tight flex items-center gap-1.5 truncate">
            <span class="truncate"><?= htmlspecialchars($shopName) ?></span>
            <?php if (!$isOpen): ?>
              <span class="px-1.5 py-0.5 text-[9px] font-black bg-rose-100 text-rose-800 rounded-full shrink-0">CLOSED</span>
            <?php elseif ($isTrialExpired): ?>
              <span class="px-1.5 py-0.5 text-[9px] font-black bg-amber-100 text-amber-900 rounded-full shrink-0">TRIAL EXPIRED</span>
            <?php endif; ?>
          </h1>
          <span class="text-[10px] sm:text-[11px] text-amber-700 font-extrabold uppercase tracking-wider block truncate"><?= htmlspecialchars($category) ?></span>
        </div>
      </div>

      <!-- Desktop Search Bar -->
      <div class="hidden md:flex items-center space-x-4 flex-1 max-w-xl mx-8">
        <div class="relative w-full">
          <input type="text" id="desktopSearchInput" onkeyup="syncDesktopSearch(this.value)" placeholder="Search products in store..."
                 class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-400">
          <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
      </div>

      <!-- Header Actions -->
      <div class="flex items-center space-x-2 shrink-0">
        <button onclick="openCustomerOrdersModal()" class="hidden sm:flex px-3 py-2 text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all items-center space-x-1.5 touch-target">
          <svg class="w-4 h-4 text-brand-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
          <span>My Orders</span>
        </button>

        <button onclick="toggleCartDrawer()" class="px-3.5 py-2.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md flex items-center space-x-1.5 touch-target">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
          <span>Cart</span>
          <span id="cartBadgeCountHeader" class="w-5 h-5 rounded-full bg-slate-900 text-brand-400 text-[10px] font-black flex items-center justify-center">0</span>
        </button>
      </div>

    </div>
  </div>
</header>

<!-- Order Confirmation Toast / Banner -->
<div id="orderConfirmationBanner" class="hidden max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
  <div class="bg-emerald-50 border-2 border-emerald-300 text-emerald-950 p-4 rounded-2xl shadow-md flex items-start justify-between">
    <div class="flex items-start space-x-3">
      <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white font-black text-lg flex items-center justify-center shrink-0">
        ✓
      </div>
      <div>
        <h4 class="font-black text-sm text-emerald-900">Order Placed Successfully!</h4>
        <p class="text-xs text-emerald-800 font-medium mt-0.5" id="orderConfirmationText">We've generated your order and opened WhatsApp to notify <?= htmlspecialchars($shopName) ?>.</p>
        <button onclick="openCustomerOrdersModal()" class="mt-2 text-xs font-black text-emerald-900 underline hover:text-emerald-700">Track Status Timeline in "My Orders" &rarr;</button>
      </div>
    </div>
    <button onclick="document.getElementById('orderConfirmationBanner').classList.add('hidden')" class="text-emerald-500 hover:text-emerald-800 font-black text-lg p-1">&times;</button>
  </div>
</div>

<?php if ($isTrialExpired): ?>
  <div class="bg-amber-500 text-slate-950 text-center py-3 px-4 font-black text-xs shadow-md">
    ⚠️ Store Notice: This merchant's trial period has expired. The store is temporarily unavailable for new orders.
  </div>
<?php elseif (!$isOpen): ?>
  <div class="bg-amber-100 text-amber-900 border-b border-amber-300 text-center py-2.5 px-4 font-black text-xs">
    ⚠️ Store Notice: We are currently closed for new orders. Browsing catalog is open!
  </div>
<?php endif; ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full">
  
  <!-- Banner Carousel -->
  <?php if (!empty($allBannerAds)): ?>
    <div class="mb-6 relative rounded-2xl overflow-hidden shadow-md border border-brand-200 bg-slate-900">
      <div id="bannerCarousel" class="flex transition-transform duration-500">
        <?php foreach ($allBannerAds as $bAd): ?>
          <div class="w-full shrink-0 h-36 sm:h-60 relative">
            <?php if (!empty($bAd['link_url'])): ?>
              <a href="<?= htmlspecialchars($bAd['link_url']) ?>" target="_blank">
                <img src="<?= htmlspecialchars($bAd['image_url']) ?>" class="w-full h-full object-cover">
              </a>
            <?php else: ?>
              <img src="<?= htmlspecialchars($bAd['image_url']) ?>" class="w-full h-full object-cover">
            <?php endif; ?>

            <?php if ($bAd['is_global']): ?>
              <span class="absolute top-2 left-2 px-2.5 py-1 text-[9px] font-black uppercase rounded-full shadow-md bg-amber-400 text-slate-950 border border-amber-500">
                ★ Platform Deal
              </span>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Search & Category Filters (Includes Wishlist Pill) -->
  <div id="categoriesBarSection" class="app-card p-3.5 sm:p-4 rounded-2xl mb-6 bg-white space-y-3">
    <div class="relative w-full md:hidden">
      <input type="text" id="storeSearchInput" onkeyup="filterStorefrontProducts()" placeholder="Search items in store..."
             class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-400">
      <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>

    <div class="flex items-center space-x-2 overflow-x-auto w-full pb-1">
      <button onclick="filterCategory('ALL', this)" class="cat-pill active px-4 py-2 rounded-full text-xs font-black whitespace-nowrap bg-brand-500 text-slate-950 shadow-sm">All Items</button>
      <button onclick="filterCategory('WISHLIST', this)" class="cat-pill px-4 py-2 rounded-full text-xs font-black whitespace-nowrap bg-rose-100 text-rose-800 border border-rose-200 hover:bg-rose-200 transition-all flex items-center space-x-1">
        <span>Wishlist</span>
        <span>❤️</span>
      </button>
      <?php foreach ($categories as $cat): ?>
        <button onclick="filterCategory('<?= htmlspecialchars($cat) ?>', this)" class="cat-pill px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all">
          <?= htmlspecialchars($cat) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Storefront Products Grid -->
  <div id="storeProductsGrid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-6"></div>

</main>

<footer class="mt-8 mb-24 md:mb-12 py-6 border-t border-brand-200/60 text-center">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <a href="/" target="_blank" class="inline-flex flex-col items-center group touch-target">
      <span class="text-[10px] sm:text-xs font-bold text-slate-500 mb-1">Powered by</span>
      <div class="flex items-center space-x-1.5 opacity-80 group-hover:opacity-100 transition-opacity">
        <img src="/assets/logo.png" alt="<?= htmlspecialchars($siteName) ?>" class="w-5 h-5 grayscale group-hover:grayscale-0 transition-all">
        <span class="font-black text-sm text-slate-800 tracking-tight"><?= htmlspecialchars($siteName) ?></span>
      </div>
      <span class="text-[10px] text-slate-400 mt-1 font-medium group-hover:text-brand-600 transition-colors">Create your own online store for free &rarr;</span>
    </a>
  </div>
</footer>


<!-- Add to Cart Toast -->
<div id="addToCartToast" class="hidden fixed bottom-20 right-4 sm:bottom-6 sm:right-6 z-50 bg-slate-900 text-white p-3.5 sm:p-4 rounded-2xl shadow-2xl border border-slate-800 flex items-center space-x-3 transition-all transform translate-y-2 max-w-sm">
  <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 overflow-hidden shrink-0 flex items-center justify-center">
    <img id="toastImg" class="w-full h-full object-cover">
  </div>
  <div class="flex-1 min-w-0">
    <h5 id="toastTitle" class="text-xs font-black text-white truncate"></h5>
    <p class="text-[11px] text-emerald-400 font-extrabold flex items-center gap-1">
      <span>✓ Added to cart</span>
    </p>
  </div>
  <div class="flex items-center space-x-1.5 shrink-0">
    <button onclick="toggleCartDrawer()" class="px-2.5 py-1.5 bg-brand-500 text-slate-950 font-black text-[11px] rounded-lg shadow-sm hover:bg-brand-400">
      View Cart
    </button>
  </div>
</div>

<!-- Mobile Bottom Nav -->
<nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-brand-200 py-1.5 px-2 shadow-lg">
  <div class="grid grid-cols-4 text-center">
    <button onclick="navGoHome()" id="bNavHome" class="flex flex-col items-center justify-center py-1 text-slate-900 font-black touch-target">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
      <span class="text-[10px] tracking-tight">Home</span>
    </button>

    <button onclick="navGoCategories()" id="bNavCategories" class="flex flex-col items-center justify-center py-1 text-slate-500 font-bold touch-target">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
      <span class="text-[10px] tracking-tight">Categories</span>
    </button>

    <button onclick="toggleCartDrawer()" id="bNavCart" class="flex flex-col items-center justify-center py-1 text-slate-500 font-bold relative touch-target">
      <div class="relative">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        <span id="cartBadgeCountBottom" class="absolute -top-1.5 -right-2.5 w-4 h-4 rounded-full bg-brand-500 text-slate-950 text-[9px] font-black flex items-center justify-center border border-white">0</span>
      </div>
      <span class="text-[10px] tracking-tight">Cart</span>
    </button>

    <button onclick="openCustomerOrdersModal()" id="bNavOrders" class="flex flex-col items-center justify-center py-1 text-slate-500 font-bold touch-target">
      <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
      <span class="text-[10px] tracking-tight">Orders</span>
    </button>
  </div>
</nav>

<!-- My Orders Modal with Visual Status Timeline (Fix 7 Priority) -->
<div id="customerOrdersModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-lg p-5 rounded-2xl shadow-2xl relative max-h-[85vh] overflow-y-auto bg-white flex flex-col justify-between">
    <div>
      <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
          <span>Track Order Status</span>
          <span class="text-xs font-bold text-amber-800 bg-brand-100 px-2 py-0.5 rounded-full border border-brand-300"><?= htmlspecialchars($shopName) ?></span>
        </h3>
        <button onclick="document.getElementById('customerOrdersModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl p-1">&times;</button>
      </div>

      <div class="mb-4 space-y-1">
        <label class="block text-[11px] font-black uppercase text-slate-600">Your Contact Phone Number</label>
        <div class="flex space-x-2">
          <input type="text" id="myOrdersPhoneInput" placeholder="Enter 10-digit WhatsApp number" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
          <button onclick="fetchCustomerOrders()" class="px-3.5 py-2 bg-slate-900 text-white text-xs font-black rounded-xl hover:bg-slate-800 shrink-0">Look Up</button>
        </div>
      </div>

      <div id="myOrdersList" class="space-y-4 min-h-[150px]"></div>
    </div>

    <div class="pt-4 border-t border-slate-100 text-center mt-4">
      <button onclick="document.getElementById('customerOrdersModal').classList.add('hidden')" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-black rounded-xl">Close</button>
    </div>
  </div>
</div>

<!-- Product Detail Modal -->
<div id="productDetailModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-md p-5 rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto bg-white">
    <button onclick="document.getElementById('productDetailModal').classList.add('hidden')" class="absolute top-3 right-3 text-slate-400 hover:text-slate-600 font-bold text-xl p-1">&times;</button>
    
    <div class="h-52 rounded-xl bg-slate-100 overflow-hidden mb-3 border border-slate-200 flex items-center justify-center relative">
      <img id="detailMainImg" class="w-full h-full object-cover">
      <button id="modalFavBtn" onclick="toggleModalFav()" class="absolute top-2 right-2 p-2 bg-white/90 rounded-full shadow-md text-base">🤍</button>
    </div>

    <div id="detailGalleryStrip" class="flex items-center space-x-2 overflow-x-auto pb-2 mb-3"></div>

    <div class="space-y-1.5 mb-5">
      <span id="detailCategory" class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase bg-brand-100 text-amber-800 border border-brand-300 inline-block"></span>
      <h3 id="detailName" class="text-base font-black text-slate-900 leading-tight"></h3>
      <div class="flex items-center justify-between pt-1">
        <span id="detailPrice" class="text-lg font-black text-emerald-700"></span>
        <span id="detailStock" class="text-xs text-slate-500 font-bold"></span>
      </div>
    </div>

    <div class="flex items-center space-x-2 pt-3 border-t border-slate-100">
      <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
        <button onclick="changeModalQty(-1)" class="px-3.5 py-2.5 text-slate-800 font-black text-sm hover:bg-slate-200 touch-target">-</button>
        <span id="modalQtyVal" class="px-3 py-2.5 text-xs font-black text-slate-900">1</span>
        <button onclick="changeModalQty(1)" class="px-3.5 py-2.5 text-slate-800 font-black text-sm hover:bg-slate-200 touch-target">+</button>
      </div>

      <button id="modalAddToCartBtn" onclick="addActiveModalToCart()" class="flex-1 py-3 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md transition-all touch-target">
        Add to Cart
      </button>
    </div>
  </div>
</div>

<!-- Slide-in Cart Drawer with Pickup/Delivery & Payment Mode Options -->
<div id="cartDrawerOverlay" onclick="toggleCartDrawer()" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm transition-opacity"></div>
<div id="cartDrawer" class="fixed inset-y-0 right-0 z-50 w-full sm:max-w-md bg-white shadow-2xl transform translate-x-full transition-transform duration-300 flex flex-col justify-between p-5 sm:p-6 overflow-y-auto">
  <div>
    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
      <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
        <span>Shopping Cart</span>
      </h3>
      <button onclick="toggleCartDrawer()" class="text-slate-400 hover:text-slate-600 font-bold text-2xl p-1">&times;</button>
    </div>
    
    <!-- Welcome Back Badge for Repeat Customers -->
    <div id="welcomeBackBadge" class="hidden my-3 p-2.5 bg-brand-50 border border-brand-200 rounded-xl text-xs text-amber-900 font-bold flex items-center gap-2">
      <span class="text-base">👋</span>
      <span>Welcome back! Your contact & delivery details are pre-filled.</span>
    </div>

    <div id="cartItemsList" class="py-3 space-y-3 max-h-[30vh] overflow-y-auto"></div>
  </div>

  <div class="pt-3 border-t border-slate-100 space-y-3">
    
    <!-- Pickup vs Delivery Toggle (v13 feature) -->
    <?php if ($deliveryEnabled): ?>
      <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200 space-y-2">
        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700">Order Fulfillment Mode</label>
        <div class="grid grid-cols-2 gap-2">
          <button type="button" id="btnFulfillmentDelivery" onclick="setFulfillmentType('delivery')" class="py-2 px-3 rounded-xl text-xs font-black transition-all border border-brand-500 bg-brand-500 text-slate-950 shadow-sm flex items-center justify-center space-x-1">
            <span>🚚 Delivery</span>
          </button>
          <button type="button" id="btnFulfillmentPickup" onclick="setFulfillmentType('pickup')" class="py-2 px-3 rounded-xl text-xs font-bold transition-all border border-slate-200 bg-white text-slate-700 flex items-center justify-center space-x-1">
            <span>🏬 Pickup</span>
          </button>
        </div>
        <?php if (!empty($deliveryAreaNote)): ?>
          <p class="text-[10px] text-slate-500 font-bold flex items-center gap-1">
            <span>📦</span> <span><?= htmlspecialchars($deliveryAreaNote) ?></span>
          </p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-[11px] font-black text-amber-900 flex items-center gap-1.5">
        <span>🏬</span> <span>Store Pickup Only (Home delivery unavailable for this shop)</span>
      </div>
    <?php endif; ?>

    <!-- Delivery Address Input Box (Shown if delivery selected) -->
    <div id="deliveryAddressContainer" class="<?= $deliveryEnabled ? '' : 'hidden' ?> space-y-1.5">
      <div class="flex items-center justify-between">
        <label class="block text-[11px] font-black uppercase text-slate-700">Delivery Address *</label>
        <button type="button" onclick="useCurrentLocation()" class="text-[10px] font-bold text-amber-800 hover:underline flex items-center gap-1">
          <span>📍 Auto-fill Location</span>
        </button>
      </div>
      <textarea id="customerAddressInput" rows="2" placeholder="Street, Flat/House No, Landmark..." class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-brand-400"></textarea>
      <div id="locStatusMsg" class="hidden text-[10px] font-bold text-emerald-700"></div>
    </div>

    <!-- Payment Mode Selection -->
    <div class="space-y-1.5">
      <label class="block text-[11px] font-black uppercase text-slate-700">Payment Mode</label>
      <div class="grid grid-cols-3 gap-1.5" id="paymentModeGroup">
        <button type="button" onclick="setPaymentMode('cod')" id="btnPayCod" class="py-1.5 px-2 rounded-xl text-[11px] font-black border border-slate-900 bg-slate-900 text-white transition-all">
          💵 Cash
        </button>
        <button type="button" onclick="setPaymentMode('upi')" id="btnPayUpi" class="py-1.5 px-2 rounded-xl text-[11px] font-bold border border-slate-200 bg-slate-50 text-slate-700 transition-all">
          📱 UPI
        </button>
        <button type="button" onclick="setPaymentMode('pickup_pay')" id="btnPayPickup" class="py-1.5 px-2 rounded-xl text-[11px] font-bold border border-slate-200 bg-slate-50 text-slate-700 transition-all">
          🏬 Pay @ Store
        </button>
      </div>
    </div>

    <!-- Coupon Code Input Box -->
    <div class="bg-slate-50 p-2.5 rounded-2xl border border-slate-200 space-y-1.5">
      <label class="block text-[10px] font-black uppercase text-slate-700">Have a Promo / Coupon Code?</label>
      <div class="flex space-x-2">
        <input type="text" id="cartCouponInput" placeholder="WELCOME10" class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl text-xs font-mono font-bold uppercase text-slate-900">
        <button onclick="applyCartCoupon()" class="px-3 py-1.5 bg-slate-900 text-white font-black text-xs rounded-xl hover:bg-slate-800 shrink-0">Apply</button>
      </div>
      <div id="couponNotice" class="hidden text-[11px] font-bold"></div>
    </div>

    <!-- Minimum Order Warning Box -->
    <div id="minOrderWarning" class="hidden p-2.5 bg-rose-50 border border-rose-200 rounded-xl text-[11px] font-black text-rose-700"></div>

    <!-- Bill Summary -->
    <div class="space-y-1 bg-brand-50/60 p-3 rounded-2xl border border-brand-200">
      <div class="flex items-center justify-between text-xs font-bold text-slate-600">
        <span>Subtotal</span>
        <span id="cartSubtotalText">₹0.00</span>
      </div>
      <div id="couponDiscountRow" class="hidden flex items-center justify-between text-xs font-bold text-emerald-700">
        <span>Coupon Discount (<strong id="appliedCouponCodeTag"></strong>)</span>
        <span id="cartDiscountText">-₹0.00</span>
      </div>
      <div id="deliveryFeeRow" class="<?= ($deliveryEnabled && $deliveryFee > 0) ? '' : 'hidden' ?> flex items-center justify-between text-xs font-bold text-slate-600">
        <span>Delivery Fee</span>
        <span id="cartDeliveryFeeText">₹<?= number_format($deliveryFee, 2) ?></span>
      </div>
      <div class="flex items-center justify-between pt-1 border-t border-brand-200/60">
        <span class="text-xs text-slate-900 font-black uppercase">Final Total Bill</span>
        <span id="cartGrandTotal" class="text-2xl font-black text-emerald-700">₹0.00</span>
      </div>
    </div>

    <div>
      <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Your WhatsApp Phone Number *</label>
      <input type="text" id="customerPhoneInput" oninput="checkPastCustomer()" placeholder="9876543210" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold focus:ring-2 focus:ring-brand-400">
    </div>

    <button id="submitOrderBtn" onclick="submitWhatsAppOrder()" <?= (!$isOpen || !$isStoreActive) ? 'disabled' : '' ?> class="w-full py-3.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-black text-sm rounded-xl shadow-lg flex items-center justify-center space-x-2 touch-target">
      <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.099 4.019 4.042-1.058z"/></svg>
      <span>Order via WhatsApp &rarr;</span>
    </button>
  </div>
</div>

<script>
const productsData       = <?= $productsJson ?>;
const midAdsData         = <?= json_encode($allMidAds) ?>;
const bannerAdsData      = <?= json_encode($allBannerAds) ?>;
const tenantId           = <?= $tenantId ?>;
const shopNameStr        = "<?= htmlspecialchars($shopName) ?>";
const whatsappNo         = "<?= htmlspecialchars($whatsappNumber) ?>";
const isDeliveryEnabled  = <?= $deliveryEnabled ?>;
const tenantDeliveryFee  = <?= $deliveryFee ?>;
const minDeliveryOrder   = <?= $minDeliveryOrder ?>;
const shopThankYouMsg    = "<?= htmlspecialchars($thankYouMsg) ?>";

let activeFulfillment = isDeliveryEnabled ? 'delivery' : 'pickup';
let activePaymentMode  = 'cod';

let cart = JSON.parse(localStorage.getItem('cart_' + tenantId) || '{}');
let wishlist = JSON.parse(localStorage.getItem('wishlist_' + tenantId) || '[]');
let activeCategoryFilter = 'ALL';
let activeModalProduct = null;
let modalQty = 1;
let toastTimeout = null;
let appliedCouponData = null;

// Track Tenant Ad or Global Platform Ad Impression
function trackAdImpression(adObj) {
  if (!adObj || !adObj.id) return;
  if (adObj.is_global) {
    fetch('/api/track_global_ad_view.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ global_ad_id: adObj.id, tenant_id: tenantId })
    }).catch(() => {});
  } else {
    fetch('/api/track_ad_view.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ ad_id: adObj.id, tenant_id: tenantId })
    }).catch(() => {});
  }
}

if (bannerAdsData.length > 0) trackAdImpression(bannerAdsData[0]);

const storedPhone   = localStorage.getItem('customer_phone_' + tenantId) || '';
const storedAddress = localStorage.getItem('customer_address_' + tenantId) || '';

if (storedPhone) {
  const pInput = document.getElementById('customerPhoneInput');
  const oInput = document.getElementById('myOrdersPhoneInput');
  if (pInput) pInput.value = storedPhone;
  if (oInput) oInput.value = storedPhone;
}
if (storedAddress) {
  const addrEl = document.getElementById('customerAddressInput');
  if (addrEl) addrEl.value = storedAddress;
}
if (storedPhone || storedAddress) {
  const wb = document.getElementById('welcomeBackBadge');
  if (wb) wb.classList.remove('hidden');
}

// Highlight initial fulfillment mode
setFulfillmentType(activeFulfillment);

function setFulfillmentType(type) {
  if (!isDeliveryEnabled && type === 'delivery') return;
  activeFulfillment = type;
  
  const btnDel = document.getElementById('btnFulfillmentDelivery');
  const btnPic = document.getElementById('btnFulfillmentPickup');
  const addrBox = document.getElementById('deliveryAddressContainer');

  const activeClass   = "py-2.5 px-3 rounded-xl text-xs font-black transition-all border-2 border-slate-900 bg-slate-900 text-white shadow-md flex items-center justify-center space-x-1.5 ring-2 ring-slate-900/20";
  const inactiveClass = "py-2.5 px-3 rounded-xl text-xs font-bold transition-all border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 flex items-center justify-center space-x-1.5 opacity-75";

  if (type === 'delivery') {
    if (btnDel) btnDel.className = activeClass;
    if (btnPic) btnPic.className = inactiveClass;
    if (addrBox) addrBox.classList.remove('hidden');
  } else {
    if (btnDel) btnDel.className = inactiveClass;
    if (btnPic) btnPic.className = activeClass;
    if (addrBox) addrBox.classList.add('hidden');
  }
  updateCartBadge();
}

function setPaymentMode(mode) {
  activePaymentMode = mode;
  ['cod', 'upi', 'pickup_pay'].forEach(m => {
    let btnId = 'btnPayCod';
    if (m === 'upi') btnId = 'btnPayUpi';
    if (m === 'pickup_pay') btnId = 'btnPayPickup';
    const btn = document.getElementById(btnId);
    if (btn) {
      if (m === mode) {
        btn.className = "py-1.5 px-2 rounded-xl text-[11px] font-black border border-slate-900 bg-slate-900 text-white transition-all";
      } else {
        btn.className = "py-1.5 px-2 rounded-xl text-[11px] font-bold border border-slate-200 bg-slate-50 text-slate-700 transition-all";
      }
    }
  });
}

function useCurrentLocation() {
  const msgEl = document.getElementById('locStatusMsg');
  if (!navigator.geolocation) {
    alert("Geolocation is not supported by your browser.");
    return;
  }
  if (msgEl) {
    msgEl.innerText = "Fetching your current location...";
    msgEl.className = "text-[10px] font-bold text-amber-800 block";
  }
  navigator.geolocation.getCurrentPosition(
    position => {
      const lat = position.coords.latitude;
      const lon = position.coords.longitude;
      fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`)
        .then(res => res.json())
        .then(data => {
          const addr = data.display_name || `Location: Lat ${lat.toFixed(4)}, Lon ${lon.toFixed(4)}`;
          document.getElementById('customerAddressInput').value = addr;
          if (msgEl) {
            msgEl.innerText = "✓ Location auto-filled!";
            msgEl.className = "text-[10px] font-bold text-emerald-700 block";
          }
        })
        .catch(() => {
          document.getElementById('customerAddressInput').value = `GPS Location: Lat ${lat.toFixed(4)}, Lon ${lon.toFixed(4)}`;
          if (msgEl) {
            msgEl.innerText = "✓ GPS coordinates saved!";
            msgEl.className = "text-[10px] font-bold text-emerald-700 block";
          }
        });
    },
    error => {
      if (msgEl) {
        msgEl.innerText = "Unable to fetch location. Please enter manually.";
        msgEl.className = "text-[10px] font-bold text-rose-600 block";
      }
    }
  );
}

function checkPastCustomer() {
  const phone = document.getElementById('customerPhoneInput').value.trim();
  const savedPhone = localStorage.getItem('customer_phone_' + tenantId);
  const wb = document.getElementById('welcomeBackBadge');
  if (wb) {
    if (phone.length >= 10 && phone === savedPhone) {
      wb.classList.remove('hidden');
    }
  }
}

const carousel = document.getElementById('bannerCarousel');
if (carousel && carousel.children.length > 1) {
  let slideIndex = 0;
  setInterval(() => {
    slideIndex = (slideIndex + 1) % carousel.children.length;
    carousel.style.transform = `translateX(-${slideIndex * 100}%)`;
    if (bannerAdsData[slideIndex]) {
      trackAdImpression(bannerAdsData[slideIndex]);
    }
  }, 4500);
}

function syncDesktopSearch(val) {
  const mobSearch = document.getElementById('storeSearchInput');
  if (mobSearch) mobSearch.value = val;
  renderStorefrontGrid();
}

function toggleWishlist(prodId, event) {
  if (event) event.stopPropagation();
  const index = wishlist.indexOf(prodId);
  if (index >= 0) {
    wishlist.splice(index, 1);
  } else {
    wishlist.push(prodId);
  }
  localStorage.setItem('wishlist_' + tenantId, JSON.stringify(wishlist));
  renderStorefrontGrid();
}

function renderStorefrontGrid() {
  const container = document.getElementById('storeProductsGrid');
  const query = (document.getElementById('storeSearchInput').value || document.getElementById('desktopSearchInput').value || '').toLowerCase().trim();

  let filtered = productsData.filter(p => {
    let matchCat = false;
    if (activeCategoryFilter === 'ALL') {
      matchCat = true;
    } else if (activeCategoryFilter === 'WISHLIST') {
      matchCat = wishlist.includes(p.id);
    } else {
      matchCat = (p.category === activeCategoryFilter);
    }
    const matchQuery = p.name.toLowerCase().includes(query);
    return matchCat && matchQuery;
  });

  if (filtered.length === 0) {
    container.innerHTML = `
      <div class="col-span-full text-center py-16 app-card rounded-2xl bg-white">
        <div class="text-4xl mb-2">${activeCategoryFilter === 'WISHLIST' ? '❤️' : '🔍'}</div>
        <h4 class="font-black text-slate-900">${activeCategoryFilter === 'WISHLIST' ? 'No items saved in wishlist yet' : 'No products found'}</h4>
        <p class="text-xs text-slate-500 mt-1 font-medium">${activeCategoryFilter === 'WISHLIST' ? 'Tap the heart icon on items to save them here!' : 'Try clearing search keywords.'}</p>
      </div>
    `;
    return;
  }

  let html = '';
  let midAdIndex = 0;

  filtered.forEach((p, idx) => {
    const qtyInCart = cart[p.id] ? cart[p.id].qty : 0;
    const isFav = wishlist.includes(p.id);

    html += `
      <div class="app-card rounded-2xl overflow-hidden flex flex-col justify-between group shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all bg-white border border-slate-200 relative">
        <div>
          <div class="h-36 sm:h-48 bg-slate-100 overflow-hidden relative flex items-center justify-center cursor-pointer" onclick="openProductModal(${p.id})">
            ${p.photo_url ? `<img src="${p.photo_url}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">` : '<span class="text-3xl">🛍️</span>'}
            <span class="absolute top-2 left-2 px-2 py-0.5 text-[8px] sm:text-[9px] font-black bg-slate-900 text-white rounded-md uppercase">${p.category}</span>
            <button onclick="toggleWishlist(${p.id}, event)" class="absolute top-2 right-2 p-1.5 rounded-full bg-white/90 shadow-sm text-sm hover:scale-110 transition-transform">
              ${isFav ? '❤️' : '🤍'}
            </button>
          </div>

          <div class="p-3.5 space-y-1">
            <h4 class="font-extrabold text-slate-900 text-xs sm:text-sm line-clamp-2 cursor-pointer hover:text-amber-800 leading-tight h-8 sm:h-10" onclick="openProductModal(${p.id})">${p.name}</h4>
            <div class="flex items-center justify-between pt-1">
              <span class="text-sm sm:text-base font-black text-emerald-700">₹${p.price.toFixed(2)}</span>
              <span class="text-[10px] text-slate-500 font-bold">${p.stock > 0 ? 'In Stock' : 'Out of Stock'}</span>
            </div>
          </div>
        </div>

        <div class="p-2.5 bg-slate-50 border-t border-slate-100">
          ${qtyInCart > 0 ? `
            <div class="flex items-center justify-between bg-slate-900 text-white rounded-xl overflow-hidden text-xs font-black min-h-[40px]">
              <button onclick="updateCartQty(${p.id}, -1)" class="px-3 py-2 hover:bg-slate-800 touch-target">-</button>
              <span>${qtyInCart} in cart</span>
              <button onclick="updateCartQty(${p.id}, 1)" class="px-3 py-2 hover:bg-slate-800 touch-target">+</button>
            </div>
          ` : `
            <button onclick="updateCartQty(${p.id}, 1, true)" class="w-full py-2.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-sm transition-all min-h-[40px] flex items-center justify-center">
              + Add to Cart
            </button>
          `}
        </div>
      </div>
    `;

    if ((idx + 1) % 8 === 0 && midAdsData.length > 0) {
      const ad = midAdsData[midAdIndex % midAdsData.length];
      trackAdImpression(ad);
      midAdIndex++;
      html += `
        <div class="col-span-full my-3 rounded-2xl overflow-hidden shadow-md bg-slate-900 border border-slate-800 h-28 sm:h-44 relative">
          ${ad.link_url ? `<a href="${ad.link_url}" target="_blank"><img src="${ad.image_url}" class="w-full h-full object-cover"></a>` : `<img src="${ad.image_url}" class="w-full h-full object-cover">`}
          <span class="absolute top-2 right-2 px-2 py-0.5 text-[9px] font-black rounded-md uppercase ${ad.is_global ? 'bg-amber-400 text-slate-950 border border-amber-500' : 'bg-brand-500 text-slate-950'}">
            ${ad.is_global ? '★ Platform Deal' : 'Promoted Banner'}
          </span>
        </div>
      `;
    }
  });

  container.innerHTML = html;
  updateCartBadge();
}

function openProductModal(prodId) {
  const p = productsData.find(item => item.id === prodId);
  if (!p) return;
  activeModalProduct = p;
  modalQty = cart[p.id] ? cart[p.id].qty : 1;
  if (modalQty < 1) modalQty = 1;

  document.getElementById('detailName').innerText = p.name;
  document.getElementById('detailPrice').innerText = '₹' + p.price.toFixed(2);
  document.getElementById('detailCategory').innerText = p.category;
  document.getElementById('detailStock').innerText = 'In Stock: ' + p.stock;
  document.getElementById('modalQtyVal').innerText = modalQty;
  document.getElementById('modalFavBtn').innerText = wishlist.includes(p.id) ? '❤️' : '🤍';

  const mainImg = document.getElementById('detailMainImg');
  const galleryStrip = document.getElementById('detailGalleryStrip');
  galleryStrip.innerHTML = '';

  const gallery = p.gallery && p.gallery.length > 0 ? p.gallery : [p.photo_url];
  mainImg.src = gallery[0] || '';

  if (gallery.length > 1) {
    gallery.forEach((url, i) => {
      const btn = document.createElement('button');
      btn.className = `w-12 h-12 rounded-lg overflow-hidden border-2 shrink-0 ${i===0?'border-brand-500':'border-transparent opacity-60'}`;
      btn.innerHTML = `<img src="${url}" class="w-full h-full object-cover">`;
      btn.onclick = () => {
        mainImg.src = url;
        Array.from(galleryStrip.children).forEach(c => c.className = 'w-12 h-12 rounded-lg overflow-hidden border-2 shrink-0 border-transparent opacity-60');
        btn.className = 'w-12 h-12 rounded-lg overflow-hidden border-2 shrink-0 border-brand-500 scale-105';
      };
      galleryStrip.appendChild(btn);
    });
  }

  document.getElementById('productDetailModal').classList.remove('hidden');
}

function toggleModalFav() {
  if (activeModalProduct) {
    toggleWishlist(activeModalProduct.id);
    document.getElementById('modalFavBtn').innerText = wishlist.includes(activeModalProduct.id) ? '❤️' : '🤍';
  }
}

function changeModalQty(delta) {
  modalQty += delta;
  if (modalQty < 1) modalQty = 1;
  document.getElementById('modalQtyVal').innerText = modalQty;
}

function addActiveModalToCart() {
  if (activeModalProduct) {
    cart[activeModalProduct.id] = { product: activeModalProduct, qty: modalQty };
    localStorage.setItem('cart_' + tenantId, JSON.stringify(cart));
    document.getElementById('productDetailModal').classList.add('hidden');
    showAddToCartToast(activeModalProduct);
    renderStorefrontGrid();
  }
}

function updateCartQty(prodId, delta, showToast = false) {
  const p = productsData.find(item => item.id === prodId);
  if (!p) return;

  if (!cart[prodId]) {
    cart[prodId] = { product: p, qty: 0 };
  }
  cart[prodId].qty += delta;

  if (cart[prodId].qty <= 0) {
    delete cart[prodId];
  }

  localStorage.setItem('cart_' + tenantId, JSON.stringify(cart));

  if (showToast && cart[prodId]) {
    showAddToCartToast(p);
  }

  renderStorefrontGrid();
}

function showAddToCartToast(product) {
  const toast = document.getElementById('addToCartToast');
  const toastImg = document.getElementById('toastImg');
  const toastTitle = document.getElementById('toastTitle');

  toastImg.src = product.photo_url || '/assets/logo.png';
  toastTitle.innerText = product.name;

  toast.classList.remove('hidden');
  toast.classList.remove('translate-y-2');

  if (toastTimeout) clearTimeout(toastTimeout);
  toastTimeout = setTimeout(() => {
    toast.classList.add('translate-y-2');
    toast.classList.add('hidden');
  }, 4000);
}

function applyCartCoupon() {
  const code = document.getElementById('cartCouponInput').value.trim().toUpperCase();
  const notice = document.getElementById('couponNotice');
  if (!code) { 
    notice.className = "text-[11px] font-extrabold text-rose-600 block";
    notice.innerText = "Please enter a coupon code.";
    return;
  }

  notice.className = "text-[11px] font-bold text-slate-500 block";
  notice.innerText = "Checking coupon...";

  let subtotal = 0;
  Object.values(cart).forEach(item => subtotal += (item.qty * item.product.price));

  fetch('/api/validate_coupon.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ tenant_id: tenantId, code: code, subtotal: subtotal })
  })
  .then(res => {
    if (!res.ok) throw new Error('Server error: ' + res.status);
    return res.json();
  })
  .then(data => {
    if (data.success) {
      appliedCouponData = data;
      notice.className = "text-[11px] font-extrabold text-emerald-600 block";
      notice.innerText = `✓ Coupon '${data.code}' applied! You save ₹${data.discount_amount.toFixed(2)}`;
      updateCartBadge();
    } else {
      appliedCouponData = null;
      notice.className = "text-[11px] font-extrabold text-rose-600 block";
      notice.innerText = `✕ ${data.error}`;
      updateCartBadge();
    }
  })
  .catch(err => {
    notice.className = "text-[11px] font-extrabold text-rose-600 block";
    notice.innerText = "Network error — please try again.";
    console.error('Coupon error:', err);
  });
}

function updateCartBadge() {
  let count = 0;
  let subtotal = 0;

  const itemsList = document.getElementById('cartItemsList');
  let itemsHtml = '';

  Object.values(cart).forEach(item => {
    count += item.qty;
    const itemSubtotal = item.qty * item.product.price;
    subtotal += itemSubtotal;

    itemsHtml += `
      <div class="flex items-center justify-between p-3 app-card rounded-xl bg-white border border-slate-200">
        <div>
          <h5 class="text-xs font-bold text-slate-900">${item.product.name}</h5>
          <p class="text-[11px] text-slate-500 font-medium">${item.qty} &times; ₹${item.product.price.toFixed(2)} = <strong class="text-emerald-700">₹${itemSubtotal.toFixed(2)}</strong></p>
        </div>
        <div class="flex items-center space-x-1 border border-slate-200 rounded-lg text-xs font-bold bg-slate-50">
          <button onclick="updateCartQty(${item.product.id}, -1)" class="px-2.5 py-1 text-slate-700 touch-target">-</button>
          <span class="px-2 py-1">${item.qty}</span>
          <button onclick="updateCartQty(${item.product.id}, 1)" class="px-2.5 py-1 text-slate-700 touch-target">+</button>
        </div>
      </div>
    `;
  });

  document.getElementById('cartBadgeCountHeader').innerText = count;
  document.getElementById('cartBadgeCountBottom').innerText = count;
  document.getElementById('cartSubtotalText').innerText = '₹' + subtotal.toFixed(2);

  let discount = 0;
  if (appliedCouponData) {
    discount = appliedCouponData.discount_amount;
    document.getElementById('couponDiscountRow').classList.remove('hidden');
    document.getElementById('appliedCouponCodeTag').innerText = appliedCouponData.code;
    document.getElementById('cartDiscountText').innerText = '-₹' + discount.toFixed(2);
  } else {
    document.getElementById('couponDiscountRow').classList.add('hidden');
  }

  // Delivery Fee & Minimum Order Threshold Validation
  let appliedDeliveryFee = 0;
  const deliveryRow = document.getElementById('deliveryFeeRow');
  const minWarning = document.getElementById('minOrderWarning');
  const submitBtn = document.getElementById('submitOrderBtn');

  if (activeFulfillment === 'delivery' && isDeliveryEnabled) {
    appliedDeliveryFee = tenantDeliveryFee;
    if (deliveryRow) {
      deliveryRow.classList.remove('hidden');
      document.getElementById('cartDeliveryFeeText').innerText = (tenantDeliveryFee > 0) ? '₹' + tenantDeliveryFee.toFixed(2) : 'FREE';
    }

    if (subtotal > 0 && minDeliveryOrder > 0 && subtotal < minDeliveryOrder) {
      const shortage = minDeliveryOrder - subtotal;
      if (minWarning) {
        minWarning.innerText = `⚠️ Minimum order for delivery is ₹${minDeliveryOrder.toFixed(2)}. Add ₹${shortage.toFixed(2)} more to your cart, or switch to Store Pickup.`;
        minWarning.classList.remove('hidden');
      }
      if (submitBtn) submitBtn.disabled = true;
    } else {
      if (minWarning) minWarning.classList.add('hidden');
      if (submitBtn) submitBtn.disabled = false;
    }
  } else {
    if (deliveryRow) deliveryRow.classList.add('hidden');
    if (minWarning) minWarning.classList.add('hidden');
    if (submitBtn) submitBtn.disabled = false;
  }

  const grandTotal = Math.max(0, subtotal - discount + appliedDeliveryFee);
  document.getElementById('cartGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
  itemsList.innerHTML = itemsHtml || `<p class="text-xs text-slate-500 text-center py-6 font-medium">Your cart is empty.</p>`;
}

function max(a, b) { return a > b ? a : b; }

function toggleCartDrawer() {
  const drawer = document.getElementById('cartDrawer');
  const overlay = document.getElementById('cartDrawerOverlay');

  if (drawer.classList.contains('translate-x-full')) {
    drawer.classList.remove('translate-x-full');
    overlay.classList.remove('hidden');
    highlightBottomNav('bNavCart');
  } else {
    drawer.classList.add('translate-x-full');
    overlay.classList.add('hidden');
    highlightBottomNav('bNavHome');
  }
}

function submitWhatsAppOrder() {
  const phone = document.getElementById('customerPhoneInput').value.trim();
  if (!phone || phone.length < 10) {
    alert("Please enter a valid 10-digit WhatsApp phone number.");
    return;
  }

  const cartKeys = Object.keys(cart);
  if (cartKeys.length === 0) {
    alert("Your cart is empty!");
    return;
  }

  let address = '';
  if (activeFulfillment === 'delivery') {
    const addrInput = document.getElementById('customerAddressInput');
    address = addrInput ? addrInput.value.trim() : '';
    if (!address) {
      alert("Please enter your delivery address.");
      return;
    }
  }

  localStorage.setItem('customer_phone_' + tenantId, phone);
  if (address) localStorage.setItem('customer_address_' + tenantId, address);
  document.getElementById('myOrdersPhoneInput').value = phone;

  const itemsPayload = cartKeys.map(id => ({
    product_id: parseInt(id),
    quantity: cart[id].qty
  }));

  let modeLabel = (activeFulfillment === 'delivery') ? '🚚 HOME DELIVERY' : '🏬 STORE PICKUP';
  let payLabel = '💵 Cash on Delivery';
  if (activePaymentMode === 'upi') payLabel = '📱 UPI / Online Pay';
  if (activePaymentMode === 'pickup_pay') payLabel = '🏬 Pay at Store';

  let msg = `*NEW ORDER - ${shopNameStr.toUpperCase()}*\n`;
  msg += `Mode: ${modeLabel}\n`;
  msg += `Payment: ${payLabel}\n`;
  msg += `Contact: +91 ${phone}\n`;
  if (activeFulfillment === 'delivery' && address) {
    msg += `Address: ${address}\n`;
  }
  msg += `\n*ITEMS ORDERED:*\n`;

  let subtotal = 0;
  cartKeys.forEach(id => {
    const item = cart[id];
    const itemSub = item.qty * item.product.price;
    subtotal += itemSub;
    msg += `• ${item.product.name} (x${item.qty}) - ₹${itemSub.toFixed(2)}\n`;
  });

  let discount = appliedCouponData ? appliedCouponData.discount_amount : 0;
  let deliveryFeeVal = (activeFulfillment === 'delivery') ? tenantDeliveryFee : 0;
  let finalTotal = Math.max(0, subtotal - discount + deliveryFeeVal);

  if (appliedCouponData) {
    msg += `\nSubtotal: ₹${subtotal.toFixed(2)}\nDiscount (${appliedCouponData.code}): -₹${discount.toFixed(2)}\n`;
  }
  if (activeFulfillment === 'delivery' && deliveryFeeVal > 0) {
    msg += `Delivery Charge: ₹${deliveryFeeVal.toFixed(2)}\n`;
  }
  msg += `\n*TOTAL BILL: ₹${finalTotal.toFixed(2)}*\n\nSent via LocalShopOS.`;

  const waUrl = `https://wa.me/91${whatsappNo}?text=${encodeURIComponent(msg)}`;

  fetch('/api/place_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      tenant_id: tenantId,
      customer_contact: phone,
      delivery_type: activeFulfillment,
      delivery_address: address,
      delivery_contact: phone,
      payment_mode: activePaymentMode,
      coupon_code: appliedCouponData ? appliedCouponData.code : '',
      items: itemsPayload
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      localStorage.removeItem('cart_' + tenantId);
      cart = {};
      appliedCouponData = null;
      renderStorefrontGrid();
      
      const drawer = document.getElementById('cartDrawer');
      const overlay = document.getElementById('cartDrawerOverlay');
      drawer.classList.add('translate-x-full');
      overlay.classList.add('hidden');

      let customMsg = data.thank_you_msg || shopThankYouMsg;
      let confirmHtml = `Order #${data.order_id} (Total: ₹${data.total.toFixed(2)}) placed! We've sent details to ${shopNameStr} on WhatsApp.`;
      if (customMsg) confirmHtml += `<br><span class="mt-1 block text-slate-600 font-normal italic">${customMsg}</span>`;

      document.getElementById('orderConfirmationText').innerHTML = confirmHtml;
      document.getElementById('orderConfirmationBanner').classList.remove('hidden');
      window.scrollTo({ top: 0, behavior: 'smooth' });

      window.open(waUrl, '_blank');
    } else {
      alert("Error: " + data.error);
    }
  });
}

function filterCategory(cat, btn) {
  activeCategoryFilter = cat;
  document.querySelectorAll('.cat-pill').forEach(b => b.className = "cat-pill px-4 py-2 rounded-full text-xs font-bold whitespace-nowrap bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all");
  btn.className = "cat-pill active px-4 py-2 rounded-full text-xs font-black whitespace-nowrap bg-brand-500 text-slate-950 shadow-sm";
  renderStorefrontGrid();
}

function filterStorefrontProducts() {
  renderStorefrontGrid();
}

function highlightBottomNav(activeId) {
  ['bNavHome', 'bNavCategories', 'bNavCart', 'bNavOrders'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      if (id === activeId) {
        el.className = "flex flex-col items-center justify-center py-1 text-slate-900 font-black touch-target";
      } else {
        el.className = "flex flex-col items-center justify-center py-1 text-slate-500 font-bold touch-target";
      }
    }
  });
}

function navGoHome() {
  highlightBottomNav('bNavHome');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function navGoCategories() {
  highlightBottomNav('bNavCategories');
  const catSection = document.getElementById('categoriesBarSection');
  if (catSection) {
    catSection.scrollIntoView({ behavior: 'smooth' });
    document.getElementById('storeSearchInput').focus();
  }
}

function openCustomerOrdersModal() {
  highlightBottomNav('bNavOrders');
  document.getElementById('customerOrdersModal').classList.remove('hidden');
  const phone = document.getElementById('myOrdersPhoneInput').value.trim();
  if (phone) {
    fetchCustomerOrders();
  }
}

function repeatPastOrder(itemsJsonStr) {
  try {
    const items = JSON.parse(itemsJsonStr);
    cart = {};
    items.forEach(item => {
      const p = productsData.find(prod => prod.id === item.product_id);
      if (p) {
        cart[p.id] = { product: p, qty: item.quantity };
      }
    });
    localStorage.setItem('cart_' + tenantId, JSON.stringify(cart));
    document.getElementById('customerOrdersModal').classList.add('hidden');
    renderStorefrontGrid();
    toggleCartDrawer();
  } catch (e) {
    alert("Unable to reorder items.");
  }
}

function fetchCustomerOrders() {
  const phone = document.getElementById('myOrdersPhoneInput').value.trim();
  const listEl = document.getElementById('myOrdersList');

  if (!phone || phone.length < 10) {
    listEl.innerHTML = `<div class="text-center py-6 text-xs text-rose-600 font-bold">Please enter a valid 10-digit phone number.</div>`;
    return;
  }

  listEl.innerHTML = `<div class="text-center py-6 text-xs text-slate-500 font-bold">Fetching your orders...</div>`;

  fetch(`/api/customer_orders.php?tenant_id=${tenantId}&phone=${encodeURIComponent(phone)}`)
    .then(res => res.json())
    .then(data => {
      if (!data.success || !data.orders || data.orders.length === 0) {
        listEl.innerHTML = `<div class="text-center py-8"><div class="text-3xl mb-1">📦</div><p class="text-xs text-slate-500 font-bold">No previous orders found for +91 ${phone}</p></div>`;
        return;
      }

      let html = '';
      data.orders.forEach(ord => {
        let itemsSummary = ord.items.map(i => `${i.product_name} (x${i.quantity})`).join(', ');

        // Visual Timeline Progression
        const statuses = ['new', 'accepted', 'preparing', 'completed'];
        const currentIdx = statuses.indexOf(ord.status);
        
        let timelineHtml = `
          <div class="grid grid-cols-4 gap-1 py-2 my-2 text-[9px] font-black uppercase text-center border-y border-slate-200/80">
            <div class="${currentIdx >= 0 ? 'text-amber-700 font-black' : 'text-slate-300'}">1. Placed</div>
            <div class="${currentIdx >= 1 ? 'text-blue-700 font-black' : 'text-slate-300'}">2. Accepted</div>
            <div class="${currentIdx >= 2 ? 'text-purple-700 font-black' : 'text-slate-300'}">3. Preparing</div>
            <div class="${currentIdx >= 3 ? 'text-emerald-700 font-black' : 'text-slate-300'}">4. Ready</div>
          </div>
        `;

        const itemsEscaped = JSON.stringify(ord.items).replace(/"/g, '&quot;');

        html += `
          <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 text-xs space-y-2">
            <div class="flex items-center justify-between">
              <span class="font-mono font-black text-amber-900">Order #${ord.id}</span>
              <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase ${
                ord.status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'
              }">${ord.status}</span>
            </div>
            
            ${timelineHtml}

            <p class="text-slate-800 font-bold leading-snug">${itemsSummary}</p>
            
            <div class="flex items-center justify-between pt-2 text-[11px] border-t border-slate-200">
              <span class="text-slate-500 font-medium">${ord.formatted_date}</span>
              <div class="flex items-center space-x-2">
                <span class="font-black text-emerald-700 text-sm">₹${parseFloat(ord.total).toFixed(2)}</span>
                <button onclick="repeatPastOrder('${itemsEscaped}')" class="px-3 py-1 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-[10px] rounded-lg shadow-sm">
                  🔄 Order Again
                </button>
              </div>
            </div>
          </div>
        `;
      });
      listEl.innerHTML = html;
    })
    .catch(() => {
      listEl.innerHTML = `<div class="text-center py-6 text-xs text-rose-600 font-bold">Failed to load orders. Try again.</div>`;
    });
}

document.addEventListener('DOMContentLoaded', () => {
  renderStorefrontGrid();
});
</script>

</body>
</html>
