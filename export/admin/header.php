<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
ob_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_super_admin_auth();

$pageTitle = $pageTitle ?? 'Super Admin Console — LocalShopOS';
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FFFDF7] font-sans antialiased text-slate-900">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/assets/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: { brand: { 50: '#fefce8', 100: '#fef9c3', 500: '#f5b400', 600: '#d97f00' } }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF7; color: #0F172A; overflow-x: hidden; }
    .app-card { background-color: #FFFFFF; border: 1px solid #F1F5F9; box-shadow: 0 2px 12px -2px rgba(245, 180, 0, 0.08); border-radius: 1.5rem; }
    .app-nav { background-color: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border-bottom: 1px solid #FEF08A; }
    .touch-target { min-height: 44px; min-width: 44px; }
  </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-[#FFFDF7] text-slate-900 overflow-x-hidden">

<!-- Admin Action Toast Container -->
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

function toggleAdminMobileDrawer() {
  const drawer = document.getElementById('adminMobileDrawer');
  const backdrop = document.getElementById('adminMobileBackdrop');
  if (drawer.classList.contains('hidden')) {
    drawer.classList.remove('hidden');
    backdrop.classList.remove('hidden');
  } else {
    drawer.classList.add('hidden');
    backdrop.classList.add('hidden');
  }
}
</script>

<!-- Super Admin Top Navigation -->
<header class="sticky top-0 z-40 app-nav shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      
      <!-- Brand & Mobile Hamburger Button -->
      <div class="flex items-center space-x-3">
        <button onclick="toggleAdminMobileDrawer()" class="lg:hidden p-2 rounded-xl text-slate-800 hover:bg-brand-100 border border-slate-200 focus:outline-none touch-target flex items-center justify-center">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>

        <a href="/admin/index.php" class="flex items-center space-x-2.5">
          <img src="/assets/logo.png" alt="LocalShopOS Logo" class="w-8 h-8 object-contain rounded-lg shrink-0">
          <div>
            <h1 class="font-black text-xs sm:text-sm text-slate-900 leading-tight">Super Admin Portal</h1>
            <span class="text-[10px] text-amber-800 font-bold block sm:inline">Platform Management</span>
          </div>
        </a>
      </div>

      <!-- Navigation Links (Desktop) -->
      <nav class="hidden lg:flex items-center space-x-1 text-xs font-extrabold">
        <a href="/admin/index.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Overview
        </a>
        <a href="/admin/plans.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Plans Builder
        </a>
        <a href="/admin/global_ads.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Global Ads
        </a>
        <a href="/admin/users.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          User Directory
        </a>
        <a href="/admin/team.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Admin Team
        </a>
        <a href="/admin/settings.php" class="px-3 py-2 rounded-xl text-slate-700 hover:text-slate-900 hover:bg-brand-100 transition-all">
          Settings
        </a>
      </nav>

      <!-- Desktop Action CTAs -->
      <div class="flex items-center space-x-2">
        <a href="/shops.php" target="_blank" class="hidden sm:flex px-3 py-1.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all items-center space-x-1">
          <span>View Directory &rarr;</span>
        </a>
        <a href="/admin/logout.php" class="px-3 py-1.5 text-xs font-black bg-slate-900 text-white hover:bg-slate-800 rounded-xl transition-all shadow-sm">
          Logout
        </a>
      </div>

    </div>
  </div>
</header>

<!-- Collapsible Mobile Drawer Overlay for Super Admin -->
<div id="adminMobileBackdrop" onclick="toggleAdminMobileDrawer()" class="hidden fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm transition-opacity lg:hidden"></div>

<aside id="adminMobileDrawer" class="hidden fixed top-0 left-0 bottom-0 z-50 w-72 bg-white border-r border-brand-300 shadow-2xl flex flex-col justify-between p-5 lg:hidden transition-transform duration-300">
  
  <div class="space-y-6">
    
    <!-- Drawer Header -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-4">
      <div class="flex items-center space-x-2.5">
        <img src="/assets/logo.png" alt="LocalShopOS Logo" class="w-8 h-8 object-contain">
        <div>
          <h3 class="font-black text-sm text-slate-900">Super Admin</h3>
          <span class="text-[10px] text-amber-800 font-bold block">Master Console</span>
        </div>
      </div>
      <button onclick="toggleAdminMobileDrawer()" class="p-2 text-slate-500 hover:text-slate-900 rounded-xl">
        ✕
      </button>
    </div>

    <!-- Mobile Drawer Links -->
    <nav class="space-y-1 text-sm font-extrabold">
      <a href="/admin/index.php" onclick="toggleAdminMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>📊</span> <span>Executive Overview</span>
      </a>
      <a href="/admin/plans.php" onclick="toggleAdminMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>📋</span> <span>Plans & Feature Registry</span>
      </a>
      <a href="/admin/global_ads.php" onclick="toggleAdminMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>📢</span> <span>Global Ad Campaigns</span>
      </a>
      <a href="/admin/users.php" onclick="toggleAdminMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>👥</span> <span>User & Tenant Directory</span>
      </a>
      <a href="/admin/team.php" onclick="toggleAdminMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>🛡️</span> <span>Admin Team & Staff</span>
      </a>
      <a href="/admin/settings.php" onclick="toggleAdminMobileDrawer()" class="flex items-center space-x-3 px-3 py-3 rounded-2xl text-slate-800 hover:bg-brand-100">
        <span>⚙️</span> <span>Platform Branding</span>
      </a>
    </nav>

  </div>

  <!-- Drawer Footer Actions -->
  <div class="border-t border-slate-100 pt-4 space-y-2">
    <a href="/shops.php" target="_blank" class="w-full py-3 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-xs rounded-xl flex items-center justify-center space-x-2 shadow">
      <span>View Live Storefront Directory &rarr;</span>
    </a>
    <a href="/admin/logout.php" class="w-full py-2.5 text-center block text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl">
      Log Out Admin
    </a>
  </div>

</aside>

<?php display_flash(); ?>
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full">
