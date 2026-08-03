<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_tenant_auth();

$tenantId  = get_logged_tenant_id();
$pdo       = getDBConnection();

// Fetch Tenant Details for Feature Gating
$tenantCheckStmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id WHERE t.id = ?");
$tenantCheckStmt->execute([$tenantId]);
$tenantInfo = $tenantCheckStmt->fetch();

// Feature Gating Check
if (!tenant_has_feature($pdo, $tenantInfo, 'sales_reports')) {
    $pageTitle = "Sales Reports — LocalShopOS";
    require_once __DIR__ . '/header.php';
    render_locked_feature_notice('sales_reports');
    require_once __DIR__ . '/footer.php';
    exit;
}

// Export CSV Action
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $filename = "sales_report_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);

    $output = fopen('php://output', 'w');
    fputcsv($output, ['Order ID', 'Customer Contact', 'Order Date', 'Coupon Code', 'Discount (INR)', 'Total Revenue (INR)', 'Status']);

    $csvStmt = $pdo->prepare("SELECT id, customer_contact, created_at, coupon_code, discount_amount, total, status FROM orders WHERE tenant_id = ? ORDER BY id DESC");
    $csvStmt->execute([$tenantId]);
    
    while ($row = $csvStmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            '#' . $row['id'],
            '+91 ' . $row['customer_contact'],
            $row['created_at'],
            $row['coupon_code'] ?: 'None',
            number_format((float)$row['discount_amount'], 2),
            number_format((float)$row['total'], 2),
            strtoupper($row['status'])
        ]);
    }
    fclose($output);
    exit;
}

$pageTitle = "Sales & Revenue Reports — LocalShopOS";
require_once __DIR__ . '/header.php';

// Fetch Revenue Analytics
$today = date('Y-m-d');
$startOfWeek  = date('Y-m-d', strtotime('monday this week'));
$startOfMonth = date('Y-m-01');

// Today Sales
$stmtToday = $pdo->prepare("SELECT SUM(total) as revenue, COUNT(*) as orders_count FROM orders WHERE tenant_id = ? AND status = 'completed' AND DATE(created_at) = ?");
$stmtToday->execute([$tenantId, $today]);
$todayData = $stmtToday->fetch();
$todayRev   = (float)($todayData['revenue'] ?? 0);
$todayOrders = (int)($todayData['orders_count'] ?? 0);

// Week Sales
$stmtWeek = $pdo->prepare("SELECT SUM(total) as revenue, COUNT(*) as orders_count FROM orders WHERE tenant_id = ? AND status = 'completed' AND DATE(created_at) >= ?");
$stmtWeek->execute([$tenantId, $startOfWeek]);
$weekData = $stmtWeek->fetch();
$weekRev   = (float)($weekData['revenue'] ?? 0);
$weekOrders = (int)($weekData['orders_count'] ?? 0);

// Month Sales
$stmtMonth = $pdo->prepare("SELECT SUM(total) as revenue, COUNT(*) as orders_count FROM orders WHERE tenant_id = ? AND status = 'completed' AND DATE(created_at) >= ?");
$stmtMonth->execute([$tenantId, $startOfMonth]);
$monthData = $stmtMonth->fetch();
$monthRev   = (float)($monthData['revenue'] ?? 0);
$monthOrders = (int)($monthData['orders_count'] ?? 0);

// Average Order Value (AOV)
$aov = $monthOrders > 0 ? ($monthRev / $monthOrders) : 0;

// Completed Orders Ledger
$ledgerStmt = $pdo->prepare("SELECT * FROM orders WHERE tenant_id = ? AND status = 'completed' ORDER BY id DESC LIMIT 20");
$ledgerStmt->execute([$tenantId]);
$completedOrders = $ledgerStmt->fetchAll();
?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-slate-900">Sales & Revenue Reports</h1>
    <p class="text-xs text-slate-500 mt-1">Financial analytics, average order values, and completed sales ledger</p>
  </div>

  <a href="/dashboard/sales.php?export=csv" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl shadow-md flex items-center space-x-1.5 self-start md:self-auto touch-target">
    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
    <span>Export CSV Report</span>
  </a>
</div>

<!-- Revenue Analytics Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <span class="text-xs font-black uppercase text-slate-500">Today's Gross Sales</span>
    <div class="text-2xl sm:text-3xl font-black text-emerald-700 mt-1">₹<?= number_format($todayRev, 2) ?></div>
    <span class="text-[11px] text-slate-500 font-bold mt-1 block"><?= $todayOrders ?> completed orders</span>
  </div>

  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <span class="text-xs font-black uppercase text-slate-500">This Week's Sales</span>
    <div class="text-2xl sm:text-3xl font-black text-amber-800 mt-1">₹<?= number_format($weekRev, 2) ?></div>
    <span class="text-[11px] text-slate-500 font-bold mt-1 block"><?= $weekOrders ?> completed orders</span>
  </div>

  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <span class="text-xs font-black uppercase text-slate-500">This Month's Sales</span>
    <div class="text-2xl sm:text-3xl font-black text-slate-900 mt-1">₹<?= number_format($monthRev, 2) ?></div>
    <span class="text-[11px] text-slate-500 font-bold mt-1 block"><?= $monthOrders ?> completed orders</span>
  </div>

  <div class="app-card p-5 rounded-2xl bg-white border border-slate-200 shadow-sm">
    <span class="text-xs font-black uppercase text-slate-500">Average Order Value (AOV)</span>
    <div class="text-2xl sm:text-3xl font-black text-brand-600 mt-1">₹<?= number_format($aov, 2) ?></div>
    <span class="text-[11px] text-slate-500 font-bold mt-1 block">Per order average</span>
  </div>
</div>

<!-- Completed Sales Ledger Table -->
<div class="app-card rounded-2xl overflow-hidden bg-white">
  <div class="p-5 border-b border-slate-100 flex items-center justify-between">
    <h3 class="text-base font-black text-slate-900">Completed Sales Ledger</h3>
    <span class="text-xs text-slate-500 font-bold">Showing last 20 completed transactions</span>
  </div>

  <?php if (empty($completedOrders)): ?>
    <div class="text-center py-16">
      <div class="text-4xl mb-2">📊</div>
      <p class="text-xs text-slate-500 font-medium">No completed orders logged yet.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs font-medium">
        <thead class="bg-brand-50 text-slate-700 uppercase font-black text-[11px]">
          <tr>
            <th class="py-3 px-4">Order ID</th>
            <th class="py-3 px-4">Customer Contact</th>
            <th class="py-3 px-4">Completed Date</th>
            <th class="py-3 px-4">Total Revenue</th>
            <th class="py-3 px-4">Fulfillment Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($completedOrders as $ord): ?>
            <tr class="hover:bg-brand-50/40">
              <td class="py-3.5 px-4 font-mono font-bold text-amber-800">#<?= $ord['id'] ?></td>
              <td class="py-3.5 px-4 font-extrabold text-slate-900">+91 <?= htmlspecialchars($ord['customer_contact']) ?></td>
              <td class="py-3.5 px-4 text-slate-500"><?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?></td>
              <td class="py-3.5 px-4 font-black text-emerald-700">₹<?= number_format($ord['total'], 2) ?></td>
              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                  COMPLETED
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
