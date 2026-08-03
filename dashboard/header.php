<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Vary: *");
ob_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_tenant_auth();

$tenantId  = get_logged_tenant_id();
$shopName  = $_SESSION['shop_name'] ?? 'My Shop';
$subdomain = $_SESSION['subdomain'] ?? '';
$pageTitle = $pageTitle ?? 'Merchant Dashboard - LocalShopOS';

$pdo = getDBConnection();

// Fetch Full Tenant Details including Plan Status & Trial Expiration
$tenantCheckStmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id WHERE t.id = ?");
$tenantCheckStmt->execute([$tenantId]);
$tenantInfo = $tenantCheckStmt->fetch();

$isOpen     = $tenantInfo ? (int)$tenantInfo['is_open'] : 1;
$planStatus = $tenantInfo ? $tenantInfo['plan_status'] : 'active';
$planName   = $tenantInfo && !empty($tenantInfo['plan_name']) ? $tenantInfo['plan_name'] : 'Starter Free';

// Feature access checks for nav gating
$canAds        = tenant_has_feature($pdo, $tenantInfo, 'shop_ads');
$canReports    = tenant_has_feature($pdo, $tenantInfo, 'sales_reports');
$canLogoUpload = tenant_has_feature($pdo, $tenantInfo, 'shop_logo_upload');
$canQrCode     = tenant_has_feature($pdo, $tenantInfo, 'qr_code_generator');
$canCoupons    = tenant_has_feature($pdo, $tenantInfo, 'coupons');
$inTrial       = is_tenant_in_trial($tenantInfo);

// Calculate trial days remaining if in trial
$trialDaysLeft = 0;
if ($inTrial && !empty($tenantInfo['trial_ends_at'])) {
    $diffSecs = strtotime($tenantInfo['trial_ends_at']) - time();
    $trialDaysLeft = max(0, ceil($diffSecs / 86400));
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full font-sans antialiased bg-[#FFFDF7] text-slate-900">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/logo.png">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                50: '#fefce8',
                100: '#fef9c3',
                200: '#fef08a',
                300: '#fde047',
                400: '#facc15',
                500: '#f5b400',
                600: '#d97f00',
                700: '#b45309',
                800: '#92400e',
              }
            },
            fontFamily: {
              sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
            }
          }
        }
      }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
      body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF7; color: #0F172A; overflow-x: hidden; }
      .app-card { background-color: #FFFFFF; border: 1px solid #F1F5F9; box-shadow: 0 2px 12px -2px rgba(245, 180, 0, 0.08); border-radius: 1rem; }
      .app-nav { background-color: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border-bottom: 1px solid #FEF08A; }
      .btn-cta { background-color: #D97F00; color: #FFFFFF; font-weight: 800; }
      .btn-cta:hover { background-color: #B45309; }
      .touch-target { min-height: 44px; min-width: 44px; }
    </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-[#FFFDF7] text-slate-900 overflow-x-hidden">

<!-- Admin Action Toast Container (Fix 4: AJAX Toast Notifications) -->
<div id="adminToastContainer" class="fixed top-5 right-5 z-50 space-y-2 pointer-events-none"></div>

<script>
function showAdminToast(message, isError = false) {
  const container = document.getElementById('adminToastContainer');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `pointer-events-auto flex items-center space-x-3 px-4 py-3 rounded-2xl shadow-2xl text-xs font-black transition-all transform translate-y-[-10px] ${
    isError ? 'bg-rose-900 text-white border border-rose-700' : 'bg-slate-900 text-white border border-slate-700'
  }`;
  
  toast.innerHTML = `
    <span class="${isError ? 'text-rose-400' : 'text-emerald-400'} text-base font-bold">${isError ? '⚠️' : '✓'}</span>
    <span>${message}</span>
  `;

  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function toggleTenantMobileDrawer() {
  const drawer = document.getElementById('tenantMobileDrawer');
  const backdrop = document.getElementById('tenantMobileBackdrop');
  if (drawer.classList.contains('hidden')) {
    drawer.classList.remove('hidden');
    backdrop.classList.remove('hidden');
  } else {
    drawer.classList.add('hidden');
    backdrop.classList.add('hidden');
  }
}
</script>

<!-- Dashboard Header -->
<header class="sticky top-0 z-40 app-nav shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      
      <!-- Brand & Store Name -->
      <div class="flex items-center space-x-3">
        <!-- Mobile Hamburger Menu Button -->
        <button onclick="toggleTenantMobileDrawer()" class="md:hidden p-2 rounded-xl text-slate-800 hover:bg-brand-100 border border-slate-200 focus:outline-none touch-target flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>

        <a href="/dashboard/index.php" class="flex items-center space-x-2.5">
          <img src="/assets/logo.png" alt="LocalShopOS Logo" class="w-8 h-8 sm:w-9 sm:h-9 object-contain rounded-lg shrink-0">
          <div class="truncate max-w-[130px] sm:max-w-xs">
            <h1 class="font-black text-xs sm:text-sm text-slate-900 leading-tight flex items-center gap-1.5 truncate">
              <span class="truncate"><?= htmlspecialchars($shopName) ?></span>
              <?php if (!$isOpen): ?>
                <span class="px-1.5 py-0.5 text-[9px] font-black bg-rose-100 text-rose-800 rounded-full shrink-0">CLOSED</span>
              <?php endif; ?>
            </h1>
            <span class="text-[10px] sm:text-[11px] text-amber-800 font-bold truncate block">/<?= htmlspecialchars($subdomain) ?></span>
          </div>
        </a>
      </div>

      <!-- Navigation Links (Desktop) -->
      <nav class="hidden md:flex items-center space-x-1 font-extrabold text-xs">
        <a href="/dashboard/index.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Overview
        </a>
        <a href="/dashboard/products.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Products
        </a>
        <a href="/dashboard/orders.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Orders
        </a>
        <a href="/dashboard/ads.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all flex items-center gap-1">
          <span>Ads</span>
          <?php if (!$canAds): ?>
            <span class="text-[10px]" title="Feature Locked">🔒</span>
          <?php endif; ?>
        </a>
        <a href="/dashboard/coupons.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all flex items-center gap-1">
          <span>Coupons</span>
          <?php if (!$canCoupons): ?>
            <span class="text-[10px]" title="Feature Locked">🔒</span>
          <?php endif; ?>
        </a>
        <a href="/dashboard/sales.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all flex items-center gap-1">
          <span>Sales</span>
          <?php if (!$canReports): ?>
            <span class="text-[10px]" title="Feature Locked">🔒</span>
          <?php endif; ?>
        </a>
        <a href="/dashboard/share.php" class="px-3 py-2 rounded-xl text-amber-900 bg-brand-100 hover:bg-brand-200 border border-brand-300 font-black transition-all flex items-center gap-1">
          <span>📱 QR Code</span>
          <?php if (!$canQrCode): ?>
            <span class="text-[10px]" title="Feature Locked">🔒</span>
          <?php endif; ?>
        </a>
        <a href="/dashboard/settings.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Settings
        </a>
      </nav>

      <!-- Plan Indicator Badge & Store Actions -->
      <div class="flex items-center space-x-2">
        <a href="/dashboard/plans.php" class="px-2.5 py-1.5 bg-brand-100 hover:bg-brand-200 border border-brand-300 text-amber-950 rounded-xl text-[11px] sm:text-xs font-black transition-all flex items-center space-x-1 shadow-sm">
          <span>👑 <?= htmlspecialchars($planName) ?></span>
          <?php if ($inTrial): ?>
            <span class="px-1.5 py-0.5 rounded bg-amber-500 text-slate-950 text-[9px] font-black uppercase">
              <?= $trialDaysLeft ?>d
            </span>
          <?php endif; ?>
        </a>

        <a href="/<?= urlencode($subdomain) ?>" target="_blank" 
           class="hidden sm:flex px-3 py-2 text-xs font-black text-slate-900 bg-brand-200 hover:bg-brand-300 border border-brand-400 rounded-xl transition-all items-center space-x-1 touch-target">
          <span>Storefront</span>
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
        </a>

        <a href="/logout.php" class="hidden sm:block px-3 py-2 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
          Logout
        </a>
      </div>

    </div>
  </div>
</header>

<!-- Collapsible Mobile Menu Drawer (Slide-Over) -->
<div id="tenantMobileBackdrop" onclick="toggleTenantMobileDrawer()" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm transition-opacity md:hidden"></div>

<aside id="tenantMobileDrawer" class="hidden fixed top-0 left-0 bottom-0 z-50 w-72 bg-white border-r border-brand-300 shadow-2xl flex flex-col justify-between p-5 md:hidden transition-transform duration-300">
  
  <div class="space-y-6">
    
    <!-- Drawer Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div class="flex items-center space-x-2.5">
        <img src="/assets/logo.png" alt="LocalShopOS Logo" class="w-8 h-8 object-contain">
        <div>
          <h3 class="font-black text-sm text-slate-900"><?= htmlspecialchars($shopName) ?></h3>
          <span class="text-[10px] text-amber-800 font-bold block">Merchant Console</span>
        </div>
      </div>
      <button onclick="toggleTenantMobileDrawer()" class="p-2 text-slate-500 hover:text-slate-900 rounded-xl">
        ✕
      </button>
    </div>

    <!-- Mobile Navigation Drawer Links -->
    <nav class="space-y-1 text-sm font-extrabold">
      <a href="/dashboard/index.php" onclick="toggleTenantMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>📊</span> <span>Overview</span>
      </a>
      <a href="/dashboard/products.php" onclick="toggleTenantMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>📦</span> <span>Products</span>
      </a>
      <a href="/dashboard/orders.php" onclick="toggleTenantMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>🛒</span> <span>Orders Inbox</span>
      </a>
      <a href="/dashboard/share.php" onclick="toggleTenantMobileDrawer()" class="flex items-center justify-between px-3 py-3 rounded-2xl bg-brand-100 text-amber-950 font-black border border-brand-300">
        <span class="flex items-center space-x-3"><span>📱</span> <span>Share & QR Code</span></span>
        <?php if (!$canQrCode): ?><span class="text-xs">🔒</span><?php endif; ?>
      </a>
      <a href="/dashboard/ads.php" onclick="toggleTenantMobileDrawer()" class="flex items-center justify-between px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span class="flex items-center space-x-3"><span>📢</span> <span>Marketing Ads</span></span>
        <?php if (!$canAds): ?><span class="text-xs">🔒</span><?php endif; ?>
      </a>
      <a href="/dashboard/coupons.php" onclick="toggleTenantMobileDrawer()" class="flex items-center justify-between px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span class="flex items-center space-x-3"><span>🎟️</span> <span>Discount Coupons</span></span>
        <?php if (!$canCoupons): ?><span class="text-xs">🔒</span><?php endif; ?>
      </a>
      <a href="/dashboard/sales.php" onclick="toggleTenantMobileDrawer()" class="flex items-center justify-between px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span class="flex items-center space-x-3"><span>📈</span> <span>Sales Reports</span></span>
        <?php if (!$canReports): ?><span class="text-xs">🔒</span><?php endif; ?>
      </a>
      <a href="/dashboard/settings.php" onclick="toggleTenantMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>⚙️</span> <span>Shop Settings</span>
      </a>
      <a href="/dashboard/plans.php" onclick="toggleTenantMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-amber-900 bg-brand-50 hover:bg-brand-100 font-black">
        <span>👑</span> <span>Subscription Plans</span>
      </a>
    </nav>

  </div>

  <!-- Drawer Footer Actions -->
  <div class="border-t border-slate-100 pt-4 space-y-2">
    <a href="/<?= urlencode($subdomain) ?>" target="_blank" class="w-full py-3 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-xs rounded-xl flex items-center justify-center space-x-2 shadow">
      <span>View Live Storefront &rarr;</span>
    </a>
    <a href="/logout.php" class="w-full py-2.5 text-center block text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl">
      Log Out of Dashboard
    </a>
  </div>

</aside>

<?php if ($planStatus === 'suspended'): ?>
  <div class="bg-rose-600 text-white text-center py-2 px-4 text-xs font-extrabold">
    ⚠️ Warning: Your shop account is currently SUSPENDED by Super Admin.
  </div>
<?php endif; ?>

<?php display_flash(); ?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full">
