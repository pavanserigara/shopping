<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_super_admin_auth();

$pdo = getDBConnection();
$errors = [];

// Handle Global Ad Upload / Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Upload New Global Platform Ad
    if ($action === 'create_global_ad') {
        $title     = trim($_POST['title'] ?? '');
        $placement = $_POST['placement'] ?? 'banner';
        $linkUrl   = trim($_POST['link_url'] ?? '');
        $startDate = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $endDate   = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $isActive  = isset($_POST['is_active']) ? 1 : 0;

        if (!in_array($placement, ['banner', 'mid_page'])) {
            $placement = 'banner';
        }

        $imageUrl = '';
        if (isset($_FILES['ad_image']) && $_FILES['ad_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/global_ads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileExt = strtolower(pathinfo($_FILES['ad_image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            
            if (in_array($fileExt, $allowedExts)) {
                $filename = 'global_ad_' . time() . '_' . uniqid() . '.' . $fileExt;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['ad_image']['tmp_name'], $targetFile)) {
                    $imageUrl = '/uploads/global_ads/' . $filename;
                } else {
                    $errors[] = "Failed to upload image file.";
                }
            } else {
                $errors[] = "Invalid image format. Allowed: JPG, PNG, WEBP, GIF.";
            }
        } else {
            $errors[] = "Please select an image file to upload.";
        }

        if (empty($errors) && !empty($imageUrl)) {
            $stmt = $pdo->prepare("
                INSERT INTO global_ads (title, placement, image_url, link_url, start_date, end_date, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $placement, $imageUrl, $linkUrl, $startDate, $endDate, $isActive]);
            respond_flash('success', "Platform-wide global ad banner created successfully!", '/admin/global_ads.php', ['reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/admin/global_ads.php');
        }
    }

    // Action 2: Toggle Active Status
    if ($action === 'toggle_active') {
        $adId = (int)($_POST['ad_id'] ?? 0);
        $stmt = $pdo->prepare("SELECT is_active FROM global_ads WHERE id = ?");
        $stmt->execute([$adId]);
        $ad = $stmt->fetch();
        if ($ad) {
            $newActive = $ad['is_active'] ? 0 : 1;
            $update = $pdo->prepare("UPDATE global_ads SET is_active = ? WHERE id = ?");
            $update->execute([$newActive, $adId]);
            respond_flash('success', "Global ad status updated.", '/admin/global_ads.php', ['reload' => true]);
        }
    }

    // Action 3: Delete Global Ad
    if ($action === 'delete_ad') {
        $adId = (int)($_POST['ad_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM global_ads WHERE id = ?");
        $stmt->execute([$adId]);
        respond_flash('success', "Global ad deleted.", '/admin/global_ads.php', ['reload' => true]);
    }
}

// Fetch All Global Ads with View Analytics
$adsStmt = $pdo->query("
    SELECT g.*, 
           (SELECT COUNT(*) FROM global_ad_views WHERE global_ad_id = g.id) as total_views,
           (SELECT COUNT(*) FROM global_ad_views WHERE global_ad_id = g.id AND DATE(viewed_at) = CURDATE()) as today_views
    FROM global_ads g 
    ORDER BY g.id DESC
");
$globalAds = $adsStmt->fetchAll();

$totalGlobalViews = (int)$pdo->query("SELECT COUNT(*) FROM global_ad_views")->fetchColumn();

$pageTitle = "Platform-Wide Ads & Analytics — Super Admin";
require_once __DIR__ . '/header.php';
?>

<?php display_flash(); ?>

<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
  
  <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h1 class="text-2xl font-black text-slate-900">Platform-Wide Global Ads & Impression Analytics</h1>
      <p class="text-xs text-slate-500 mt-1">Super admin promotional banners rendered across <strong>every</strong> tenant storefront on LocalShopOS</p>
    </div>
    <button onclick="document.getElementById('adFormModal').classList.remove('hidden')" 
            class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow-md flex items-center space-x-1.5 self-start">
      <span>+ Upload New Global Ad</span>
    </button>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs space-y-1">
      <?php foreach ($errors as $err): ?>
        <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Global Ad Impressions Counter -->
  <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6 flex items-center justify-between">
    <div class="flex items-center space-x-3">
      <div class="w-10 h-10 rounded-xl bg-brand-500 text-slate-950 font-black text-lg flex items-center justify-center shrink-0">
        🌍
      </div>
      <div>
        <h3 class="text-xs font-black uppercase text-slate-500">Total Platform Global Ad Impressions</h3>
        <div class="text-2xl font-black text-slate-900"><?= number_format($totalGlobalViews) ?> Views</div>
      </div>
    </div>
    <span class="text-xs font-bold text-amber-800 bg-brand-100 px-3 py-1 rounded-full border border-brand-300">Platform Monetization Channel</span>
  </div>

  <!-- Ads Cards Grid -->
  <div class="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-sm">
    <div class="p-5 border-b border-slate-100">
      <h3 class="text-base font-black text-slate-900">Active & Scheduled Global Platform Ads</h3>
    </div>

    <?php if (empty($globalAds)): ?>
      <div class="text-center py-16">
        <div class="text-5xl mb-3">📢</div>
        <h4 class="text-base font-black text-slate-900">No global ads uploaded</h4>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Upload platform-wide header carousels or mid-page ads that display across all merchant storefronts.</p>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
        <?php foreach ($globalAds as $ad): ?>
          <div class="app-card rounded-xl overflow-hidden border border-slate-200 flex flex-col justify-between bg-white shadow-sm">
            <div>
              <div class="h-36 bg-slate-900 overflow-hidden relative">
                <img src="<?= htmlspecialchars($ad['image_url']) ?>" class="w-full h-full object-cover">
                <span class="absolute top-2 left-2 px-2.5 py-1 text-[10px] font-black uppercase rounded-full shadow-md <?= $ad['placement'] === 'banner' ? 'bg-brand-500 text-slate-950' : 'bg-amber-600 text-white' ?>">
                  <?= $ad['placement'] === 'banner' ? 'Top Global Header Banner' : 'Global Mid-Grid Ad' ?>
                </span>
              </div>

              <div class="p-4 space-y-2">
                <h4 class="font-black text-slate-900 text-sm"><?= htmlspecialchars($ad['title'] ?: 'Global Platform Banner') ?></h4>
                
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                  <span class="text-slate-500 font-bold">Impression Views:</span>
                  <span class="font-black text-slate-900">
                    <strong class="text-emerald-700 font-black"><?= number_format($ad['total_views']) ?></strong> total
                    <span class="text-[10px] text-amber-700 font-bold block text-right">(<?= (int)$ad['today_views'] ?> today)</span>
                  </span>
                </div>

                <?php if (!empty($ad['link_url'])): ?>
                  <p class="text-xs text-amber-800 font-bold truncate flex items-center pt-1">
                    <svg class="w-3.5 h-3.5 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    <?= htmlspecialchars($ad['link_url']) ?>
                  </p>
                <?php endif; ?>
              </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
              <form method="POST" action="/admin/global_ads.php" data-no-ajax class="inline">
                <input type="hidden" name="action" value="toggle_active">
                <input type="hidden" name="ad_id" value="<?= $ad['id'] ?>">
                <button type="submit" class="text-xs font-black px-3 py-1 rounded-full border transition-all <?= $ad['is_active'] ? 'bg-emerald-100 border-emerald-300 text-emerald-800' : 'bg-slate-200 border-slate-300 text-slate-600' ?>">
                  <?= $ad['is_active'] ? '● Active Everywhere' : '○ Inactive' ?>
                </button>
              </form>

              <form method="POST" action="/admin/global_ads.php" data-no-ajax class="inline" onsubmit="return confirm('Delete global ad?')">
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

</main>

<!-- Upload Ad Modal -->
<div id="adFormModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-lg p-6 rounded-2xl shadow-2xl relative bg-white">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
      <h3 class="text-base font-black text-slate-900">Upload Global Platform-Wide Ad Banner</h3>
      <button onclick="document.getElementById('adFormModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>

    <form method="POST" action="/admin/global_ads.php" enctype="multipart/form-data" data-no-ajax class="space-y-4">
      <input type="hidden" name="action" value="create_global_ad">

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Campaign Label / Title</label>
        <input type="text" name="title" placeholder="e.g. National Super Deals Sale" required
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Ad Placement Type *</label>
        <select name="placement" required class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
          <option value="banner">Top Carousel Header Banner (Appears First in Carousel)</option>
          <option value="mid_page">Mid-Grid Listing Ad Block</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Banner Image File *</label>
        <input type="file" name="ad_image" accept="image/*" required onchange="previewAdImage(event)"
               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-500 file:text-slate-950">
        <div id="adImgPreviewContainer" class="hidden mt-3 h-28 rounded-xl overflow-hidden bg-slate-900 border border-slate-200">
          <img id="adImgPreview" class="w-full h-full object-cover">
        </div>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Destination Link URL (Optional)</label>
        <input type="url" name="link_url" placeholder="https://..."
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
      </div>

      <div class="flex items-center space-x-2 pt-2">
        <input type="checkbox" name="is_active" value="1" checked id="is_active_chk" class="rounded text-brand-600 focus:ring-brand-400">
        <label for="is_active_chk" class="text-xs font-bold text-slate-700">Activate ad banner across all shops immediately</label>
      </div>

      <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
        <button type="button" onclick="document.getElementById('adFormModal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500">Cancel</button>
        <button type="submit" class="px-5 py-2.5 bg-slate-900 text-white font-black text-xs rounded-xl shadow-md">Upload & Publish Ad</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewAdImage(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('adImgPreview').src = e.target.result;
      document.getElementById('adImgPreviewContainer').classList.remove('hidden');
    }
    reader.readAsDataURL(file);
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
