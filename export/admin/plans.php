<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_super_admin_auth();

$pdo = getDBConnection();
$errors = [];

// Handle Plan Creation, Updates, Deletions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Create or Edit Plan
    if ($action === 'save_plan') {
        $planId        = (int)($_POST['plan_id'] ?? 0);
        $name          = trim($_POST['name'] ?? '');
        $price         = (float)($_POST['price'] ?? 0);
        $billingPeriod = trim($_POST['billing_period'] ?? 'monthly');
        $productLimit  = max(1, (int)($_POST['product_limit'] ?? 30));
        $isDefault     = isset($_POST['is_default']) ? 1 : 0;
        $selectedFeats = $_POST['features'] ?? [];

        if (empty($name)) $errors[] = "Plan name is required.";

        if (empty($errors)) {
            // If setting as default, un-default other plans first
            if ($isDefault) {
                $pdo->exec("UPDATE plans SET is_default = 0");
            }

            if ($planId > 0) {
                // Update Plan
                $stmt = $pdo->prepare("UPDATE plans SET name = ?, price = ?, billing_period = ?, product_limit = ?, is_default = ? WHERE id = ?");
                $stmt->execute([$name, $price, $billingPeriod, $productLimit, $isDefault, $planId]);
            } else {
                // Insert New Plan
                $stmt = $pdo->prepare("INSERT INTO plans (name, price, billing_period, product_limit, is_default) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $price, $billingPeriod, $productLimit, $isDefault]);
                $planId = (int)$pdo->lastInsertId();
            }

            // Sync Plan Features Checklist
            $pdo->prepare("DELETE FROM plan_features WHERE plan_id = ?")->execute([$planId]);
            $featStmt = $pdo->prepare("INSERT INTO plan_features (plan_id, feature_key) VALUES (?, ?)");
            foreach ($selectedFeats as $fk) {
                if (isset(FEATURE_REGISTRY[$fk])) {
                    $featStmt->execute([$planId, $fk]);
                }
            }

            respond_flash('success', "Subscription plan '{$name}' saved successfully!", '/admin/plans.php', ['reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/admin/plans.php');
        }
    }

    // Action 2: Delete Plan
    if ($action === 'delete_plan') {
        $planId = (int)($_POST['plan_id'] ?? 0);
        
        // Prevent deleting default plan
        $checkDef = $pdo->prepare("SELECT is_default FROM plans WHERE id = ?");
        $checkDef->execute([$planId]);
        if ($checkDef->fetchColumn()) {
            respond_flash('error', "Cannot delete the default plan. Please set another plan as default first.", '/admin/plans.php');
        } else {
            $pdo->prepare("DELETE FROM plans WHERE id = ?")->execute([$planId]);
            respond_flash('success', "Plan deleted.", '/admin/plans.php', ['reload' => true]);
        }
    }
}

// Fetch All Plans with Features List
$plansStmt = $pdo->query("SELECT * FROM plans ORDER BY price ASC");
$plans = $plansStmt->fetchAll();

$planFeaturesMap = [];
$pfRows = $pdo->query("SELECT * FROM plan_features")->fetchAll();
foreach ($pfRows as $r) {
    $planFeaturesMap[$r['plan_id']][] = $r['feature_key'];
}

$pageTitle = "Plan Builder & Feature Registry — Super Admin";
require_once __DIR__ . '/header.php';
?>

<div class="mb-6 sm:mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-xl sm:text-2xl font-black text-slate-900">Subscription Plan System & Feature Registry</h1>
    <p class="text-xs text-slate-500 mt-1 font-medium">Create custom plans, assign feature access capabilities, and set default post-trial plans</p>
  </div>
  <button onclick="openPlanModal()" class="w-full sm:w-auto px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow-md flex items-center justify-center space-x-1.5 touch-target">
    <span>+ Create New Plan</span>
  </button>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs space-y-1">
    <?php foreach ($errors as $err): ?>
      <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Plans Grid -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
  <?php foreach ($plans as $p): 
    $pFeats = $planFeaturesMap[$p['id']] ?? [];
  ?>
    <div class="bg-white border-2 rounded-3xl p-5 sm:p-6 shadow-sm flex flex-col justify-between relative <?= $p['is_default'] ? 'border-brand-500 bg-brand-50/20' : 'border-slate-200' ?>">
      
      <?php if ($p['is_default']): ?>
        <span class="absolute -top-3 right-6 px-3 py-0.5 rounded-full text-[10px] font-black uppercase bg-brand-500 text-slate-950 shadow-sm border border-brand-400">
          ★ Default Post-Trial Plan
        </span>
      <?php endif; ?>

      <div>
        <div class="border-b border-slate-100 pb-4 mb-4">
          <h3 class="text-lg font-black text-slate-900"><?= htmlspecialchars($p['name']) ?></h3>
          <div class="mt-2 flex items-baseline gap-1">
            <span class="text-3xl font-black text-slate-900">₹<?= number_format($p['price'], 2) ?></span>
            <span class="text-xs text-slate-500 font-bold">/ <?= htmlspecialchars($p['billing_period']) ?></span>
          </div>
          <p class="text-xs text-amber-800 font-bold mt-1">Catalog Limit: <strong><?= (int)$p['product_limit'] ?> Items</strong></p>
        </div>

        <div class="space-y-2 mb-6">
          <span class="text-[11px] font-black uppercase text-slate-400 block tracking-wider">Included Capabilities</span>
          <?php foreach (FEATURE_REGISTRY as $fk => $info): 
            $hasFeat = in_array($fk, $pFeats);
          ?>
            <div class="flex items-center justify-between text-xs font-medium <?= $hasFeat ? 'text-slate-800 font-bold' : 'text-slate-400 opacity-60' ?>">
              <span class="flex items-center gap-1.5">
                <span class="<?= $hasFeat ? 'text-emerald-600 font-black' : 'text-slate-300' ?>"><?= $hasFeat ? '✓' : '✕' ?></span>
                <span><?= htmlspecialchars($info['name']) ?></span>
              </span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
        <button onclick="editPlan(<?= (int)$p['id'] ?>)" class="text-xs font-black text-amber-800 hover:underline touch-target">
          Edit Features & Pricing &rarr;
        </button>

        <?php if (!$p['is_default']): ?>
          <form method="POST" action="/admin/plans.php" class="inline" onsubmit="return confirm('Delete plan <?= htmlspecialchars(addslashes($p['name'])) ?>?')">
            <input type="hidden" name="action" value="delete_plan">
            <input type="hidden" name="plan_id" value="<?= $p['id'] ?>">
            <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 touch-target">Delete</button>
          </form>
        <?php endif; ?>
      </div>

    </div>
  <?php endforeach; ?>
</div>

<!-- Create / Edit Plan Modal -->
<div id="planModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4">
  <div class="app-card w-full max-w-xl p-5 sm:p-6 rounded-3xl shadow-2xl relative max-h-[90vh] overflow-y-auto bg-white">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
      <h3 id="planModalTitle" class="text-base font-black text-slate-900">Create New Subscription Plan</h3>
      <button onclick="document.getElementById('planModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-xl">&times;</button>
    </div>

    <form method="POST" action="/admin/plans.php" data-no-ajax class="space-y-4">
      <input type="hidden" name="action" value="save_plan">
      <input type="hidden" name="plan_id" id="modalPlanId" value="0">

      <div>
        <label class="block text-xs font-black uppercase text-slate-700 mb-1">Plan Name *</label>
        <input type="text" name="name" id="modalPlanName" required placeholder="e.g. Gold VIP Plan"
               class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Price (₹) *</label>
          <input type="number" step="0.01" name="price" id="modalPlanPrice" required placeholder="499.00"
                 class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
        </div>

        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Product Catalog Limit *</label>
          <input type="number" name="product_limit" id="modalPlanLimit" required value="50"
                 class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
        </div>
      </div>

      <div class="flex items-center space-x-2 pt-1">
        <input type="checkbox" name="is_default" id="modalPlanDefault" value="1" class="rounded text-amber-600 w-4 h-4">
        <label for="modalPlanDefault" class="text-xs font-bold text-slate-700">Set as Default Post-Trial Plan (Free tier)</label>
      </div>

      <!-- Feature Checklist Grid (Mobile Responsive Checkboxes) -->
      <div class="pt-3 border-t border-slate-100">
        <label class="block text-xs font-black uppercase text-slate-700 mb-2">Feature Access Capabilities (Checklist)</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 bg-slate-50 p-3.5 rounded-2xl border border-slate-200 max-h-60 overflow-y-auto">
          <?php foreach (FEATURE_REGISTRY as $fk => $info): ?>
            <label class="flex items-start space-x-3 p-2 rounded-xl bg-white border border-slate-100 hover:border-amber-300 cursor-pointer touch-target">
              <input type="checkbox" name="features[]" value="<?= $fk ?>" id="chk_<?= $fk ?>" class="mt-1 rounded text-amber-600 focus:ring-amber-400 w-4 h-4 shrink-0">
              <div>
                <span class="text-xs font-bold text-slate-900 block"><?= htmlspecialchars($info['name']) ?></span>
                <span class="text-[10px] text-slate-500 block leading-tight mt-0.5"><?= htmlspecialchars($info['desc']) ?></span>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row justify-end gap-2">
        <button type="button" onclick="document.getElementById('planModal').classList.add('hidden')" class="w-full sm:w-auto px-4 py-3 text-xs font-bold text-slate-500">Cancel</button>
        <button type="submit" class="w-full sm:w-auto px-5 py-3.5 bg-slate-900 text-white font-black text-xs rounded-xl shadow-md touch-target">Save Subscription Plan</button>
      </div>
    </form>
  </div>
</div>

<script>
const plansData = <?= json_encode(array_column($plans, null, 'id'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const planFeaturesMap = <?= json_encode($planFeaturesMap, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function openPlanModal() {
  document.getElementById('planModalTitle').innerText = 'Create New Subscription Plan';
  document.getElementById('modalPlanId').value = 0;
  document.getElementById('modalPlanName').value = '';
  document.getElementById('modalPlanPrice').value = '';
  document.getElementById('modalPlanLimit').value = 30;
  document.getElementById('modalPlanDefault').checked = false;
  
  document.querySelectorAll('input[name="features[]"]').forEach(chk => chk.checked = false);
  document.getElementById('planModal').classList.remove('hidden');
}

function editPlan(planId) {
  const plan = plansData[planId];
  if (!plan) return;
  const features = planFeaturesMap[planId] || [];

  document.getElementById('planModalTitle').innerText = 'Edit Plan: ' + plan.name;
  document.getElementById('modalPlanId').value = plan.id;
  document.getElementById('modalPlanName').value = plan.name;
  document.getElementById('modalPlanPrice').value = plan.price;
  document.getElementById('modalPlanLimit').value = plan.product_limit;
  document.getElementById('modalPlanDefault').checked = parseInt(plan.is_default) === 1;

  document.querySelectorAll('input[name="features[]"]').forEach(chk => {
    chk.checked = features.includes(chk.value);
  });

  document.getElementById('planModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
