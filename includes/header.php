<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

$pdo = getDBConnection();

// Fetch Global Platform Settings for dynamic header/footer branding
$platformSettings = [];
try {
    $platformSettings = $pdo->query("SELECT * FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {}

$siteName       = $platformSettings['site_name'] ?? 'LocalShopOS';
$supportContact = $platformSettings['support_contact_number'] ?? '+917676446647';
$siteLogo       = !empty($platformSettings['site_logo_url']) ? $platformSettings['site_logo_url'] : '/assets/logo.png';
$primaryColor   = $platformSettings['primary_color'] ?? '#f5b400';
$accentColor    = $platformSettings['accent_color'] ?? '#f5b400';

$pageTitle       = $pageTitle ?? "{$siteName} — Zero-Commission WhatsApp Store Operating System";
$metaDescription = $metaDescription ?? "Turn your local shop into a mobile-first digital storefront in 5 minutes. Direct WhatsApp orders, zero commission fees, and powerful merchant marketing.";
$ogTitle         = $ogTitle ?? $pageTitle;
$ogDescription   = $ogDescription ?? $metaDescription;
$ogImage         = $ogImage ?? 'https://localshopos.com/assets/og-image.png';
$ogUrl           = $ogUrl ?? 'https://localshopos.com/';
?>
<!DOCTYPE html>
<html lang="en" class="h-full font-sans antialiased scroll-smooth selection:bg-brand-500 selection:text-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    
    <!-- Open Graph SEO -->
    <meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">
    <meta property="og:url" content="<?= htmlspecialchars($ogUrl) ?>">
    <meta property="og:type" content="website">
    
    <!-- Icons -->
    <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($siteLogo) ?>">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($siteLogo) ?>">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            colors: {
              brand: {
                50: '#FFFDF0',
                100: '#FEF9C3',
                200: '#FEF08A',
                300: '#FDE047',
                400: '#FACC15',
                500: '#F5B400', // Signature Bold Gold
                600: '#D97F00',
                700: '#B45309',
                800: '#92400E',
                900: '#78350F',
                950: '#451A03',
              },
              dark: {
                950: '#070A11',
                900: '#0F172A',
                800: '#1E293B',
                700: '#334155'
              }
            },
            fontFamily: {
              sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
            }
          }
        }
      }
    </script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
      /* Brand Color Fail-Safe Classes */
      .text-brand-400 { color: #FACC15 !important; }
      .text-brand-300 { color: #FDE047 !important; }
      .bg-brand-500 { background-color: #F5B400 !important; }
      .border-brand-500 { border-color: #F5B400 !important; }

      /* Global Theme Base */
      html, body { 
        font-family: 'Plus Jakarta Sans', sans-serif; 
        background-color: #070A11;
        color: #F8FAFC;
        overflow-x: hidden;
      }

      /* Desktop Custom Cursor */
      @media (pointer: fine) {
        body { cursor: default; }
        #customCursorDot {
          width: 8px;
          height: 8px;
          background-color: #F5B400;
          position: fixed;
          top: 0; left: 0;
          border-radius: 50%;
          pointer-events: none;
          z-index: 9999;
          transform: translate(-50%, -50%);
          transition: transform 0.05s ease-out;
        }
        #customCursorRing {
          width: 36px;
          height: 36px;
          border: 1.5px solid rgba(245, 180, 0, 0.5);
          position: fixed;
          top: 0; left: 0;
          border-radius: 50%;
          pointer-events: none;
          z-index: 9998;
          transform: translate(-50%, -50%);
          transition: transform 0.15s ease-out, width 0.2s, height 0.2s, background-color 0.2s, border-color 0.2s;
        }
        body.cursor-hover #customCursorRing {
          width: 56px;
          height: 56px;
          background-color: rgba(245, 180, 0, 0.15);
          border-color: #F5B400;
        }
      }
      @media (pointer: coarse) {
        #customCursorDot, #customCursorRing { display: none !important; }
      }

      /* Floating & Pulsing Animations */
      @keyframes floatSlow {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
      }
      .animate-float-slow {
        animation: floatSlow 4s ease-in-out infinite;
      }

      @keyframes glowPulse {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 0.5; transform: scale(1.05); }
      }
      .animate-glow-mesh {
        animation: glowPulse 6s ease-in-out infinite;
      }

      /* Navbar Backdrop */
      .app-nav {
        background-color: rgba(7, 10, 17, 0.92);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        transition: all 0.3s ease;
      }
      .app-nav.scrolled {
        background-color: rgba(7, 10, 17, 0.98);
        border-bottom-color: rgba(245, 180, 0, 0.4);
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.9);
      }

      /* Glassmorphic Bento Cards */
      .bento-card {
        background: rgba(22, 31, 51, 0.9);
        backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.12);
        border-radius: 1.5rem;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      }
      .bento-card:hover {
        border-color: rgba(245, 180, 0, 0.5);
        box-shadow: 0 15px 35px -10px rgba(245, 180, 0, 0.25);
        transform: translateY(-4px);
      }

      /* Gold Glow Action Buttons */
      .btn-gold-action {
        background: linear-gradient(135deg, #F5B400 0%, #D97F00 100%);
        color: #070A11;
        font-weight: 900;
        box-shadow: 0 4px 25px -2px rgba(245, 180, 0, 0.5);
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
      }
      .btn-gold-action:hover {
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 8px 35px 0px rgba(245, 180, 0, 0.75);
        filter: brightness(1.08);
      }

      .btn-glass-secondary {
        background: rgba(255, 255, 255, 0.08);
        color: #FFFFFF;
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-weight: 800;
        transition: all 0.2s ease;
      }
      .btn-glass-secondary:hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: #F5B400;
        color: #F5B400;
      }

      /* Smooth Scroll Reveal (Graceful fallback) */
      .reveal-on-scroll {
        opacity: 1;
        transform: translateY(0);
        transition: opacity 0.6s ease-out, transform 0.6s ease-out;
      }
      .js-reveal .reveal-on-scroll {
        opacity: 0;
        transform: translateY(24px);
      }
      .js-reveal .reveal-on-scroll.is-visible {
        opacity: 1;
        transform: translateY(0);
      }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-dark-950 text-slate-100 selection:bg-brand-500 selection:text-slate-950">

<!-- Custom Cursor (Desktop) -->
<div id="customCursorDot"></div>
<div id="customCursorRing"></div>

<!-- Header Navbar -->
<header id="mainHeader" class="sticky top-0 z-50 app-nav text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-20">
      
      <!-- Brand Logo -->
      <a href="/" class="flex items-center space-x-3 group shrink-0">
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($siteName) ?> Logo" class="h-9 sm:h-11 w-auto object-contain rounded-xl group-hover:scale-105 transition-transform shadow-md">
        <span class="font-black text-xl sm:text-2xl tracking-tight text-white">
          <?= htmlspecialchars($siteName) ?><span class="text-brand-500" style="color: #FACC15 !important;">.</span>
        </span>
      </a>

      <!-- Desktop Links -->
      <nav class="hidden md:flex items-center space-x-8 text-sm font-bold text-slate-200">
        <a href="#how-it-works" class="hover:text-brand-400 transition-colors">How It Works</a>
        <a href="#features" class="hover:text-brand-400 transition-colors">Features</a>
        <a href="#pricing" class="hover:text-brand-400 transition-colors">Pricing</a>
        <a href="#about" class="hover:text-brand-400 transition-colors">Why 0% Fee</a>
        <a href="/shops.php" class="hover:text-brand-400 transition-colors flex items-center gap-1.5 text-brand-400 font-extrabold">
          <span>Live Stores</span>
          <span class="px-2 py-0.5 text-[10px] bg-brand-500/20 text-brand-300 border border-brand-500/40 rounded-full font-black">Directory</span>
        </a>
      </nav>

      <!-- Desktop Action CTAs -->
      <div class="hidden sm:flex items-center space-x-3">
        <?php if (is_tenant_logged_in()): ?>
          <a href="/dashboard/index.php" class="px-5 py-2.5 text-xs font-black rounded-xl bg-brand-500 text-slate-950 hover:bg-brand-400 transition-all shadow-md">
            <span>My Store Dashboard &rarr;</span>
          </a>
        <?php elseif (is_super_admin_logged_in()): ?>
          <a href="/admin/index.php" class="px-5 py-2.5 text-xs font-black rounded-xl bg-brand-500 text-slate-950 transition-all shadow-md">
            <span>Super Admin &rarr;</span>
          </a>
        <?php else: ?>
          <a href="/login.php" class="px-4 py-2 text-xs font-bold text-slate-200 hover:text-white transition-colors">
            Log In
          </a>
          <a href="/signup.php" class="px-5 py-2.5 text-xs btn-gold-action rounded-xl flex items-center space-x-1">
            <span>Start Free Trial</span>
          </a>
        <?php endif; ?>
      </div>

      <!-- Mobile Hamburger Button -->
      <div class="md:hidden flex items-center">
        <button onclick="toggleMobileNav()" class="p-2.5 rounded-xl text-slate-100 bg-white/10 border border-white/20 focus:outline-none">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>

    </div>
  </div>

  <!-- Mobile Menu Drawer -->
  <div id="mobileMenuDrawer" class="hidden md:hidden border-b border-white/10 bg-dark-950/98 backdrop-blur-2xl px-5 pt-4 pb-6 space-y-4">
    <a href="#how-it-works" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-slate-200 hover:text-brand-400">How It Works</a>
    <a href="#features" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-slate-200 hover:text-brand-400">Features</a>
    <a href="#pricing" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-slate-200 hover:text-brand-400">Pricing</a>
    <a href="#about" onclick="toggleMobileNav()" class="block py-2 text-sm font-bold text-slate-200 hover:text-brand-400">Why 0% Fee</a>
    <a href="/shops.php" class="block py-2 text-sm font-black text-brand-400">🏪 Merchant Directory</a>
    
    <div class="pt-3 border-t border-white/10 flex flex-col gap-2.5">
      <?php if (is_tenant_logged_in()): ?>
        <a href="/dashboard/index.php" class="py-3 text-center text-xs font-black bg-brand-500 text-slate-950 rounded-xl">Go to Dashboard</a>
      <?php else: ?>
        <a href="/login.php" class="py-3 text-center text-xs font-bold bg-white/10 text-white rounded-xl">Log In</a>
        <a href="/signup.php" class="py-3 text-center text-xs font-black btn-gold-action rounded-xl">Start Free Trial</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<?php display_flash(); ?>
