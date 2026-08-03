<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_super_admin_auth();

$pdo = getDBConnection();

// Fetch Global Platform Settings
try {
    $sett = $pdo->query("SELECT default_trial_days, default_product_limit FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
    $defaultTrialDays   = (int)($sett['default_trial_days'] ?? 15);
    $defaultProductLimit = (int)($sett['default_product_limit'] ?? 30);
} catch (Exception $e) {
    $defaultTrialDays   = 15;
    $defaultProductLimit = 30;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_defaults') {
    $newTrial = max(1, (int)($_POST['default_trial_days'] ?? 15));
    $newLimit = max(1, (int)($_POST['default_product_limit'] ?? 30));

    try {
        $stmt = $pdo->prepare("UPDATE platform_settings SET default_trial_days = ?, default_product_limit = ? WHERE id = 1");
        $stmt->execute([$newTrial, $newLimit]);
        $defaultTrialDays = $newTrial;
        $defaultProductLimit = $newLimit;
        set_flash('success', "Platform defaults updated: {$newTrial} days trial, {$newLimit} products limit.");
    } catch (Exception $e) {
        set_flash('error', "Failed to update platform defaults.");
    }
}

// Fetch All Plans for Selection
$allPlansStmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
$allPlans = $allPlansStmt->fetchAll();

// Handle Super Admin Actions
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || 
          isset($_POST['is_ajax']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Update Platform Defaults
    if ($action === 'update_global_settings') {
        $defaultTrialDays = max(1, (int)($_POST['default_trial_days'] ?? 15));
        $defaultProductLimit = max(1, (int)($_POST['default_product_limit'] ?? 30));

        $pdo->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('default_trial_days', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$defaultTrialDays, $defaultTrialDays]);
        $pdo->prepare("INSERT INTO platform_settings (setting_key, setting_value) VALUES ('default_product_limit', ?) ON DUPLICATE KEY UPDATE setting_value = ?")->execute([$defaultProductLimit, $defaultProductLimit]);

        $msg = "Platform global defaults updated.";
        if ($isAjax) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg]);
            exit;
        }
        set_flash('success', $msg);
        header("Location: /admin/index.php");
        exit;
    }

    // Action 2: Update Individual Tenant Controls
    if ($action === 'update_tenant_controls') {
        $tenantId     = (int)($_POST['tenant_id'] ?? 0);
        $planId       = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;
        $productLimit = max(1, (int)($_POST['product_limit'] ?? 30));
        $planStatus   = $_POST['plan_status'] ?? 'active';

        if (!in_array($planStatus, ['active', 'trial', 'trial_expired', 'suspended'])) {
            $planStatus = 'active';
        }

        if ($planId) {
            $pCheck = $pdo->prepare("SELECT product_limit FROM plans WHERE id = ?");
            $pCheck->execute([$planId]);
            $pLimit = $pCheck->fetchColumn();
            if ($pLimit) {
                $productLimit = (int)$pLimit;
            }
        }

        $update = $pdo->prepare("UPDATE tenants SET plan_id = ?, product_limit = ?, plan_status = ? WHERE id = ?");
        $update->execute([$planId, $productLimit, $planStatus, $tenantId]);

        $msg = "Tenant #{$tenantId} updated successfully!";
        if ($isAjax) {
            if (ob_get_length()) ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $msg]);
            exit;
        }
        set_flash('success', $msg);
        header("Location: /admin/index.php");
        exit;
    }

    // Action 3: Log Manual Payment / Invoice (v7)
    if ($action === 'log_payment') {
        $tenantId    = (int)($_POST['tenant_id'] ?? 0);
        $planId      = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;
        $amount      = (float)($_POST['amount'] ?? 0);
        $paymentDate = $_POST['payment_date'] ?: date('Y-m-d');
        $notes       = trim($_POST['notes'] ?? '');

        if ($tenantId > 0 && $amount > 0) {
            $logStmt = $pdo->prepare("
                INSERT INTO payment_log (tenant_id, plan_id, amount, payment_date, notes) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $logStmt->execute([$tenantId, $planId, $amount, $paymentDate, $notes]);

            // Update tenant plan status to active and assign plan
            $upStmt = $pdo->prepare("UPDATE tenants SET plan_status = 'active', plan_id = COALESCE(?, plan_id) WHERE id = ?");
            $upStmt->execute([$planId, $tenantId]);

            $msg = "Payment of ₹{$amount} logged for Tenant #{$tenantId}. Account set to Active.";
            if ($isAjax) {
                if (ob_get_length()) ob_clean();
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $msg]);
                exit;
            }
            set_flash('success', $msg);
        }
        header("Location: /admin/index.php");
        exit;
    }
}

// Platform Analytics Metrics
$totalTenants  = (int)$pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
$activeTenants = (int)$pdo->query("SELECT COUNT(*) FROM tenants WHERE plan_status = 'active'")->fetchColumn();
$trialTenants  = (int)$pdo->query("SELECT COUNT(*) FROM tenants WHERE plan_status = 'trial' AND (trial_ends_at IS NULL OR trial_ends_at >= NOW())")->fetchColumn();

// Platform MRR (Sum of price of active tenant plans)
$mrr = (float)$pdo->query("
    SELECT SUM(p.price) 
    FROM tenants t 
    JOIN plans p ON t.plan_id = p.id 
    WHERE t.plan_status = 'active'
")->fetchColumn();

// Total Manual Payments Logged
$totalPaymentsLogged = (float)$pdo->query("SELECT SUM(amount) FROM payment_log")->fetchColumn();

// Conversion Rate
$conversionRate = $totalTenants > 0 ? round(($activeTenants / $totalTenants) * 100, 1) : 0;

// Fetch All Registered Tenants with Health Check Indicators
$tenantsStmt = $pdo->query("
    SELECT t.*, p.name as plan_name,
           (SELECT COUNT(*) FROM products WHERE tenant_id = t.id) as product_count,
           (SELECT COUNT(*) FROM orders WHERE tenant_id = t.id) as order_count,
           (SELECT MAX(created_at) FROM orders WHERE tenant_id = t.id) as last_order_at,
           (SELECT SUM(total) FROM orders WHERE tenant_id = t.id AND status = 'completed') as total_revenue
    FROM tenants t 
    LEFT JOIN plans p ON t.plan_id = p.id
    ORDER BY t.id DESC
");
$tenants = $tenantsStmt->fetchAll();

// Fetch Payment Log History
$paymentsStmt = $pdo->query("
    SELECT pl.*, t.shop_name, p.name as plan_name
    FROM payment_log pl
    JOIN tenants t ON pl.tenant_id = t.id
    LEFT JOIN plans p ON pl.plan_id = p.id
    ORDER BY pl.id DESC LIMIT 15
");
$paymentLogs = $paymentsStmt->fetchAll();

$pageTitle = "Platform Revenue & Tenant Health Dashboard — Super Admin";
require_once __DIR__ . '/header.php';
?>
  
  <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-slate-900">Platform Revenue & Tenant Health Dashboard</h1>
      <p class="text-xs text-slate-500 mt-1 font-medium">Track MRR metrics, log manual subscription payments, and monitor active store health</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="/admin/users.php" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow-sm transition-all flex items-center space-x-1.5">
        <span>👥 User & Password Reset Directory</span>
      </a>
      <button onclick="document.getElementById('logPaymentModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md flex items-center space-x-1.5">
        <span>+ Log Manual Payment</span>
      </button>
      <a href="/admin/plans.php" class="px-4 py-2 bg-brand-500 text-slate-950 font-black text-xs rounded-xl shadow-sm hover:bg-brand-400">
        Manage Plans
      </a>
    </div>
  </div>

  <!-- Platform Executive MRR & Revenue Cards Grid (Fix 7 Priority) -->
  <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-8">
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <span class="text-xs font-black uppercase text-slate-500">Platform MRR (Active Plans)</span>
      <div class="text-3xl font-black text-emerald-700 mt-1">₹<?= number_format($mrr, 2) ?></div>
      <span class="text-[11px] text-slate-500 font-bold block mt-1">Monthly Recurring Revenue</span>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <span class="text-xs font-black uppercase text-slate-500">Total Manual Invoices Logged</span>
      <div class="text-3xl font-black text-amber-800 mt-1">₹<?= number_format($totalPaymentsLogged, 2) ?></div>
      <span class="text-[11px] text-slate-500 font-bold block mt-1">Offline UPI/Cash collected</span>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <span class="text-xs font-black uppercase text-slate-500">Trial-to-Paid Conversion</span>
      <div class="text-3xl font-black text-slate-900 mt-1"><?= $conversionRate ?>%</div>
      <span class="text-[11px] text-emerald-700 font-bold block mt-1"><?= $activeTenants ?> Paid / <?= $trialTenants ?> In Trial</span>
    </div>

    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
      <span class="text-xs font-black uppercase text-slate-500">Total Registered Tenants</span>
      <div class="text-3xl font-black text-brand-600 mt-1"><?= $totalTenants ?></div>
      <span class="text-[11px] text-slate-500 font-bold block mt-1">Active platform stores</span>
    </div>
  </div>

  <!-- Global Platform Controls Settings Form -->
  <div class="bg-white border border-brand-200 rounded-2xl p-6 mb-8 shadow-sm">
    <h3 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
      <span>⚡ Global Signup & Trial Default Settings</span>
    </h3>
    
    <form method="POST" action="/admin/index.php" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
      <input type="hidden" name="action" value="update_global_settings">
      
      <div>
        <label class="block text-xs font-black uppercase text-slate-700 mb-1">Default Trial Duration (Days)</label>
        <input type="number" name="default_trial_days" value="<?= $defaultTrialDays ?>" min="1" required
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
      </div>

      <div>
        <label class="block text-xs font-black uppercase text-slate-700 mb-1">Default Product Limit Per Shop</label>
        <input type="number" name="default_product_limit" value="<?= $defaultProductLimit ?>" min="1" required
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
      </div>

      <button type="submit" class="py-2.5 px-6 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow-md transition-all">
        Save Global Defaults
      </button>
    </form>
  </div>

  <!-- Tenants Health & Management Table -->
  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm mb-8">
    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div>
        <h3 class="text-base font-black text-slate-900">Registered Tenants & Health Monitoring</h3>
        <span class="text-xs font-bold text-slate-500" id="tenantCountLabel"><?= count($tenants) ?> Total Tenants</span>
      </div>

      <!-- Live Search Bar -->
      <div class="relative w-full sm:w-72">
        <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
          <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <input
          type="text"
          id="tenantSearchBar"
          oninput="filterTenants(this.value)"
          placeholder="Search shop name, subdomain, phone…"
          class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-brand-400 focus:ring-2 focus:ring-brand-300 transition-all"
        >
        <button onclick="clearTenantSearch()" id="clearSearchBtn" class="hidden absolute inset-y-0 right-2.5 flex items-center text-slate-400 hover:text-slate-700">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </div>

    <!-- No Results Message -->
    <div id="noTenantsMsg" class="hidden text-center py-12">
      <div class="text-3xl mb-2">🔍</div>
      <p class="text-sm font-black text-slate-600">No stores match your search</p>
      <p class="text-xs text-slate-400 mt-1">Try searching by shop name or subdomain</p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs font-medium">
        <thead class="bg-brand-50 text-slate-700 uppercase font-black text-[11px]">
          <tr>
            <th class="py-3.5 px-4">ID & Shop</th>
            <th class="py-3.5 px-4">Subdomain & Contact</th>
            <th class="py-3.5 px-4">Tenant Health</th>
            <th class="py-3.5 px-4">Catalog / Orders</th>
            <th class="py-3.5 px-4">Status & Trial</th>
            <th class="py-3.5 px-4">Assigned Plan</th>
            <th class="py-3.5 px-4 text-right">Plan Controls</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($tenants as $t): 
            $nowTS = time();
            $trialEndTS = !empty($t['trial_ends_at']) ? strtotime($t['trial_ends_at']) : 0;
            $isTrialExpired = ($t['plan_status'] === 'trial' && $trialEndTS > 0 && $nowTS > $trialEndTS);
            
            // Health Check: Check days since last login or last order
            $lastActivityTS = max(
                !empty($t['last_login_at']) ? strtotime($t['last_login_at']) : 0,
                !empty($t['last_order_at']) ? strtotime($t['last_order_at']) : 0,
                strtotime($t['created_at'])
            );
            $daysInactive = floor(($nowTS - $lastActivityTS) / 86400);
            $isInactive = ($daysInactive >= 14);

            if ($t['plan_status'] === 'suspended') {
                $statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-300">Suspended</span>';
            } elseif ($t['plan_status'] === 'active') {
                $statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">Paid Active</span>';
            } elseif ($isTrialExpired || $t['plan_status'] === 'trial_expired') {
                $statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-900 border border-amber-300">Trial Expired</span>';
            } else {
                $daysLeft = ceil(($trialEndTS - $nowTS) / 86400);
                $statusBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-brand-200 text-slate-950 border border-brand-400">Trial (' . max(0, $daysLeft) . 'd left)</span>';
            }
          ?>
            <tr class="hover:bg-brand-50/40 tenant-row"
                data-search="<?= strtolower(htmlspecialchars($t['shop_name'] . ' ' . $t['subdomain'] . ' ' . $t['whatsapp_number'])) ?>">
              <td class="py-3.5 px-4 font-mono font-bold text-slate-500">
                <div class="flex items-center space-x-2">
                  <div class="w-8 h-8 rounded-lg bg-brand-100 overflow-hidden flex items-center justify-center font-black text-slate-900 border border-brand-300 shrink-0">
                    <?php if (!empty($t['logo_url'])): ?>
                      <img src="<?= htmlspecialchars($t['logo_url']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                      <?= mb_substr(htmlspecialchars($t['shop_name']), 0, 1) ?>
                    <?php endif; ?>
                  </div>
                  <div>
                    <h4 class="font-black text-slate-900"><?= htmlspecialchars($t['shop_name']) ?></h4>
                    <span class="text-[10px] text-slate-400">#<?= $t['id'] ?></span>
                  </div>
                </div>
              </td>

              <td class="py-3.5 px-4">
                <a href="/<?=  urlencode($t['subdomain']) ?>" target="_blank" class="font-mono text-[11px] text-amber-800 font-bold hover:underline block">
                  /shop/<?= htmlspecialchars($t['subdomain']) ?>
                </a>
                <span class="text-[10px] text-slate-500">+91 <?= htmlspecialchars($t['whatsapp_number']) ?></span>
              </td>

              <!-- At-a-Glance Health Check Column -->
              <td class="py-3.5 px-4">
                <?php if ($isInactive): ?>
                  <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-rose-100 text-rose-800 border border-rose-300" title="No order or login in <?= $daysInactive ?> days">
                    ⚠️ Inactive (<?= $daysInactive ?>d)
                  </span>
                <?php else: ?>
                  <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300">
                    🟢 Healthy (<?= $daysInactive ?>d ago)
                  </span>
                <?php endif; ?>
              </td>

              <td class="py-3.5 px-4">
                <span class="font-black text-slate-900"><?= (int)$t['product_count'] ?> items</span> / 
                <strong class="text-emerald-700"><?= (int)$t['order_count'] ?> orders</strong>
              </td>

              <td class="py-3.5 px-4">
                <?= $statusBadge ?>
              </td>

              <td class="py-3.5 px-4">
                <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-slate-100 text-slate-800 border border-slate-200">
                  <?= htmlspecialchars($t['plan_name'] ?: 'Starter Free') ?>
                </span>
              </td>

              <td class="py-3.5 px-4 text-right">
                <form method="POST" action="/admin/index.php" class="flex items-center justify-end space-x-2">
                  <input type="hidden" name="action" value="update_tenant_controls">
                  <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
                  
                  <select name="plan_id" class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                    <option value="">-- Plan --</option>
                    <?php foreach ($allPlans as $pl): ?>
                      <option value="<?= $pl['id'] ?>" <?= (int)$t['plan_id'] === (int)$pl['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($pl['name']) ?> (₹<?= (int)$pl['price'] ?>)
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <select name="plan_status" class="px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                    <option value="active" <?= $t['plan_status'] === 'active' ? 'selected' : '' ?>>Paid Active</option>
                    <option value="trial" <?= $t['plan_status'] === 'trial' ? 'selected' : '' ?>>Trial Mode</option>
                    <option value="trial_expired" <?= ($t['plan_status'] === 'trial_expired' || $isTrialExpired) ? 'selected' : '' ?>>Trial Expired</option>
                    <option value="suspended" <?= $t['plan_status'] === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                  </select>

                  <button type="submit" class="px-2.5 py-1 bg-slate-900 text-white rounded-lg text-xs font-black hover:bg-slate-800">
                    Save
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Manual Payment Invoice Log History Table -->
  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="text-base font-black text-slate-900">Recent Manual Payment Invoices Logged</h3>
      <button onclick="document.getElementById('logPaymentModal').classList.remove('hidden')" class="text-xs font-black text-emerald-800 hover:underline">+ Log Payment</button>
    </div>

    <?php if (empty($paymentLogs)): ?>
      <div class="text-center py-10 text-xs text-slate-500 font-bold">No manual payments logged yet.</div>
    <?php else: ?>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs font-medium">
          <thead class="bg-slate-50 text-slate-700 uppercase font-black text-[11px]">
            <tr>
              <th class="py-3 px-4">Log ID</th>
              <th class="py-3 px-4">Tenant Store</th>
              <th class="py-3 px-4">Plan</th>
              <th class="py-3 px-4">Amount Paid</th>
              <th class="py-3 px-4">Payment Date</th>
              <th class="py-3 px-4">Notes</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php foreach ($paymentLogs as $plog): ?>
              <tr>
                <td class="py-3 px-4 font-mono font-bold text-slate-500">#<?= $plog['id'] ?></td>
                <td class="py-3 px-4 font-black text-slate-900"><?= htmlspecialchars($plog['shop_name']) ?></td>
                <td class="py-3 px-4 font-bold text-amber-800"><?= htmlspecialchars($plog['plan_name'] ?: 'Subscription') ?></td>
                <td class="py-3 px-4 font-black text-emerald-700">₹<?= number_format($plog['amount'], 2) ?></td>
                <td class="py-3 px-4 text-slate-500"><?= date('d M Y', strtotime($plog['payment_date'])) ?></td>
                <td class="py-3 px-4 text-slate-600 italic"><?= htmlspecialchars($plog['notes'] ?: 'Offline Payment') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</main>

<script>
function filterTenants(query) {
  const q = query.toLowerCase().trim();
  const rows = document.querySelectorAll('.tenant-row');
  const noMsg = document.getElementById('noTenantsMsg');
  const clearBtn = document.getElementById('clearSearchBtn');
  const countLabel = document.getElementById('tenantCountLabel');

  clearBtn.classList.toggle('hidden', q === '');

  let visible = 0;
  rows.forEach(row => {
    const text = row.getAttribute('data-search') || '';
    const match = q === '' || text.includes(q);
    row.style.display = match ? '' : 'none';
    if (match) visible++;
  });

  // Show/hide no-results message
  noMsg.classList.toggle('hidden', visible > 0);

  // Update counter label
  countLabel.textContent = q === ''
    ? `${rows.length} Total Tenants`
    : `${visible} of ${rows.length} Tenants`;
}

function clearTenantSearch() {
  const bar = document.getElementById('tenantSearchBar');
  bar.value = '';
  filterTenants('');
  bar.focus();
}
</script>

<!-- Manual Payment Modal -->
<div id="logPaymentModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white p-6 rounded-2xl w-full max-w-md shadow-2xl relative">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
      <h3 class="text-base font-black text-slate-900">Log Manual Subscription Payment</h3>
      <button onclick="document.getElementById('logPaymentModal').classList.add('hidden')" class="text-slate-400 font-bold text-xl">&times;</button>
    </div>

    <form method="POST" action="/admin/index.php" class="space-y-4">
      <input type="hidden" name="action" value="log_payment">

      <div>
        <label class="block text-xs font-black uppercase text-slate-700 mb-1">Select Tenant Store *</label>
        <select name="tenant_id" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
          <option value="">-- Choose Tenant --</option>
          <?php foreach ($tenants as $t): ?>
            <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['shop_name']) ?> (/shop/<?= htmlspecialchars($t['subdomain']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Assigned Plan</label>
          <select name="plan_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
            <option value="">-- Keep Current --</option>
            <?php foreach ($allPlans as $pl): ?>
              <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['name']) ?> (₹<?= (int)$pl['price'] ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Amount Paid (₹) *</label>
          <input type="number" step="0.01" name="amount" required placeholder="499.00" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
        </div>
      </div>

      <div>
        <label class="block text-xs font-black uppercase text-slate-700 mb-1">Payment Date *</label>
        <input type="date" name="payment_date" value="<?= date('Y-m-d') ?>" required class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
      </div>

      <div>
        <label class="block text-xs font-black uppercase text-slate-700 mb-1">Notes / Transaction Reference</label>
        <input type="text" name="notes" placeholder="UPI Ref / GPay / Bank Transfer #123" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
      </div>

      <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
        <button type="button" onclick="document.getElementById('logPaymentModal').classList.add('hidden')" class="px-4 py-2 bg-slate-100 text-slate-700 text-xs font-bold rounded-xl">Cancel</button>
        <button type="submit" class="px-5 py-2 bg-emerald-600 text-white text-xs font-black rounded-xl shadow-md hover:bg-emerald-700">Save & Activate</button>
      </div>
    </form>
  </div>
</div>

<script>
document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function(e) {
    e.preventDefault();
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) submitBtn.disabled = true;

    const formData = new FormData(this);
    formData.append('is_ajax', '1');

    fetch(this.action || window.location.href, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
      if (submitBtn) submitBtn.disabled = false;
      if (data.success) {
        if (typeof showAdminToast === 'function') {
          showAdminToast(data.message);
        }
        // Close modals if open
        const tenantModal = document.getElementById('tenantControlModal');
        const paymentModal = document.getElementById('logPaymentModal');
        if (tenantModal) tenantModal.classList.add('hidden');
        if (paymentModal) paymentModal.classList.add('hidden');
      } else {
        if (typeof showAdminToast === 'function') {
          showAdminToast(data.error || "Update failed", true);
        } else {
          alert(data.error || "Update failed");
        }
      }
    })
    .catch(err => {
      if (submitBtn) submitBtn.disabled = false;
      console.error("AJAX submit error:", err);
      form.submit();
    });
  });
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
