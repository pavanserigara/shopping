<?php
$pageTitle = "Merchant Overview — LocalShopOS";
require_once __DIR__ . '/header.php';

$today = date('Y-m-d');

// Total revenue today
$revStmt = $pdo->prepare("SELECT SUM(total) FROM orders WHERE tenant_id = ? AND status = 'completed' AND DATE(created_at) = ?");
$revStmt->execute([$tenantId, $today]);
$todayRevenue = (float)$revStmt->fetchColumn();

// Total new orders today
$newOrdersStmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE tenant_id = ? AND status = 'new'");
$newOrdersStmt->execute([$tenantId]);
$newOrdersCount = (int)$newOrdersStmt->fetchColumn();

// Total active products count
$prodStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE tenant_id = ? AND is_active = 1");
$prodStmt->execute([$tenantId]);
$activeProductsCount = (int)$prodStmt->fetchColumn();

// Fetch Low Stock Alert Products (stock <= 5)
$lowStockStmt = $pdo->prepare("SELECT * FROM products WHERE tenant_id = ? AND is_active = 1 AND stock_count <= 5 ORDER BY stock_count ASC LIMIT 6");
$lowStockStmt->execute([$tenantId]);
$lowStockProducts = $lowStockStmt->fetchAll();

// Fetch Recent Orders (last 5)
$recentOrdersStmt = $pdo->prepare("SELECT * FROM orders WHERE tenant_id = ? ORDER BY id DESC LIMIT 5");
$recentOrdersStmt->execute([$tenantId]);
$recentOrders = $recentOrdersStmt->fetchAll();
?>

<!-- Store Status Header Bar -->
<div class="app-card p-6 rounded-2xl mb-8 bg-gradient-to-r from-brand-100 via-brand-50 to-white border-2 border-brand-300">
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="space-y-1">
      <div class="flex items-center space-x-2">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900">Welcome Back, <?= htmlspecialchars($shopName) ?>!</h1>
      </div>
      <p class="text-xs text-slate-700 font-medium">Your store link: 
        <a href="/<?=  urlencode($subdomain) ?>" target="_blank" class="font-extrabold text-amber-800 underline">
          localshopos.com/shop/<?= htmlspecialchars($subdomain) ?>
        </a>
      </p>
    </div>

    <!-- Store Status Toggle & Quick Link -->
    <div class="flex items-center space-x-3 bg-white p-2.5 rounded-2xl border border-brand-300 shadow-sm self-start md:self-auto">
      <span class="text-xs font-black uppercase text-slate-700">Store Status:</span>
      <form method="POST" action="/dashboard/settings.php" class="flex items-center space-x-2">
        <input type="hidden" name="action" value="toggle_store_status">
        <button type="submit" class="px-3.5 py-1.5 rounded-xl text-xs font-black transition-all <?= $isOpen ? 'bg-emerald-600 text-white shadow-md' : 'bg-rose-600 text-white shadow-md' ?>">
          <?= $isOpen ? '🟢 OPEN FOR ORDERS' : '🔴 CLOSED' ?>
        </button>
      </form>
    </div>
  </div>
</div>

<!-- Metrics Overview Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 sm:gap-6 mb-8">
  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-black uppercase text-slate-500">Today's Revenue</span>
      <span class="p-2 rounded-xl bg-brand-100 text-slate-900 text-base font-black">💰</span>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-emerald-700">₹<?= number_format($todayRevenue, 2) ?></div>
    <span class="text-[11px] text-slate-500 font-medium mt-1 block">Completed WhatsApp orders today</span>
  </div>

  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-black uppercase text-slate-500">New Orders Pending</span>
      <span class="p-2 rounded-xl bg-amber-100 text-amber-800 text-base font-black">📬</span>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-amber-700"><?= $newOrdersCount ?></div>
    <a href="/dashboard/orders.php" class="text-[11px] text-amber-800 font-extrabold mt-1 block hover:underline">View Order Inbox &rarr;</a>
  </div>

  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <div class="flex items-center justify-between mb-2">
      <span class="text-xs font-black uppercase text-slate-500">Active Products</span>
      <span class="p-2 rounded-xl bg-brand-100 text-slate-900 text-base font-black">📦</span>
    </div>
    <div class="text-2xl sm:text-3xl font-black text-slate-900"><?= $activeProductsCount ?></div>
    <a href="/dashboard/products.php" class="text-[11px] text-amber-800 font-extrabold mt-1 block hover:underline">Manage Catalog &rarr;</a>
  </div>
</div>

<!-- Low Stock Warning Banner & Quick Restock (Fix 7 Priority) -->
<?php if (!empty($lowStockProducts)): ?>
  <div class="app-card p-5 rounded-2xl mb-8 bg-amber-50 border-2 border-amber-300 space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="text-sm font-black text-amber-900 flex items-center gap-2">
        <span class="text-lg">⚠️</span>
        <span>Low-Stock Alerts (<?= count($lowStockProducts) ?> items need restocking)</span>
      </h3>
      <a href="/dashboard/products.php" class="text-xs font-black text-amber-900 underline hover:text-amber-700">Go to Products &rarr;</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
      <?php foreach ($lowStockProducts as $lp): ?>
        <div class="bg-white p-3 rounded-xl border border-amber-200 flex items-center justify-between text-xs">
          <div class="min-w-0 pr-2">
            <h5 class="font-extrabold text-slate-900 truncate"><?= htmlspecialchars($lp['name']) ?></h5>
            <span class="text-[11px] font-black <?= $lp['stock_count'] === 0 ? 'text-rose-600' : 'text-amber-700' ?>">
              <?= $lp['stock_count'] === 0 ? 'OUT OF STOCK' : 'Only ' . $lp['stock_count'] . ' left' ?>
            </span>
          </div>
          <a href="/dashboard/products.php" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-slate-950 font-black text-[10px] rounded-lg shrink-0">
            Restock
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

<!-- Quick Actions Shortcuts -->
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-8">
  <a href="/dashboard/products.php" class="app-card p-4 rounded-2xl bg-white hover:border-brand-400 text-center space-y-1.5 transition-all shadow-sm">
    <span class="text-2xl block">➕</span>
    <span class="font-black text-xs text-slate-900 block">Add Product</span>
  </a>
  <a href="/dashboard/ads.php" class="app-card p-4 rounded-2xl bg-white hover:border-brand-400 text-center space-y-1.5 transition-all shadow-sm">
    <span class="text-2xl block">📢</span>
    <span class="font-black text-xs text-slate-900 block">Manage Ads</span>
  </a>
  <a href="/dashboard/coupons.php" class="app-card p-4 rounded-2xl bg-white hover:border-brand-400 text-center space-y-1.5 transition-all shadow-sm">
    <span class="text-2xl block">🎟️</span>
    <span class="font-black text-xs text-slate-900 block">Coupons</span>
  </a>
  <a href="/dashboard/settings.php" class="app-card p-4 rounded-2xl bg-white hover:border-brand-400 text-center space-y-1.5 transition-all shadow-sm">
    <span class="text-2xl block">⚙️</span>
    <span class="font-black text-xs text-slate-900 block">Store Settings</span>
  </a>
</div>

<!-- Recent Orders Section -->
<div class="app-card rounded-2xl overflow-hidden bg-white">
  <div class="p-5 border-b border-slate-100 flex items-center justify-between">
    <h3 class="text-base font-black text-slate-900">Recent Customer Orders</h3>
    <a href="/dashboard/orders.php" class="text-xs font-black text-amber-800 hover:underline">View All Orders &rarr;</a>
  </div>

  <?php if (empty($recentOrders)): ?>
    <div class="text-center py-12">
      <div class="text-4xl mb-2">🛍️</div>
      <p class="text-xs text-slate-500 font-medium">No orders received yet.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-brand-50 text-slate-700 uppercase font-black text-[11px]">
          <tr>
            <th class="py-3 px-4">Order ID</th>
            <th class="py-3 px-4">Customer Contact</th>
            <th class="py-3 px-4">Date</th>
            <th class="py-3 px-4">Total</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php foreach ($recentOrders as $ord): ?>
            <tr class="hover:bg-brand-50/50">
              <td class="py-3 px-4 font-mono font-bold text-amber-800">#<?= $ord['id'] ?></td>
              <td class="py-3 px-4 font-bold text-slate-900">+91 <?= htmlspecialchars($ord['customer_contact']) ?></td>
              <td class="py-3 px-4 text-slate-500"><?= date('d M, h:i A', strtotime($ord['created_at'])) ?></td>
              <td class="py-3 px-4 font-black text-emerald-700">₹<?= number_format($ord['total'], 2) ?></td>
              <td class="py-3 px-4">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase 
                  <?= $ord['status'] === 'new' ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-emerald-100 text-emerald-800' ?>">
                  <?= htmlspecialchars($ord['status']) ?>
                </span>
              </td>
              <td class="py-3 px-4 text-right">
                <a href="/dashboard/orders.php" class="text-xs font-bold text-amber-800 hover:underline">Manage</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
