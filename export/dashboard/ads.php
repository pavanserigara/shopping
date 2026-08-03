<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_tenant_auth();

$tenantId = get_logged_tenant_id();
$pdo      = getDBConnection();

$tCheckStmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id WHERE t.id = ?");
$tCheckStmt->execute([$tenantId]);
$tenantInfo = $tCheckStmt->fetch();

$hasAdsFeature       = tenant_has_feature($pdo, $tenantInfo, 'shop_ads');
$hasAnalyticsFeature = tenant_has_feature($pdo, $tenantInfo, 'ad_analytics');

$errors = [];

// Handle Ad Creation / Updates / Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasAdsFeature) {
        respond_flash('error', "Ad banners feature is locked on your current plan.", '/dashboard/ads.php');
    }

    $action = $_POST['action'] ?? '';

    // Action 1: Upload New Ad Banner
    if ($action === 'create_ad') {
        $title     = trim($_POST['title'] ?? '');
        $type      = $_POST['type'] ?? 'banner';
        $linkUrl   = trim($_POST['link_url'] ?? '');
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $endDate   = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $isActive  = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if (!in_array($type, ['banner', 'mid_page'])) {
            $type = 'banner';
        }

        // Image upload handling
        $imageUrl = '';
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/' . $tenantId . '/ads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($fileExt, $allowedExts)) {
                $filename = 'ad_' . time() . '_' . uniqid() . '.' . $fileExt;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['ad_image']['tmp_name'], $targetFile)) {
                    $imageUrl = '/uploads/' . $tenantId . '/ads/' . $filename;
                } else {
                    $errors[] = "Failed to upload image file.";
                }
            } else {
                $errors[] = "Invalid image format. Allowed: JPG, PNG, WEBP, GIF.";
            }
        } else {
            $errors[] = "Please select an ad image file to upload.";
        }

        if (empty($errors) && !empty($imageUrl)) {
            $stmt = $pdo->prepare("
                INSERT INTO ads (tenant_id, title, type, image_url, link_url, start_date, end_date, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$tenantId, $title, $type, $imageUrl, $linkUrl, $startDate, $endDate, $isActive]);
            respond_flash('success', "Ad banner created successfully!", '/dashboard/ads.php', ['reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/dashboard/ads.php');
        }
    }

    // Action 2: Toggle Active Status
    if ($action === 'toggle_active') {
        $adId = (int)($_POST['ad_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT is_active FROM ads WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$adId, $tenantId]);
        $ad = $stmt->fetch();
        if ($ad) {
            $newActive = $ad['is_active'] ? 0 : 1;
            $update = $pdo->prepare("UPDATE ads SET is_active = ? WHERE id = ? AND tenant_id = ?");
            $update->execute([$newActive, $adId, $tenantId]);
            respond_flash('success', "Ad status updated.", '/dashboard/ads.php', ['reload' => true]);
        }
    }

    // Action 3: Delete Ad
    if ($action === 'delete_ad') {
        $adId = (int)($_POST['ad_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM ads WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$adId, $tenantId]);
        respond_flash('success', "Ad deleted successfully.", '/dashboard/ads.php', ['reload' => true]);
    }
}

$pageTitle = "Ad Banners & Analytics — LocalShopOS";
require_once __DIR__ . '/header.php';

if (!$hasAdsFeature) {
    render_locked_feature_notice('shop_ads');
    require_once __DIR__ . '/footer.php';
    exit;
}

// Fetch All Ads for logged-in Tenant with View Impression Analytics
$adsStmt = $pdo->prepare("
    SELECT a.*, 
           (SELECT COUNT(*) FROM ad_views WHERE ad_id = a.id) as total_views,
           (SELECT COUNT(*) FROM ad_views WHERE ad_id = a.id AND DATE(viewed_at) = CURDATE()) as today_views
    FROM ads a 
    WHERE a.tenant_id = ? 
    ORDER BY a.id DESC
");
$adsStmt->execute([$tenantId]);
$ads = $adsStmt->fetchAll();

// Total impression count for tenant
$totImpStmt = $pdo->prepare("SELECT COUNT(*) FROM ad_views WHERE tenant_id = ?");
$totImpStmt->execute([$tenantId]);
$totalImpressions = (int)$totImpStmt->fetchColumn();
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-xl sm:text-2xl font-black text-slate-900">Promotional Ad Banners & Analytics</h1>
    <p class="text-xs text-slate-600 mt-0.5">Manage customer-facing promotional banners and track real-time view impressions.</p>
  </div>
  
  <button onclick="document.getElementById('adFormModal').classList.remove('hidden')" 
          class="px-4 py-2.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-sm hover:scale-[1.02] transition-all flex items-center space-x-1.5 self-start">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
    <span>Upload New Banner Ad</span>
  </button>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs space-y-1">
    <?php foreach ($errors as $err): ?>
      <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Analytics Overview Card (Gated by ad_analytics feature key) -->
<div class="app-card p-5 rounded-2xl mb-8 bg-white border border-slate-200 shadow-sm flex items-center justify-between relative overflow-hidden">
  <div class="flex items-center space-x-3">
    <div class="w-10 h-10 rounded-xl bg-brand-100 text-slate-900 font-black text-xl flex items-center justify-center shrink-0">
      📊
    </div>
    <div>
      <h3 class="text-xs font-black uppercase text-slate-500">Total Store Banner Impressions</h3>
      <div class="text-2xl font-black text-slate-900 mt-0.5">
        <?php if ($hasAnalyticsFeature): ?>
          <?= number_format($totalImpressions) ?> Views
        <?php else: ?>
          <span class="text-slate-400 font-bold text-lg">🔒 Gated on Free Plan</span>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (!$hasAnalyticsFeature): ?>
    <a href="/dashboard/plans.php" class="px-3 py-1.5 bg-brand-100 text-amber-900 font-black text-xs rounded-xl border border-brand-300 hover:bg-brand-200">
      Upgrade to Unlock Analytics &rarr;
    </a>
  <?php endif; ?>
</div>

<!-- Active Ads List Grid -->
<div class="app-card rounded-2xl overflow-hidden bg-white border border-slate-200">
  <div class="p-5 border-b border-slate-100 flex items-center justify-between">
    <h3 class="text-base font-black text-slate-900">Your Store Promotional Ads</h3>
    <span class="text-xs font-bold text-slate-500"><?= count($ads) ?> Total Banners</span>
  </div>

  <?php if (empty($ads)): ?>
    <div class="text-center py-16">
      <div class="text-5xl mb-3">🎯</div>
      <h4 class="text-base font-black text-slate-900">No promotional ads uploaded yet</h4>
      <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Upload top header carousel banners or mid-page promotional ad blocks to boost customer sales on your shop.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
      <?php foreach ($ads as $ad): ?>
        <div class="app-card rounded-xl overflow-hidden border border-slate-200 flex flex-col justify-between bg-white shadow-sm">
          <div>
            <div class="h-36 bg-slate-900 overflow-hidden relative">
              <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="w-full h-full object-cover">
              <span class="absolute top-2 left-2 px-2.5 py-1 text-[10px] font-black uppercase rounded-full shadow-md <?= $ad['type'] === 'banner' ? 'bg-brand-500 text-slate-950' : 'bg-slate-800 text-white' ?>">
                <?= $ad['type'] === 'banner' ? 'Header Carousel Banner' : 'Mid-Grid Listing Ad' ?>
              </span>
            </div>

            <div class="p-4 space-y-2">
              <h4 class="font-black text-slate-900 text-sm"><?= htmlspecialchars($ad['title'] ?: 'Untitled Banner') ?></h4>

              <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-bold">Ad Impressions:</span>
                <?php if ($hasAnalyticsFeature): ?>
                  <span class="font-black text-slate-900">
                    <strong class="text-emerald-700 font-black"><?= number_format($ad['total_views']) ?></strong> views
                    <span class="text-[10px] text-amber-700 font-bold block text-right">(<?= (int)$ad['today_views'] ?> today)</span>
                  </span>
                <?php else: ?>
                  <span class="text-[11px] text-slate-400 font-bold">🔒 Locked</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <form method="POST" action="/dashboard/ads.php" data-no-ajax class="inline">
              <input type="hidden" name="action" value="toggle_active">
              <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
              <button type="submit" class="text-xs font-black px-3 py-1 rounded-full border transition-all <?= $ad['is_active'] ? 'bg-emerald-100 border-emerald-300 text-emerald-800' : 'bg-slate-200 border-slate-300 text-slate-600' ?>">
                <?= $ad['is_active'] ? '● Active' : '○ Disabled' ?>
              </button>
            </form>

            <form method="POST" action="/dashboard/ads.php" data-no-ajax class="inline" onsubmit="return confirm('Delete ad banner?')">
              <input type="hidden" name="action" value="delete_ad">
              <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
              <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Upload Ad Modal -->
<div id="adFormModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-lg p-6 rounded-2xl shadow-2xl relative bg-white">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
      <h3 class="text-base font-black text-slate-900">Upload Store Banner Ad</h3>
      <button onclick="document.getElementById('adFormModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>

    <form method="POST" action="/dashboard/ads.php" enctype="multipart/form-data" data-no-ajax class="space-y-4">
      <input type="hidden" name="action" value="create_ad">

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Banner Title</label>
        <input type="text" name="title" placeholder="e.g. Festival Super Saver 20% OFF" required
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Placement Type *</label>
        <select name="type" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
          <option value="banner">Top Carousel Header Banner</option>
          <option value="mid_page">Mid-Grid Listing Ad Block</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Banner Image File *</label>
        <input type="file" name="ad_image" accept="image/*" required
               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-500 file:text-slate-950">
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Destination Link URL (Optional)</label>
        <input type="url" name="link_url" placeholder="https://..."
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
      </div>

      <div class="flex items-center space-x-2 pt-2">
        <input type="checkbox" name="is_active" value="1" checked id="is_active_chk" class="rounded text-brand-600 focus:ring-brand-400">
        <label for="is_active_chk" class="text-xs font-bold text-slate-700">Activate ad banner in store immediately</label>
      </div>

      <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
        <button type="button" onclick="document.getElementById('adFormModal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500">Cancel</button>
        <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white font-black text-xs rounded-xl shadow-md">Upload & Save Banner</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
