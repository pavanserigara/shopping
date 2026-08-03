<?php
$pageTitle = "LocalShopOS — Premium WhatsApp Storefront OS for Local Merchants";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$pdo = getDBConnection();

// Fetch Dynamic Subscription Plans from DB
$dbPlans = [];
try {
    $stmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
    $rawPlans = $stmt->fetchAll();

    foreach ($rawPlans as $p) {
        $featStmt = $pdo->prepare("SELECT feature_key FROM plan_features WHERE plan_id = ?");
        $featStmt->execute([$p['id']]);
        $features = $featStmt->fetchAll(PDO::FETCH_COLUMN);

        $dbPlans[] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'price' => (float)$p['price'],
            'billing_period' => $p['billing_period'],
            'product_limit' => (int)$p['product_limit'],
            'is_default' => (bool)$p['is_default'],
            'features' => $features
        ];
    }
} catch (Exception $e) {
    error_log("Landing Page Plans Query Error: " . $e->getMessage());
}

// Feature titles mapping (must stay in sync with FEATURE_REGISTRY in includes/features.php)
$featureLabels = [
    'product_management'     => 'Product Catalog & Stock Counter',
    'order_management'       => 'WhatsApp Order Cart & Checkout',
    'product_image_gallery'  => 'Multi-Photo Product Gallery',
    'sales_reports'          => 'Sales Analytics & Revenue Reports',
    'shop_ads'               => 'In-Store Promotional Banners',
    'ad_analytics'           => 'Ad Impression Analytics',
    'shop_logo_upload'       => 'Custom Shop Logo Branding',
    'shop_directory_listing' => 'Public Merchant Directory Listing',
    'qr_code_generator'      => 'Branded QR Code Studio (Print-Ready)',
    'coupons'                => 'Discount Coupons & Promo Codes',
];

// Fetch platform WhatsApp contact number for CTA buttons & contact section
try {
    $waRow = $pdo->query("SELECT whatsapp_contact FROM platform_settings WHERE id = 1")->fetchColumn();
    $whatsappContact = !empty($waRow) ? trim($waRow) : '';
} catch (Exception $e) {
    $whatsappContact = '';
}
$waCtaLink = $whatsappContact
    ? "https://wa.me/{$whatsappContact}?text=" . urlencode("Hi! I want to get started with LocalShopOS for my shop.")
    : "/signup.php";

require_once __DIR__ . '/includes/header.php';
?>

<!-- JSON-LD SEO Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "<?= htmlspecialchars($siteName) ?>",
  "operatingSystem": "Web",
  "applicationCategory": "BusinessApplication",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "INR"
  },
  "description": "The simple, zero-commission operating system for local shopkeepers to take their stores online and accept instant structured orders on WhatsApp."
}
</script>

<main class="flex-1 w-full bg-[#070A11]">

  <!-- ==========================================
       1. HERO SECTION
       ========================================== -->
  <section class="relative bg-[#070A11] text-white pt-12 pb-20 sm:pt-20 sm:pb-28 overflow-hidden border-b border-white/10">
    
    <!-- Background Glow Orb -->
    <div class="absolute top-1/4 left-1/3 -translate-x-1/2 -translate-y-1/2 w-[600px] sm:w-[850px] h-[350px] sm:h-[500px] bg-brand-500/10 rounded-full blur-[140px] pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
        
        <!-- Hero Copy Column -->
        <div class="lg:col-span-7 space-y-6 text-left reveal-on-scroll">
          
          <div class="inline-flex items-center space-x-2 px-4 py-2 rounded-full bg-brand-500/10 border border-brand-500/40 text-brand-400 text-xs font-black tracking-wide">
            <span class="w-2.5 h-2.5 rounded-full bg-brand-400 animate-pulse"></span>
            <span>⚡ Built for Indian Kirana, Bakeries & Retail Stores</span>
          </div>

          <!-- Hero Headline: White with Solid Yellow Ending Word -->
          <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.15]">
            Turn Your Local Shop Into A <span class="text-brand-400" style="color: #FACC15 !important;">WhatsApp Storefront</span>
          </h1>

          <p class="text-base sm:text-xl text-slate-200 leading-relaxed font-semibold max-w-2xl">
            Zero platform commission. 5-minute setup. Allow your customers to browse items online and send pre-formatted order lists directly to your WhatsApp phone.
          </p>

          <!-- Hero Action CTAs -->
          <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4 pt-2">
            <a href="/signup.php" class="px-8 py-4 btn-gold-action text-slate-950 font-black text-base rounded-2xl text-center flex items-center justify-center space-x-2 group">
              <span>Start 14-Day Free Trial</span>
              <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>

            <a href="/shops.php" class="px-7 py-4 btn-glass-secondary rounded-2xl text-center text-sm flex items-center justify-center space-x-2 text-white">
              <span>Browse Live Shops 🏪</span>
            </a>
          </div>

          <!-- Metric Badges -->
          <div class="pt-8 border-t border-white/15 grid grid-cols-3 gap-6 text-left">
            <div>
              <div class="text-3xl sm:text-4xl font-black text-white">0%</div>
              <div class="text-xs font-extrabold text-slate-300 uppercase tracking-wider mt-1">Commission Fee</div>
            </div>
            <div>
              <div class="text-3xl sm:text-4xl font-black text-brand-400" style="color: #FACC15 !important;">5 Mins</div>
              <div class="text-xs font-extrabold text-slate-300 uppercase tracking-wider mt-1">Setup Time</div>
            </div>
            <div>
              <div class="text-3xl sm:text-4xl font-black text-white">100%</div>
              <div class="text-xs font-extrabold text-slate-300 uppercase tracking-wider mt-1">Direct Profits</div>
            </div>
          </div>

        </div>

        <!-- Hero Visual: Bright High-Contrast Phone Mockup -->
        <div class="lg:col-span-5 relative reveal-on-scroll">
          <div class="relative mx-auto max-w-sm sm:max-w-md">
            
            <!-- Outer Phone Container -->
            <div class="bg-[#0F172A] border-2 border-brand-500/50 rounded-[2.5rem] p-4 shadow-[0_0_70px_rgba(245,180,0,0.3)] relative overflow-hidden">
              
              <!-- Store Header Bar -->
              <div class="bg-[#1E293B] rounded-2xl p-4 border border-white/15 mb-3 flex items-center justify-between">
                <div class="flex items-center space-x-3">
                  <div class="w-10 h-10 rounded-xl bg-brand-500 text-slate-950 font-black flex items-center justify-center text-sm shadow">
                    LK
                  </div>
                  <div>
                    <h4 class="font-extrabold text-white text-sm">Laxmi Kirana Store</h4>
                    <p class="text-[10px] text-emerald-400 font-bold flex items-center gap-1">
                      <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Open For Orders
                    </p>
                  </div>
                </div>
                <span class="px-2.5 py-1 text-[10px] font-black bg-brand-500 text-slate-950 rounded-full">
                  Verified
                </span>
              </div>

              <!-- Product Items List Preview -->
              <div class="space-y-2.5 mb-3">
                <div class="bg-[#1E293B] p-3.5 rounded-xl border border-white/15 flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center text-lg">🌾</div>
                    <div>
                      <div class="text-xs font-bold text-white">Chakki Fresh Atta 5kg</div>
                      <div class="text-[11px] font-black text-brand-400" style="color: #FACC15 !important;">₹245.00</div>
                    </div>
                  </div>
                  <button onclick="simulateAddCart(this)" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-xs rounded-lg transition-transform active:scale-95 shadow">
                    + Add
                  </button>
                </div>

                <div class="bg-[#1E293B] p-3.5 rounded-xl border border-white/15 flex items-center justify-between">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-slate-900 flex items-center justify-center text-lg">🥛</div>
                    <div>
                      <div class="text-xs font-bold text-white">Amul Taaza Milk 1L</div>
                      <div class="text-[11px] font-black text-brand-400" style="color: #FACC15 !important;">₹54.00</div>
                    </div>
                  </div>
                  <button onclick="simulateAddCart(this)" class="px-3 py-1.5 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-xs rounded-lg transition-transform active:scale-95 shadow">
                    + Add
                  </button>
                </div>
              </div>

              <!-- WhatsApp Order Notification Card -->
              <div class="bg-gradient-to-r from-emerald-950 via-emerald-900 to-[#1E293B] border-2 border-emerald-400 p-3.5 rounded-2xl shadow-2xl flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500 text-slate-950 flex items-center justify-center shrink-0 shadow">
                  <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.147 4.191 4.225-1.108z"/></svg>
                </div>
                <div class="flex-1 min-w-0 text-left">
                  <div class="text-[11px] font-black text-emerald-300 uppercase tracking-wide">WHATSAPP ORDER SENT</div>
                  <div class="text-xs text-white truncate font-bold">"1x Atta 5kg + 2x Milk 1L — Total ₹353"</div>
                </div>
              </div>

            </div>

            <!-- Floating Badge -->
            <div class="absolute -bottom-6 -left-6 bg-[#0F172A] border-2 border-brand-500/50 px-4 py-3 rounded-2xl shadow-2xl flex items-center space-x-3 animate-float-slow hidden sm:flex">
              <span class="text-2xl">📱</span>
              <div>
                <div class="text-xs font-black text-white">Direct WhatsApp Sync</div>
                <div class="text-[10px] text-slate-300 font-extrabold">No app installation needed</div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================
       2. HOW IT WORKS
       ========================================== -->
  <section id="how-it-works" class="py-16 sm:py-24 bg-[#0F172A] relative border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="text-center max-w-3xl mx-auto mb-14 reveal-on-scroll">
        <span class="px-4 py-1.5 rounded-full bg-brand-500/15 border border-brand-500/40 text-brand-400 text-xs font-black uppercase tracking-wider">
          Simple 3-Step Onboarding
        </span>
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mt-4">
          How LocalShopOS <span class="text-brand-400" style="color: #FACC15 !important;">Works</span>
        </h2>
        <p class="text-base sm:text-lg text-slate-200 font-semibold mt-3">
          From signup to accepting your first WhatsApp order in less than 5 minutes.
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
        
        <!-- Step 1 -->
        <div class="bento-card p-8 bg-[#161F33] border border-white/15 relative flex flex-col justify-between reveal-on-scroll shadow-2xl">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-brand-500 text-slate-950 font-black text-2xl flex items-center justify-center mb-6 shadow-lg">
              01
            </div>
            <h3 class="text-xl sm:text-2xl font-black text-white mb-3">Claim Your Store Subdomain</h3>
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-semibold">
              Register your shop name and pick your clean URL (e.g. <span class="text-brand-400 font-mono font-bold" style="color: #FACC15 !important;">localshopos.com/laxmi-kirana</span>). Enter your WhatsApp phone number.
            </p>
          </div>
          <div class="mt-8 pt-4 border-t border-white/15 text-xs font-black text-brand-400 flex items-center space-x-1" style="color: #FACC15 !important;">
            <span>Instant Subdomain Setup</span> &rarr;
          </div>
        </div>

        <!-- Step 2 -->
        <div class="bento-card p-8 bg-[#161F33] border border-white/15 relative flex flex-col justify-between reveal-on-scroll shadow-2xl">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-brand-500 text-slate-950 font-black text-2xl flex items-center justify-center mb-6 shadow-lg">
              02
            </div>
            <h3 class="text-xl sm:text-2xl font-black text-white mb-3">Add Products & Print QR</h3>
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-semibold">
              Upload product photos, set prices, and stock limits. Print your store's QR code for your checkout counter or shopping bags.
            </p>
          </div>
          <div class="mt-8 pt-4 border-t border-white/15 text-xs font-black text-brand-400 flex items-center space-x-1" style="color: #FACC15 !important;">
            <span>Digital Catalog Live</span> &rarr;
          </div>
        </div>

        <!-- Step 3 -->
        <div class="bento-card p-8 bg-[#161F33] border border-white/15 relative flex flex-col justify-between reveal-on-scroll shadow-2xl">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-500 text-slate-950 font-black text-2xl flex items-center justify-center mb-6 shadow-lg">
              03
            </div>
            <h3 class="text-xl sm:text-2xl font-black text-white mb-3">Receive Orders on WhatsApp</h3>
            <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-semibold">
              Shoppers cart items and click "Send Order via WhatsApp". You get an instant itemized order list ready for delivery!
            </p>
          </div>
          <div class="mt-8 pt-4 border-t border-white/15 text-xs font-black text-emerald-400 flex items-center space-x-1">
            <span>Zero Commission Orders</span> &rarr;
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================
       3. ZERO COMMISSION BRAND CALLOUT
       ========================================== -->
  <section id="about" class="py-16 sm:py-24 bg-[#070A11] border-b border-white/10 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
      
      <div class="max-w-4xl mx-auto text-center space-y-6 reveal-on-scroll">
        <span class="px-4 py-1.5 rounded-full bg-brand-500/15 border border-brand-500/40 text-brand-400 text-xs font-black uppercase tracking-wider inline-block">
          0% Commission Guarantee
        </span>

        <h2 class="text-3xl sm:text-5xl font-black tracking-tight text-white leading-tight">
          Why Pay 15% to 30% Marketplace Commissions When You Can Keep <span class="text-brand-400" style="color: #FACC15 !important;">100% Of Your Profit?</span>
        </h2>

        <p class="text-slate-200 text-base sm:text-xl font-semibold leading-relaxed max-w-3xl mx-auto">
          Third-party delivery platforms take huge cuts and block you from direct customer contact. LocalShopOS empowers you to run your own online store with zero middleman fees.
        </p>

        <!-- Oversized Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-8 text-center">
          <div class="bento-card p-6 bg-[#161F33] border border-white/15 shadow-xl">
            <div class="text-5xl sm:text-6xl font-black text-brand-400 tracking-tight" style="color: #FACC15 !important;">₹0</div>
            <div class="text-xs sm:text-sm font-black uppercase tracking-wider mt-2 text-white">Per-Order Commission</div>
          </div>
          <div class="bento-card p-6 bg-[#161F33] border border-white/15 shadow-xl">
            <div class="text-5xl sm:text-6xl font-black text-white tracking-tight">100%</div>
            <div class="text-xs sm:text-sm font-black uppercase tracking-wider mt-2 text-white">Direct Customer Ownership</div>
          </div>
          <div class="bento-card p-6 bg-[#161F33] border border-white/15 shadow-xl">
            <div class="text-5xl sm:text-6xl font-black text-brand-400 tracking-tight" style="color: #FACC15 !important;">&lt; 5m</div>
            <div class="text-xs sm:text-sm font-black uppercase tracking-wider mt-2 text-white">Store Activation Time</div>
          </div>
        </div>

      </div>

    </div>
  </section>

  <!-- ==========================================
       4. FEATURES SHOWCASE
       ========================================== -->
  <section id="features" class="py-16 sm:py-24 bg-[#0F172A] relative overflow-hidden border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="text-center max-w-3xl mx-auto mb-16 reveal-on-scroll">
        <span class="px-4 py-1.5 rounded-full bg-brand-500/15 border border-brand-500/40 text-brand-400 text-xs font-black uppercase tracking-wider">
          Built For Merchant Growth
        </span>
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mt-4">
          Powerful Tools Built For <span class="text-brand-400" style="color: #FACC15 !important;">Your Shop</span>
        </h2>
      </div>

      <!-- Feature Row 1 -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center mb-20 reveal-on-scroll">
        <div class="lg:col-span-6 space-y-5 text-left">
          <div class="w-12 h-12 rounded-xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-xl font-black">
            💬
          </div>
          <h3 class="text-2xl sm:text-4xl font-black text-white">Direct <span class="text-brand-400" style="color: #FACC15 !important;">WhatsApp</span> Order Cart</h3>
          <p class="text-slate-200 text-base sm:text-lg leading-relaxed font-semibold">
            No complex app downloads or payment holds. Customers choose products, type their delivery address, and submit a clean itemized order list directly into your WhatsApp chat.
          </p>
          <ul class="space-y-3 text-sm sm:text-base font-bold text-white">
            <li class="flex items-center space-x-2.5">
              <span class="text-emerald-400 font-black">✓</span>
              <span>Instant customer phone number saved in chat history</span>
            </li>
            <li class="flex items-center space-x-2.5">
              <span class="text-emerald-400 font-black">✓</span>
              <span>Supports Cash on Delivery (COD) or UPI transfers</span>
            </li>
            <li class="flex items-center space-x-2.5">
              <span class="text-emerald-400 font-black">✓</span>
              <span>Pre-calculated totals and coupon discount codes</span>
            </li>
          </ul>
        </div>

        <div class="lg:col-span-6">
          <div class="bento-card p-6 bg-[#161F33] border border-emerald-500/40 shadow-2xl">
            <div class="flex items-center space-x-3 pb-4 border-b border-white/15">
              <div class="w-8 h-8 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center font-black text-xs shadow">WA</div>
              <div>
                <div class="text-xs font-bold text-white">WhatsApp Order Format Preview</div>
                <div class="text-[10px] text-slate-300 font-bold">Auto-formatted message sent by customer</div>
              </div>
            </div>
            <div class="mt-4 bg-[#0A1120] p-4 rounded-xl text-xs font-mono text-emerald-300 leading-relaxed border border-emerald-500/40 font-bold shadow-inner">
              *🛍️ NEW ORDER #1084*<br>
              ---------------------------<br>
              • 2x Fortune Chakki Atta 5kg (₹490.00)<br>
              • 1x Tata Salt 1kg (₹28.00)<br>
              ---------------------------<br>
              *Total: ₹518.00*<br><br>
              📍 *Delivery Address:*<br>
              Flat 402, Sunshine Apartments, MG Road.<br>
              📞 Phone: +91 XXXXX XXXXX
            </div>
          </div>
        </div>
      </div>

      <!-- Feature Row 2 -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center reveal-on-scroll">
        <div class="lg:col-span-6 lg:order-2 space-y-5 text-left">
          <div class="w-12 h-12 rounded-xl bg-brand-500/20 border border-brand-500/40 text-brand-400 flex items-center justify-center text-xl font-black">
            📢
          </div>
          <h3 class="text-2xl sm:text-4xl font-black text-white">Merchant Ad Engine & <span class="text-brand-400" style="color: #FACC15 !important;">Banner Uploads</span></h3>
          <p class="text-slate-200 text-base sm:text-lg leading-relaxed font-semibold">
            Highlight weekly offers, fresh arrivals, or festival discounts right at the top of your shop storefront. Boost sales by creating eye-catching visual banner campaigns.
          </p>
          <ul class="space-y-3 text-sm sm:text-base font-bold text-white">
            <li class="flex items-center space-x-2.5">
              <span class="text-brand-400 font-black" style="color: #FACC15 !important;">✓</span>
              <span>Top carousel & mid-page ad placements</span>
            </li>
            <li class="flex items-center space-x-2.5">
              <span class="text-brand-400 font-black" style="color: #FACC15 !important;">✓</span>
              <span>Set custom start & end dates for seasonal sales</span>
            </li>
            <li class="flex items-center space-x-2.5">
              <span class="text-brand-400 font-black" style="color: #FACC15 !important;">✓</span>
              <span>Click-through link redirection</span>
            </li>
          </ul>
        </div>

        <div class="lg:col-span-6 lg:order-1">
          <div class="bento-card p-6 bg-[#161F33] border border-brand-500/40 shadow-2xl">
            <div class="rounded-xl overflow-hidden relative h-48 bg-gradient-to-r from-amber-600 to-brand-500 flex items-center p-6 text-slate-950 shadow-lg">
              <div>
                <span class="px-2.5 py-1 bg-slate-950 text-brand-400 rounded-md text-[10px] font-black uppercase" style="color: #FACC15 !important;">FESTIVAL SPECIAL OFFER</span>
                <h4 class="text-xl font-black mt-2 text-slate-950">20% Off All Dairy & Sweets!</h4>
                <p class="text-xs font-bold mt-1 text-slate-900">Valid till Sunday • Use code: SWEET20</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ==========================================
       5. DYNAMIC PRICING SECTION
       ========================================== -->
  <section id="pricing" class="py-16 sm:py-24 bg-[#070A11] relative border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="text-center max-w-3xl mx-auto mb-14 reveal-on-scroll">
        <span class="px-4 py-1.5 rounded-full bg-brand-500/15 border border-brand-500/40 text-brand-400 text-xs font-black uppercase tracking-wider">
          Transparent Subscription Plans
        </span>
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mt-4">
          Choose The Right Plan For <span class="text-brand-400" style="color: #FACC15 !important;">Your Shop</span>
        </h2>
        <p class="text-base sm:text-lg text-slate-200 font-semibold mt-3">
          Start with a 14-day free trial. Upgrade anytime as your shop inventory grows.
        </p>
      </div>

      <!-- Pricing Grid — fully dynamic from DB -->
      <?php if (empty($dbPlans)): ?>
        <div class="col-span-3 text-center text-slate-400 py-20 font-bold">No plans configured yet. <a href="/admin/plans.php" class="text-brand-400 underline">Create plans →</a></div>
      <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-<?= min(count($dbPlans), 3) ?> gap-8 items-stretch">
        <?php
        // Determine "recommended" plan (non-zero mid-price plan or first paid plan)
        $paidPlans = array_values(array_filter($dbPlans, fn($p) => $p['price'] > 0));
        $recommendedId = count($paidPlans) >= 2 ? $paidPlans[1]['id'] : ($paidPlans[0]['id'] ?? null);
        ?>
        <?php foreach ($dbPlans as $plan):
          $isHighlighted = ($plan['id'] === $recommendedId);
          $isFree = ($plan['price'] == 0);
          $planFeatureSet = array_flip($plan['features']);
        ?>
          <div class="bento-card flex flex-col justify-between relative reveal-on-scroll overflow-visible
            <?= $isHighlighted
              ? 'border-2 border-brand-500 shadow-[0_0_50px_rgba(245,180,0,0.3)] bg-[#161F33] scale-[1.02]'
              : 'bg-[#131B2E] border border-white/15' ?>">

            <?php if ($isHighlighted): ?>
              <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-5 py-1.5 bg-brand-500 text-slate-950 rounded-full text-[11px] font-black uppercase tracking-wider shadow-xl whitespace-nowrap z-10">
                ★ Most Popular
              </div>
            <?php endif; ?>

            <!-- Plan Header -->
            <div class="p-7 pb-0">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-black text-white"><?= htmlspecialchars($plan['name']) ?></h3>
                <?php if ($isFree || $plan['is_default']): ?>
                  <span class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full bg-slate-700 text-slate-300 border border-white/10">Free Tier</span>
                <?php endif; ?>
              </div>

              <!-- Price Display -->
              <div class="flex items-baseline gap-2 mb-2">
                <?php if ($isFree): ?>
                  <span class="text-4xl sm:text-5xl font-black text-white">₹0</span>
                  <span class="text-xs text-slate-400 font-bold">forever</span>
                <?php else: ?>
                  <span class="text-4xl sm:text-5xl font-black text-white">₹<?= number_format($plan['price'], 0) ?></span>
                  <span class="text-xs text-slate-400 font-bold">/ <?= htmlspecialchars($plan['billing_period']) ?></span>
                <?php endif; ?>
              </div>

              <!-- Product Limit Badge -->
              <div class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl bg-brand-500/10 border border-brand-500/20 text-brand-400 text-xs font-black mb-6" style="color: #FACC15 !important; border-color: rgba(250,204,21,0.2);">
                <span>📦</span>
                <span>Up to <?= $plan['product_limit'] ?> products</span>
              </div>

              <!-- Feature List — all known features with ✓ / ✗ per plan -->
              <ul class="space-y-2.5 border-t border-white/10 pt-5 mb-6">
                <!-- Always-included base features -->
                <li class="flex items-center space-x-2.5 text-xs font-bold text-white">
                  <span class="shrink-0 w-4 h-4 rounded-full bg-brand-500 flex items-center justify-center text-slate-950 font-black text-[10px]">✓</span>
                  <span>Unlimited WhatsApp Orders</span>
                </li>
                <li class="flex items-center space-x-2.5 text-xs font-bold text-white">
                  <span class="shrink-0 w-4 h-4 rounded-full bg-brand-500 flex items-center justify-center text-slate-950 font-black text-[10px]">✓</span>
                  <span>Clean <span class="font-mono text-brand-400" style="color: #FACC15 !important;">/{subdomain}</span> Store URL</span>
                </li>

                <?php foreach ($featureLabels as $fKey => $fLabel):
                  $has = isset($planFeatureSet[$fKey]);
                ?>
                  <li class="flex items-center space-x-2.5 text-xs font-bold <?= $has ? 'text-white' : 'text-slate-600' ?>">
                    <span class="shrink-0 w-4 h-4 rounded-full flex items-center justify-center font-black text-[10px]
                      <?= $has ? 'bg-brand-500 text-slate-950' : 'bg-slate-800 text-slate-500' ?>">
                      <?= $has ? '✓' : '✗' ?>
                    </span>
                    <span><?= htmlspecialchars($fLabel) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            </div>

            <!-- CTA Button -->
            <div class="p-7 pt-0 mt-auto">
              <?php if ($isHighlighted): ?>
                <div class="mb-3 text-center text-[11px] font-black text-amber-400 uppercase tracking-widest">
                  🎯 15-day free trial included
                </div>
              <?php elseif (!$isFree): ?>
                <div class="mb-3 text-center text-[11px] font-bold text-slate-500 uppercase tracking-widest">
                  Includes 15-day free trial
                </div>
              <?php endif; ?>
              <a href="/signup.php" class="block w-full py-4 text-center text-xs font-black rounded-xl transition-all shadow-md
                <?= $isHighlighted ? 'btn-gold-action' : ($isFree ? 'border border-white/25 text-white hover:bg-white/10' : 'btn-glass-secondary text-white') ?>">
                <?= $isFree ? 'Start Free Forever' : 'Get Started Now' ?> &rarr;
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

    </div>
  </section>

  <!-- ==========================================
       6. TESTIMONIALS
       ========================================== -->
  <section class="py-16 sm:py-24 bg-[#0F172A] border-b border-white/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-14 reveal-on-scroll">
        <span class="px-4 py-1.5 rounded-full bg-brand-500/15 border border-brand-500/40 text-brand-400 text-xs font-black uppercase tracking-wider">
          Merchant Reviews
        </span>
        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mt-4">
          Loved By <span class="text-brand-400" style="color: #FACC15 !important;">Local Shopkeepers</span>
        </h2>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="bento-card p-6 bg-[#161F33] border border-white/15 flex flex-col justify-between reveal-on-scroll shadow-xl">
          <p class="text-sm sm:text-base text-white font-semibold italic leading-relaxed mb-6">
            "Setting up Laxmi Kirana on LocalShopOS took less than 10 minutes. Our regular apartment customers love ordering via WhatsApp, and we pay zero commission fees!"
          </p>
          <div class="flex items-center space-x-3 border-t border-white/15 pt-4">
            <div class="w-10 h-10 rounded-full bg-brand-500 text-slate-950 font-black flex items-center justify-center text-xs shadow">
              RS
            </div>
            <div>
              <div class="text-xs font-black text-white">Ramesh Sharma</div>
              <div class="text-[10px] text-brand-400 font-extrabold" style="color: #FACC15 !important;">Laxmi Kirana Store • Mumbai</div>
            </div>
          </div>
        </div>

        <div class="bento-card p-6 bg-[#161F33] border border-white/15 flex flex-col justify-between reveal-on-scroll shadow-xl">
          <p class="text-sm sm:text-base text-white font-semibold italic leading-relaxed mb-6">
            "The banner ads feature is amazing. Every time we introduce a new festive sweet box at Gupta Bakery, we upload a banner and orders start flowing on WhatsApp!"
          </p>
          <div class="flex items-center space-x-3 border-t border-white/15 pt-4">
            <div class="w-10 h-10 rounded-full bg-amber-600 text-white font-black flex items-center justify-center text-xs shadow">
              AG
            </div>
            <div>
              <div class="text-xs font-black text-white">Anil Gupta</div>
              <div class="text-[10px] text-brand-400 font-extrabold" style="color: #FACC15 !important;">Gupta Bakery & Sweets • Delhi</div>
            </div>
          </div>
        </div>

        <div class="bento-card p-6 bg-[#161F33] border border-white/15 flex flex-col justify-between reveal-on-scroll shadow-xl">
          <p class="text-sm sm:text-base text-white font-semibold italic leading-relaxed mb-6">
            "Having a clean store link like localshopos.com/fresh-fruits makes our business look super professional on Instagram and WhatsApp status."
          </p>
          <div class="flex items-center space-x-3 border-t border-white/15 pt-4">
            <div class="w-10 h-10 rounded-full bg-emerald-600 text-white font-black flex items-center justify-center text-xs shadow">
              VP
            </div>
            <div>
              <div class="text-xs font-black text-white">Vikram Patel</div>
              <div class="text-[10px] text-brand-400 font-extrabold" style="color: #FACC15 !important;">Fresh Fruits Mart • Ahmedabad</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </section>

  <!-- ==========================================
       7. FAQ & CONTACT SECTION
       ========================================== -->
  <section id="contact" class="py-16 sm:py-24 bg-[#070A11] relative">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <div class="lg:col-span-6 space-y-6 reveal-on-scroll">
          <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
            Frequently <span class="text-brand-400" style="color: #FACC15 !important;">Asked Questions</span>
          </h2>
          
          <div class="space-y-4">
            <div class="bento-card p-5 bg-[#161F33] border border-white/15 cursor-pointer" onclick="toggleFaq(this)">
              <div class="flex items-center justify-between font-black text-base text-white">
                <span>Do I need technical skills?</span>
                <span class="faq-icon text-brand-400 font-black" style="color: #FACC15 !important;">+</span>
              </div>
              <div class="faq-answer hidden mt-2 text-xs sm:text-sm text-slate-100 font-semibold pt-3 border-t border-white/15">
                No technical skills needed! You just add product photos and prices, and your shop is live.
              </div>
            </div>

            <div class="bento-card p-5 bg-[#161F33] border border-white/15 cursor-pointer" onclick="toggleFaq(this)">
              <div class="flex items-center justify-between font-black text-base text-white">
                <span>How do payments work?</span>
                <span class="faq-icon text-brand-400 font-black" style="color: #FACC15 !important;">+</span>
              </div>
              <div class="faq-answer hidden mt-2 text-xs sm:text-sm text-slate-100 font-semibold pt-3 border-t border-white/15">
                Orders land in your WhatsApp. You accept Cash on Delivery or send your personal UPI QR code in chat.
              </div>
            </div>

            <div class="bento-card p-5 bg-[#161F33] border border-white/15 cursor-pointer" onclick="toggleFaq(this)">
              <div class="flex items-center justify-between font-black text-base text-white">
                <span>Is there any hidden fee?</span>
                <span class="faq-icon text-brand-400 font-black" style="color: #FACC15 !important;">+</span>
              </div>
              <div class="faq-answer hidden mt-2 text-xs sm:text-sm text-slate-100 font-semibold pt-3 border-t border-white/15">
                Zero hidden fees. 0% commission on all orders. You keep 100% of your customer revenue.
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-6 reveal-on-scroll">
          <div class="bento-card p-8 bg-[#161F33] border border-brand-500/40 shadow-2xl space-y-4">
            <h3 class="text-xl sm:text-2xl font-black text-white">Need Help <span class="text-brand-400" style="color: #FACC15 !important;">Setting Up?</span></h3>
            <p class="text-xs sm:text-sm font-semibold text-slate-200">Send us a message and our support team will reach out to help you set up your store.</p>

            <div id="contactToast" class="hidden p-3 rounded-xl bg-emerald-500/20 border border-emerald-500 text-emerald-300 text-xs font-bold text-center">
              ✓ Message sent! We will contact you on WhatsApp shortly.
            </div>

            <form id="contactForm" onsubmit="handleContactSubmit(event)" class="space-y-4">
              <div>
                <label class="block text-xs font-black text-slate-200 uppercase mb-1">Your Name</label>
                <input type="text" required class="w-full px-3.5 py-3 bg-[#0A1120] border border-white/20 focus:border-brand-400 rounded-xl text-sm font-bold text-white focus:outline-none">
              </div>

              <div>
                <label class="block text-xs font-black text-slate-200 uppercase mb-1">WhatsApp Phone Number</label>
                <input type="text" required class="w-full px-3.5 py-3 bg-[#0A1120] border border-white/20 focus:border-brand-400 rounded-xl text-sm font-bold text-white focus:outline-none" placeholder="9876543210">
              </div>

              <div>
                <label class="block text-xs font-black text-slate-200 uppercase mb-1">Message</label>
                <textarea rows="3" required class="w-full px-3.5 py-3 bg-[#0A1120] border border-white/20 focus:border-brand-400 rounded-xl text-sm font-bold text-white focus:outline-none" placeholder="Tell us about your shop..."></textarea>
              </div>

              <button type="submit" class="w-full py-4 btn-gold-action rounded-xl text-xs font-black text-slate-950">
                Send Support Request &rarr;
              </button>
            </form>
          </div>
        </div>

      </div>

    </div>
  </section>

</main>

<!-- Page Specific JS -->
<script>
function toggleFaq(card) {
  const answer = card.querySelector('.faq-answer');
  const icon = card.querySelector('.faq-icon');
  if (answer.classList.contains('hidden')) {
    answer.classList.remove('hidden');
    icon.textContent = '−';
  } else {
    answer.classList.add('hidden');
    icon.textContent = '+';
  }
}

function simulateAddCart(btn) {
  const originalText = btn.textContent;
  btn.textContent = '✓ Added';
  btn.classList.replace('bg-brand-500', 'bg-emerald-500');
  btn.classList.replace('text-slate-950', 'text-white');
  setTimeout(() => {
    btn.textContent = originalText;
    btn.classList.replace('bg-emerald-500', 'bg-brand-500');
    btn.classList.replace('text-white', 'text-slate-950');
  }, 1200);
}

function handleContactSubmit(e) {
  e.preventDefault();
  const toast = document.getElementById('contactToast');
  const form = document.getElementById('contactForm');
  toast.classList.remove('hidden');
  form.reset();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
