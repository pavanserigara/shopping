<?php
$pageTitle = "Explore Local Merchants — LocalShopOS Directory";
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDBConnection();

$searchQuery = trim($_GET['q'] ?? '');
$searchCat   = trim($_GET['category'] ?? '');
$directoryError = null;
$tenants = [];
$allCategories = [];

try {
    // Fetch Active & Valid Trial Shop Tenants
    $sql = "SELECT t.*, 
            (SELECT COUNT(*) FROM products WHERE tenant_id = t.id AND is_active = 1) as total_products 
            FROM tenants t 
            WHERE (t.plan_status = 'active' OR (t.plan_status = 'trial' AND (t.trial_ends_at IS NULL OR t.trial_ends_at >= NOW())))";
    $params = [];

    if (!empty($searchQuery)) {
        $sql .= " AND (t.shop_name LIKE ? OR t.subdomain LIKE ? OR t.category LIKE ?)";
        $params[] = "%{$searchQuery}%";
        $params[] = "%{$searchQuery}%";
        $params[] = "%{$searchQuery}%";
    }

    if (!empty($searchCat)) {
        $sql .= " AND t.category = ?";
        $params[] = $searchCat;
    }

    $sql .= " ORDER BY t.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tenants = $stmt->fetchAll();

    // Get unique categories list from active tenants
    $categoriesStmt = $pdo->query("
        SELECT DISTINCT category FROM tenants 
        WHERE (plan_status = 'active' OR (plan_status = 'trial' AND (trial_ends_at IS NULL OR trial_ends_at >= NOW()))) 
          AND category IS NOT NULL AND category != ''
        ORDER BY category ASC
    ");
    $allCategories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

} catch (Exception $e) {
    error_log("Directory Query Error: " . $e->getMessage());
    $directoryError = "Unable to load merchant directory at this moment. Please refresh or try again shortly.";
}
?>

<!-- Directory Header Hero -->
<section class="bg-[#0F172A] py-8 sm:py-14 border-b border-white/10 relative overflow-hidden">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
    <span class="text-[11px] sm:text-xs font-black uppercase tracking-wider text-brand-400 bg-brand-500/20 px-3 py-1 rounded-full border border-brand-500/40 inline-block mb-2 sm:mb-3">Verified Local Stores</span>
    <h1 class="text-2xl sm:text-5xl font-black text-white tracking-tight">Shop Directory <span class="text-brand-400" style="color: #FACC15 !important;">Index</span></h1>
    <p class="text-xs sm:text-base text-slate-200 mt-2 font-semibold max-w-xl mx-auto">Discover local neighborhood kirana stores, bakeries, hardware shops, and order directly on WhatsApp.</p>
  </div>
</section>

<main class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 w-full">
  
  <?php if (!empty($directoryError)): ?>
    <div class="bento-card p-4 sm:p-6 bg-rose-950/80 border border-rose-500/50 text-rose-200 mb-6 text-center">
      <p class="font-bold text-xs sm:text-sm">⚠️ <?= htmlspecialchars($directoryError) ?></p>
    </div>
  <?php endif; ?>

  <!-- Search & Category Filters -->
  <div class="bento-card p-3.5 sm:p-4 mb-6 sm:mb-8 bg-[#161F33]">
    <form method="GET" action="/shops.php" class="flex flex-col sm:flex-row gap-3 items-center justify-between">
      
      <!-- Search Input -->
      <div class="relative w-full sm:w-80">
        <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search shop name, category..."
               class="w-full pl-9 pr-4 py-2 sm:py-2.5 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:outline-none focus:border-brand-400 font-bold">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5 sm:top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      </div>

      <!-- Category Filter Pills -->
      <div class="flex items-center space-x-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 scrollbar-none">
        <a href="/shops.php" class="px-3.5 py-1.5 rounded-full text-xs font-black whitespace-nowrap transition-all <?= empty($searchCat) ? 'bg-brand-500 text-slate-950 shadow-sm' : 'bg-white/10 text-slate-200 hover:bg-white/20' ?>">
          All Stores
        </a>
        <?php foreach ($allCategories as $cat): ?>
          <a href="/shops.php?category=<?= urlencode($cat) ?>" class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all <?= $searchCat === $cat ? 'bg-brand-500 text-slate-950 shadow-sm' : 'bg-white/10 text-slate-200 hover:bg-white/20' ?>">
            <?= htmlspecialchars($cat) ?>
          </a>
        <?php endforeach; ?>
      </div>

    </form>
  </div>

  <!-- Shops Grid -->
  <?php if (empty($tenants)): ?>
    <div class="text-center py-12 bento-card bg-[#161F33]">
      <div class="text-4xl sm:text-5xl mb-3">🏪</div>
      <h3 class="text-base font-black text-white">No active shops found</h3>
      <p class="text-xs text-slate-200 mt-1 font-semibold">Try resetting search keywords or category filters.</p>
      <a href="/shops.php" class="mt-4 inline-block px-5 py-2.5 bg-brand-500 text-slate-950 font-black text-xs rounded-xl shadow-md">View All Shops</a>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 sm:gap-6">
      <?php foreach ($tenants as $t): ?>
        <div class="bento-card p-4 sm:p-5 bg-[#161F33] flex flex-col justify-between hover:border-brand-500 group shadow-md transition-all">
          
          <div class="space-y-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center space-x-3">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-brand-500 text-slate-950 font-black text-lg sm:text-xl flex items-center justify-center shrink-0 shadow-sm overflow-hidden border border-brand-400">
                  <?php if (!empty($t['logo_url'])): ?>
                    <img src="<?= htmlspecialchars($t['logo_url']) ?>" alt="Shop Logo" class="w-full h-full object-cover">
                  <?php else: ?>
                    <?= mb_substr(htmlspecialchars($t['shop_name']), 0, 1) ?>
                  <?php endif; ?>
                </div>

                <div>
                  <h3 class="font-black text-white text-sm sm:text-base leading-tight group-hover:text-brand-400 transition-colors line-clamp-1">
                    <?= htmlspecialchars($t['shop_name']) ?>
                  </h3>
                  <span class="text-[10px] sm:text-[11px] text-brand-400 font-bold uppercase tracking-wider block" style="color: #FACC15 !important;">
                    <?= htmlspecialchars($t['category'] ?: 'General Retail') ?>
                  </span>
                </div>
              </div>

              <span class="px-2 py-0.5 text-[9px] font-black rounded-full shrink-0 <?= $t['is_open'] ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40' ?>">
                <?= $t['is_open'] ? 'OPEN' : 'CLOSED' ?>
              </span>
            </div>

            <div class="bg-[#0A1120] p-3 rounded-xl border border-white/15 space-y-1 text-xs text-slate-200 font-semibold">
              <p class="flex items-center space-x-1.5">
                <span class="text-emerald-400">💬</span>
                <span>WhatsApp: <strong class="text-white">+91 <?= htmlspecialchars($t['whatsapp_number']) ?></strong></span>
              </p>
              <p class="flex items-center space-x-1.5">
                <span class="text-brand-400" style="color: #FACC15 !important;">📦</span>
                <span>Catalog: <strong class="text-white"><?= (int)$t['total_products'] ?> Active Items</strong></span>
              </p>
            </div>
          </div>

          <div class="pt-3.5 mt-3.5 border-t border-white/15 flex items-center justify-between">
            <span class="text-[11px] text-slate-300 font-mono font-bold">/<?= htmlspecialchars($t['subdomain']) ?></span>
            <a href="/<?= urlencode($t['subdomain']) ?>" target="_blank" 
               class="px-3.5 py-2 btn-gold-action text-slate-950 font-black text-xs rounded-xl shadow-md flex items-center space-x-1">
              <span>Visit Store</span>
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
          </div>

        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
