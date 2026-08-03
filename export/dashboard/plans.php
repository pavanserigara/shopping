<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_tenant_auth();

$pdo = getDBConnection();
$tenantId = (int)$_SESSION['tenant_id'];

// Fetch Tenant Info
$stmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id WHERE t.id = ?");
$stmt->execute([$tenantId]);
$tenant = $stmt->fetch();

// Fetch All Plans with Features List
$plansStmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
$plans = $plansStmt->fetchAll();

$planFeaturesMap = [];
$pfRows = $pdo->query("SELECT * FROM plan_features")->fetchAll();
foreach ($pfRows as $r) {
    $planFeaturesMap[$r['plan_id']][] = $r['feature_key'];
}

$inTrial = is_tenant_in_trial($tenant);

// Fetch platform WhatsApp contact number (set by super admin in /admin/settings.php)
$waNumRow = $pdo->query("SELECT whatsapp_contact FROM platform_settings WHERE id = 1")->fetchColumn();
$whatsappContact = !empty($waNumRow) ? trim($waNumRow) : '919999999999'; // fallback until admin sets it
$waUpgradeBase = "https://wa.me/{$whatsappContact}?text=";
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#FFFDF7] font-sans antialiased text-slate-900">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Plans & Upgrades — Merchant Dashboard</title>
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
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800;900&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #FFFDF7; }
    .app-card { background-color: #FFFFFF; border: 1px solid #F1F5F9; box-shadow: 0 2px 10px -2px rgba(245, 180, 0, 0.06); border-radius: 1.5rem; }
    .btn-cta { background-color: #D97F00; color: #FFFFFF; font-weight: 800; }
  </style>
</head>
<body class="min-h-full flex flex-col justify-between bg-[#FFFDF7]">

<header class="bg-white border-b border-brand-200 sticky top-0 z-40">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between h-16">
      <div class="flex items-center space-x-3">
        <a href="/dashboard/index.php"><img src="/assets/logo.png" alt="LocalShopOS Logo" class="w-8 h-8 object-contain rounded-lg"></a>
        <span class="font-black text-base text-slate-900"><?= htmlspecialchars($tenant['shop_name']) ?></span>
        <span class="text-xs bg-brand-100 text-amber-900 font-bold px-2.5 py-0.5 rounded-full border border-brand-300">Subscription Plans</span>
      </div>

      <div class="flex items-center space-x-3">
        <a href="/dashboard/index.php" class="text-xs font-bold text-slate-700 hover:text-slate-900">&larr; Back to Dashboard</a>
      </div>
    </div>
  </div>
</header>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
  
  <div class="text-center max-w-2xl mx-auto mb-10">
    <span class="px-3.5 py-1 rounded-full text-xs font-black uppercase tracking-wider bg-brand-100 text-amber-900 border border-brand-300 inline-block mb-3">
      Subscription & Growth Plans
    </span>
    <h1 class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Upgrade Your Local Store Capabilities</h1>
    <p class="text-xs sm:text-sm text-slate-600 mt-2 font-medium">Unlock photo galleries, custom logo uploads, ad banners, and ad view analytics</p>

    <!-- Current Plan Banner -->
    <div class="mt-6 p-4 rounded-2xl bg-white border border-brand-300 shadow-sm inline-block text-left">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-xl bg-brand-500 text-slate-950 font-black text-xl flex items-center justify-center shrink-0">
          👑
        </div>
        <div>
          <span class="text-[10px] font-black uppercase text-slate-400 block">Your Current Status</span>
          <h4 class="text-sm font-black text-slate-900">
            <?= $inTrial ? '⚡ Active Free Trial (Full Access Unlocked)' : htmlspecialchars($tenant['plan_name'] ?: 'Free Tier Plan') ?>
          </h4>
          <span class="text-[11px] text-amber-800 font-bold">Catalog Limit: <?= (int)$tenant['product_limit'] ?> Items</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Plans Comparison Matrix Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <?php foreach ($plans as $p): 
      $pFeats = $planFeaturesMap[$p['id']] ?? [];
      $isCurrentPlan = ((int)$tenant['plan_id'] === (int)$p['id'] && !$inTrial);
    ?>
      <div class="app-card p-6 sm:p-8 rounded-3xl flex flex-col justify-between relative bg-white transition-all hover:shadow-xl <?= $isCurrentPlan ? 'border-2 border-brand-500 ring-2 ring-brand-300' : 'border border-slate-200' ?>">
        
        <?php if ($isCurrentPlan): ?>
          <span class="absolute -top-3.5 right-6 px-3.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-900 text-brand-400 border border-slate-800">
            Current Plan
          </span>
        <?php elseif ($p['is_default']): ?>
          <span class="absolute -top-3.5 right-6 px-3.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-700 border border-slate-300">
            Starter Plan
          </span>
        <?php endif; ?>

        <div>
          <div class="border-b border-slate-100 pb-5 mb-6">
            <h3 class="text-xl font-black text-slate-900"><?= htmlspecialchars($p['name']) ?></h3>
            <div class="mt-3 flex items-baseline gap-1">
              <span class="text-4xl font-black text-slate-900">₹<?= number_format($p['price'], 0) ?></span>
              <span class="text-xs text-slate-500 font-bold">/ <?= htmlspecialchars($p['billing_period']) ?></span>
            </div>
            <p class="text-xs text-amber-800 font-extrabold mt-1">Up to <strong><?= (int)$p['product_limit'] ?> Products</strong></p>
          </div>

          <div class="space-y-3 mb-8">
            <span class="text-[11px] font-black uppercase text-slate-400 block tracking-wider">Features & Capabilities</span>
            <?php foreach (FEATURE_REGISTRY as $fk => $info): 
              $hasFeat = in_array($fk, $pFeats);
            ?>
              <div class="flex items-start space-x-2.5 text-xs">
                <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 mt-0.5 text-[10px] font-black <?= $hasFeat ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' ?>">
                  <?= $hasFeat ? '✓' : '✕' ?>
                </span>
                <span class="<?= $hasFeat ? 'text-slate-800 font-bold' : 'text-slate-400 line-through' ?>">
                  <?= htmlspecialchars($info['name']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100">
          <?php if ($isCurrentPlan): ?>
            <button disabled class="w-full py-3 bg-slate-100 text-slate-500 font-black text-xs rounded-xl cursor-default">
              Active Plan
            </button>
          <?php else: ?>
            <a href="<?= $waUpgradeBase . urlencode("Hello LocalShopOS! I would like to upgrade my shop '{$tenant['shop_name']}' to the {$p['name']} plan (₹{$p['price']}/mo). Please assist.") ?>" target="_blank"
               class="w-full py-3.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md flex items-center justify-center space-x-1.5 touch-target hover:scale-[1.02] transition-all">
              <span>Upgrade to <?= htmlspecialchars($p['name']) ?> &rarr;</span>
            </a>
          <?php endif; ?>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

</main>

<footer class="bg-white border-t border-slate-200 py-4 mt-8">
  <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500 font-medium">
    LocalShopOS Subscription & Upgrade Portal. Need custom limits? Contact support on WhatsApp.
  </div>
</footer>

</body>
</html>
