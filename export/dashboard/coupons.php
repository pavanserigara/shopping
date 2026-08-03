<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_tenant_auth();

$tenantId = get_logged_tenant_id();
$pdo      = getDBConnection();

$tStmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id WHERE t.id = ?");
$tStmt->execute([$tenantId]);
$tenantInfo = $tStmt->fetch();

$pageTitle = "Discount Coupons — Merchant Dashboard";
require_once __DIR__ . '/header.php';

if (!tenant_has_feature($pdo, $tenantInfo, 'coupons')) {
    render_locked_feature_notice('coupons');
    require_once __DIR__ . '/footer.php';
    exit;
}

// Fetch all coupons for this tenant
$couponsStmt = $pdo->prepare("SELECT * FROM coupons WHERE tenant_id = ? ORDER BY id DESC");
$couponsStmt->execute([$tenantId]);
$coupons = $couponsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="mb-6">
  <h1 class="text-xl sm:text-2xl font-black text-slate-900">Discount Coupons & Offers</h1>
  <p class="text-xs text-slate-500 font-medium mt-1">Create promo codes for your WhatsApp storefront. Changes apply instantly — no page refresh needed.</p>
</div>

<div class="flex flex-col lg:flex-row gap-6 items-start">

  <!-- ── LEFT: Create Form ── -->
  <div class="w-full lg:w-80 flex-shrink-0">
    <div class="app-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
      <h3 class="text-sm font-black text-slate-900 mb-4 flex items-center gap-2">
        <span class="text-base">🎟️</span> Create New Coupon
      </h3>

      <!-- Inline feedback -->
      <div id="couponMsg" class="hidden mb-3 px-3 py-2 rounded-xl text-xs font-bold"></div>

      <form id="couponCreateForm" data-no-ajax class="space-y-4">
        <div>
          <label class="block text-[10px] font-black uppercase text-slate-600 mb-1 tracking-wide">Coupon Code *</label>
          <input id="fCode" type="text" name="code" required placeholder="e.g. SAVE20"
                 class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-black uppercase text-slate-900 focus:ring-2 focus:ring-amber-400 focus:border-amber-400 outline-none transition-all">
          <p class="text-[9px] text-slate-400 mt-1">Letters, numbers, hyphens only. Max 50 chars.</p>
        </div>

        <div>
          <label class="block text-[10px] font-black uppercase text-slate-600 mb-1 tracking-wide">Discount Type *</label>
          <select id="fType" name="discount_type" class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-amber-400 outline-none">
            <option value="percentage">Percentage (%)</option>
            <option value="flat">Flat Amount (₹)</option>
          </select>
        </div>

        <div>
          <label class="block text-[10px] font-black uppercase text-slate-600 mb-1 tracking-wide">Discount Value *</label>
          <input id="fValue" type="number" step="0.01" min="0.01" name="discount_value" required placeholder="e.g. 10"
                 class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-amber-400 outline-none transition-all">
          <p id="fValueHint" class="text-[9px] text-slate-400 mt-1">Enter percentage (e.g. 10 = 10% off)</p>
        </div>

        <div>
          <label class="block text-[10px] font-black uppercase text-slate-600 mb-1 tracking-wide">Min. Order Amount (₹)</label>
          <input id="fMin" type="number" step="0.01" min="0" name="min_order_amount" value="0"
                 class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-amber-400 outline-none transition-all">
        </div>

        <div>
          <label class="block text-[10px] font-black uppercase text-slate-600 mb-1 tracking-wide">Expiry Date (optional)</label>
          <input id="fExpiry" type="date" name="expires_at"
                 class="w-full px-3 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-amber-400 outline-none transition-all">
        </div>

        <button id="couponSubmitBtn" type="submit"
                class="w-full py-2.5 btn-cta text-white text-xs font-black rounded-xl shadow-md hover:shadow-lg transition-all">
          + Add Coupon Code
        </button>
      </form>
    </div>
  </div>

  <!-- ── RIGHT: Coupon List ── -->
  <div class="flex-1 min-w-0">
    <div id="couponList">
      <?php if (empty($coupons)): ?>
        <div id="emptyCouponState" class="app-card p-12 rounded-2xl bg-white border border-slate-100 text-center">
          <div class="text-4xl mb-3">🎟️</div>
          <h3 class="text-sm font-black text-slate-900">No coupons yet</h3>
          <p class="text-xs text-slate-500 mt-1">Fill the form on the left to create your first promo code.</p>
        </div>
      <?php else: ?>
        <div id="couponGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
          <?php foreach ($coupons as $c): ?>
            <?= renderCouponCard($c) ?>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php
function renderCouponCard(array $c): string {
    $activeClass = $c['is_active'] ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-500';
    $activeLabel = $c['is_active'] ? 'Active' : 'Disabled';
    $toggleLabel = $c['is_active'] ? 'Disable' : 'Enable';
    $discountDisplay = $c['discount_type'] === 'percentage'
        ? (float)$c['discount_value'] . '%'
        : '₹' . number_format($c['discount_value'], 2);
    $expires = !empty($c['expires_at']) ? date('d M Y', strtotime($c['expires_at'])) : 'No expiry';
    $code = htmlspecialchars($c['code']);
    $id   = (int)$c['id'];

    return <<<HTML
    <div id="coupon-card-{$id}" class="app-card p-5 rounded-2xl bg-white border border-slate-200 flex flex-col justify-between space-y-4 hover:shadow-md transition-shadow">
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <span class="px-3 py-1 font-mono font-black text-xs rounded-xl bg-amber-100 text-amber-900 border border-amber-300 tracking-wider">{$code}</span>
          <span id="coupon-status-{$id}" class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full {$activeClass}">{$activeLabel}</span>
        </div>
        <div class="text-slate-900">
          <span class="text-2xl font-black">{$discountDisplay}</span>
          <span class="text-xs text-slate-500 font-bold uppercase ml-1">OFF</span>
        </div>
        <p class="text-xs text-slate-500 font-medium">Min. order: <strong class="text-slate-700">₹{$c['min_order_amount']}</strong></p>
        <p class="text-[9px] text-slate-400 font-medium">Expires: {$expires}</p>
      </div>
      <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
        <button onclick="couponToggle({$id})" id="coupon-toggle-btn-{$id}"
                class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
          {$toggleLabel}
        </button>
        <button onclick="couponDelete({$id}, '{$code}')"
                class="px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
          Delete
        </button>
      </div>
    </div>
HTML;
}
?>

<script>
const TENANT_ID = <?= (int)$tenantId ?>;

// ── Update discount value hint based on type ──
document.getElementById('fType').addEventListener('change', function() {
  const hint = document.getElementById('fValueHint');
  hint.textContent = this.value === 'percentage'
    ? 'Enter percentage (e.g. 10 = 10% off)'
    : 'Enter flat ₹ amount (e.g. 50 = ₹50 off)';
});

// ── Show feedback in the form panel ──
function showCouponMsg(text, isError) {
  const el = document.getElementById('couponMsg');
  el.textContent = text;
  el.className = 'mb-3 px-3 py-2 rounded-xl text-xs font-bold ' +
    (isError ? 'bg-rose-50 border border-rose-200 text-rose-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700');
  el.classList.remove('hidden');
}

// ── Inject a new coupon card into the grid (no page reload) ──
function injectCouponCard(coupon) {
  // Remove empty state if present
  const empty = document.getElementById('emptyCouponState');
  if (empty) empty.remove();

  let grid = document.getElementById('couponGrid');
  if (!grid) {
    grid = document.createElement('div');
    grid.id = 'couponGrid';
    grid.className = 'grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4';
    document.getElementById('couponList').appendChild(grid);
  }

  const expires = coupon.expires_at
    ? new Date(coupon.expires_at).toLocaleDateString('en-IN', {day:'2-digit', month:'short', year:'numeric'})
    : 'No expiry';
  const discountDisplay = coupon.discount_type === 'percentage'
    ? coupon.discount_value + '%'
    : '₹' + parseFloat(coupon.discount_value).toFixed(2);

  const card = document.createElement('div');
  card.id = 'coupon-card-' + coupon.id;
  card.className = 'app-card p-5 rounded-2xl bg-white border border-slate-200 flex flex-col justify-between space-y-4 hover:shadow-md transition-shadow';
  card.innerHTML = `
    <div class="space-y-2">
      <div class="flex items-center justify-between">
        <span class="px-3 py-1 font-mono font-black text-xs rounded-xl bg-amber-100 text-amber-900 border border-amber-300 tracking-wider">${coupon.code}</span>
        <span id="coupon-status-${coupon.id}" class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-emerald-100 text-emerald-800">Active</span>
      </div>
      <div class="text-slate-900">
        <span class="text-2xl font-black">${discountDisplay}</span>
        <span class="text-xs text-slate-500 font-bold uppercase ml-1">OFF</span>
      </div>
      <p class="text-xs text-slate-500 font-medium">Min. order: <strong class="text-slate-700">₹${parseFloat(coupon.min_order_amount).toFixed(2)}</strong></p>
      <p class="text-[9px] text-slate-400 font-medium">Expires: ${expires}</p>
    </div>
    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
      <button onclick="couponToggle(${coupon.id})" id="coupon-toggle-btn-${coupon.id}"
              class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors">
        Disable
      </button>
      <button onclick="couponDelete(${coupon.id}, '${coupon.code}')"
              class="px-3 py-1.5 rounded-lg text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors">
        Delete
      </button>
    </div>`;
  grid.prepend(card);
}

// ── Create coupon form submit ──
document.getElementById('couponCreateForm').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('couponSubmitBtn');
  btn.disabled = true;
  btn.textContent = '⏳ Saving...';
  document.getElementById('couponMsg').classList.add('hidden');

  const payload = {
    code:              document.getElementById('fCode').value,
    discount_type:     document.getElementById('fType').value,
    discount_value:    document.getElementById('fValue').value,
    min_order_amount:  document.getElementById('fMin').value,
    expires_at:        document.getElementById('fExpiry').value || null,
    is_active:         1,
  };

  try {
    const res  = await fetch('/api/coupon_create.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    let data;
    try {
      data = await res.json();
    } catch (parseErr) {
      const raw = await res.text().catch(() => '');
      showCouponMsg('Server error — could not parse response: ' + raw.slice(0, 100), true);
      return;
    }

    if (data.success) {
      showCouponMsg('✓ ' + data.message, false);
      injectCouponCard(data.coupon);
      this.reset(); // clear form
    } else {
      showCouponMsg('⚠ ' + data.error, true);
    }
  } catch (netErr) {
    showCouponMsg('Network error — please check your connection.', true);
  } finally {
    btn.disabled = false;
    btn.textContent = '+ Add Coupon Code';
  }
});

// ── Toggle coupon active state ──
async function couponToggle(id) {
  const btn = document.getElementById('coupon-toggle-btn-' + id);
  if (btn) { btn.disabled = true; btn.textContent = '...'; }

  try {
    const res  = await fetch('/api/coupon_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'toggle', coupon_id: id }),
    });
    const data = await res.json();

    if (data.success) {
      const statusEl = document.getElementById('coupon-status-' + id);
      if (data.is_active) {
        statusEl.textContent = 'Active';
        statusEl.className = 'px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-emerald-100 text-emerald-800';
        if (btn) btn.textContent = 'Disable';
      } else {
        statusEl.textContent = 'Disabled';
        statusEl.className = 'px-2 py-0.5 text-[9px] font-black uppercase rounded-full bg-slate-100 text-slate-500';
        if (btn) btn.textContent = 'Enable';
      }
      if (typeof showAdminToast === 'function') showAdminToast(data.message);
    } else {
      alert(data.error);
    }
  } catch(err) {
    alert('Network error. Please try again.');
  } finally {
    if (btn) btn.disabled = false;
  }
}

// ── Delete coupon ──
async function couponDelete(id, code) {
  if (!confirm('Delete coupon "' + code + '"? This cannot be undone.')) return;

  try {
    const res  = await fetch('/api/coupon_action.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'delete', coupon_id: id }),
    });
    const data = await res.json();

    if (data.success) {
      const card = document.getElementById('coupon-card-' + id);
      if (card) {
        card.style.transition = 'opacity 0.3s';
        card.style.opacity = '0';
        setTimeout(() => {
          card.remove();
          // Show empty state if grid is now empty
          const grid = document.getElementById('couponGrid');
          if (grid && grid.children.length === 0) {
            grid.remove();
            const list = document.getElementById('couponList');
            list.innerHTML = `<div id="emptyCouponState" class="app-card p-12 rounded-2xl bg-white border border-slate-100 text-center">
              <div class="text-4xl mb-3">🎟️</div>
              <h3 class="text-sm font-black text-slate-900">No coupons yet</h3>
              <p class="text-xs text-slate-500 mt-1">Fill the form on the left to create your first promo code.</p>
            </div>`;
          }
        }, 300);
      }
      if (typeof showAdminToast === 'function') showAdminToast(data.message);
    } else {
      alert(data.error);
    }
  } catch(err) {
    alert('Network error. Please try again.');
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
