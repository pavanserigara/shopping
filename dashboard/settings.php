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

$stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->execute([$tenantId]);
$tenantInfo = $stmt->fetch();

$isOpen         = (int)($tenantInfo['is_open'] ?? 1);
$hasLogoFeature = tenant_has_feature($pdo, $tenantInfo, 'shop_logo_upload');
$errors         = [];

// Handle Settings Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_store_status') {
        $stmt = $pdo->prepare("SELECT is_open FROM tenants WHERE id = ?");
        $stmt->execute([$tenantId]);
        $curr = (int)$stmt->fetchColumn();
        $newStatus = $curr ? 0 : 1;
        $stmt = $pdo->prepare("UPDATE tenants SET is_open = ? WHERE id = ?");
        $stmt->execute([$newStatus, $tenantId]);
        $msg = "Store status updated to: " . ($newStatus ? "OPEN (Customers can order)" : "CLOSED (Orders paused)");
        respond_flash('success', $msg, '/dashboard/settings.php', ['is_open' => $newStatus, 'reload' => true]);
    }

    if ($action === 'update_profile') {
        $shopName       = trim($_POST['shop_name'] ?? '');
        $whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
        $category       = trim($_POST['category'] ?? '');

        if (empty($shopName)) $errors[] = "Shop name is required.";
        if (empty($whatsappNumber)) $errors[] = "WhatsApp number is required.";

        // Logo Upload Handling (gated by shop_logo_upload)
        $logoUrl = $tenantInfo['logo_url'] ?? null;
        if ($hasLogoFeature && isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/logos/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $ext = strtolower(pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];

            if (in_array($ext, $allowedExts)) {
                $filename = 'logo_tenant_' . $tenantId . '_' . time() . '.' . $ext;
                $targetFile = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['logo_image']['tmp_name'], $targetFile)) {
                    $logoUrl = '/uploads/logos/' . $filename;
                } else {
                    $errors[] = "Failed to upload logo image file.";
                }
            } else {
                $errors[] = "Invalid logo format. Allowed: JPG, PNG, WEBP, SVG, GIF.";
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE tenants SET shop_name = ?, whatsapp_number = ?, category = ?, logo_url = ? WHERE id = ?");
            $stmt->execute([$shopName, $whatsappNumber, $category, $logoUrl, $tenantId]);
            $_SESSION['shop_name'] = $shopName;
            $msg = "Store profile updated successfully.";
            respond_flash('success', $msg, '/dashboard/settings.php', ['logo_url' => $logoUrl, 'reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/dashboard/settings.php');
        }
    }

    if ($action === 'update_delivery_settings') {
        $deliveryEnabled  = isset($_POST['delivery_enabled']) ? 1 : 0;
        $deliveryFee      = max(0, (float)($_POST['delivery_fee'] ?? 0));
        $minDeliveryOrder = max(0, (float)($_POST['min_delivery_order'] ?? 0));
        $deliveryAreaNote = trim($_POST['delivery_area_note'] ?? '');
        $thankYouMsg      = trim($_POST['order_thank_you_msg'] ?? '');

        $stmt = $pdo->prepare("
            UPDATE tenants SET 
                delivery_enabled = ?, 
                delivery_fee = ?, 
                min_delivery_order = ?, 
                delivery_area_note = ?, 
                order_thank_you_msg = ? 
            WHERE id = ?
        ");
        $stmt->execute([$deliveryEnabled, $deliveryFee, $minDeliveryOrder, $deliveryAreaNote, $thankYouMsg, $tenantId]);
        respond_flash('success', "Delivery & Fulfillment settings saved successfully.", '/dashboard/settings.php', ['reload' => true]);
    }

    if ($action === 'change_password') {
        $currentPass = trim($_POST['current_password'] ?? '');
        $newPass     = trim($_POST['new_password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');

        // Fetch current user from admin_users
        $userId = $_SESSION['user_id'] ?? 0;
        $uStmt  = $pdo->prepare("SELECT * FROM admin_users WHERE id = ? AND tenant_id = ?");
        $uStmt->execute([$userId, $tenantId]);
        $currUser = $uStmt->fetch();

        if (!$currUser || !password_verify($currentPass, $currUser['password_hash'])) {
            respond_flash('error', "Current password entered is incorrect.", '/dashboard/settings.php');
        } elseif (empty($newPass)) {
            respond_flash('error', "New password cannot be empty.", '/dashboard/settings.php');
        } elseif (strlen($newPass) < 6) {
            respond_flash('error', "New password must be at least 6 characters long.", '/dashboard/settings.php');
        } elseif ($newPass !== $confirmPass) {
            respond_flash('error', "New password and confirmation password do not match.", '/dashboard/settings.php');
        } else {
            $newHash = password_hash($newPass, PASSWORD_DEFAULT);
            $upStmt  = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ? AND tenant_id = ?");
            $upStmt->execute([$newHash, $userId, $tenantId]);
            respond_flash('success', "Your account password has been updated successfully!", '/dashboard/settings.php', ['reload' => true]);
        }
    }
}

$pageTitle = "Store Profile & Settings — LocalShopOS";
require_once __DIR__ . '/header.php';

// Re-fetch fresh tenant details
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->execute([$tenantId]);
$tenant = $stmt->fetch();
?>

<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-black text-slate-900">Store Profile & Branding Settings</h1>
    <p class="text-xs text-slate-500 mt-1">Manage shop branding, logo mark, store availability, and contact information</p>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs space-y-1">
    <?php foreach ($errors as $err): ?>
      <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
  
  <!-- Left Column: Open/Closed & General Info -->
  <div class="space-y-6">
    
    <!-- Open / Closed Toggle Switch Card -->
    <div class="app-card p-6 rounded-2xl bg-white border border-slate-200 shadow-sm">
      <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-2">Store Ordering Status</h3>
      <p class="text-xs text-slate-500 mb-4 font-medium">Toggle whether customers can place WhatsApp orders or see a "Closed" message.</p>

      <form method="POST" action="/dashboard/settings.php" data-no-ajax>
        <input type="hidden" name="action" value="toggle_store_status">
        <button type="submit" class="w-full py-3 px-4 rounded-xl text-xs font-black text-white shadow-md flex items-center justify-center space-x-2 transition-all <?= $tenant['is_open'] ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700' ?>">
          <span><?= $tenant['is_open'] ? '🟢 STORE IS CURRENTLY OPEN' : '🔴 STORE IS CLOSED' ?></span>
        </button>
      </form>
    </div>

    <!-- Storefront Quick Link Card -->
    <div class="app-card p-6 rounded-2xl bg-brand-50 border border-brand-200 shadow-sm">
      <h3 class="text-xs font-black text-amber-900 uppercase tracking-wider mb-1">Your Public Shop URL</h3>
      <div class="bg-white p-3 rounded-xl border border-brand-300 font-mono text-xs text-slate-900 font-bold mb-3 truncate">
        http://localhost:8000/<?= htmlspecialchars($tenant['subdomain']) ?>
      </div>
      <a href="/<?=  urlencode($tenant['subdomain']) ?>" target="_blank" 
         class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shadow-sm flex items-center justify-center space-x-1">
        <span>Visit Live Storefront &rarr;</span>
      </a>
    </div>

  </div>

  <!-- Right Column: Profile & Branding Settings Form -->
  <div class="lg:col-span-2">
    <div class="app-card p-6 sm:p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-6">
      
      <h3 class="text-base font-black text-slate-900 border-b border-slate-100 pb-3">Basic Business Information</h3>

      <form method="POST" action="/dashboard/settings.php" enctype="multipart/form-data" data-no-ajax class="space-y-5">
        <input type="hidden" name="action" value="update_profile">

        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Shop Name *</label>
          <input type="text" name="shop_name" value="<?= htmlspecialchars($tenant['shop_name']) ?>" required
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1">WhatsApp Phone Number *</label>
            <div class="flex">
              <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-xs font-bold text-slate-600">+91</span>
              <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($tenant['whatsapp_number']) ?>" required
                     class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-r-xl text-xs font-bold text-slate-900">
            </div>
          </div>

          <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1">Store Category</label>
            <input type="text" name="category" value="<?= htmlspecialchars($tenant['category']) ?>" placeholder="e.g. Kirana & Grocery"
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
          </div>
        </div>

        <!-- Custom Shop Logo Section (Gated by shop_logo_upload) -->
        <div class="pt-4 border-t border-slate-100">
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-black uppercase text-slate-700">Custom Shop Logo</label>
            <?php if (!$hasLogoFeature): ?>
              <span class="text-[10px] font-black uppercase text-amber-800 bg-brand-100 px-2 py-0.5 rounded-md border border-brand-300">🔒 Premium Feature</span>
            <?php endif; ?>
          </div>

          <?php if ($hasLogoFeature): ?>
            <div class="flex items-center space-x-4">
              <div class="w-16 h-16 rounded-2xl bg-brand-100 border border-brand-300 flex items-center justify-center font-black text-slate-900 text-2xl overflow-hidden shrink-0 shadow-sm">
                <?php if (!empty($tenant['logo_url'])): ?>
                  <img id="logoPreview" src="<?= htmlspecialchars($tenant['logo_url']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                  <span id="logoInitial"><?= mb_substr(htmlspecialchars($tenant['shop_name']), 0, 1) ?></span>
                <?php endif; ?>
              </div>

              <div class="flex-1">
                <input type="file" name="logo_image" accept="image/*" onchange="previewLogoFile(event)"
                       class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-500 file:text-slate-950">
                <p class="text-[10px] text-slate-400 mt-1">Recommended: Square PNG/JPG logo (e.g. 500x500px).</p>
              </div>
            </div>
          <?php else: ?>
            <div class="p-4 bg-slate-900 text-white rounded-2xl border border-slate-800 text-xs flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <span class="text-xl">🔒</span>
                <div>
                  <h5 class="font-black text-white">Custom Logo Upload Locked</h5>
                  <p class="text-[11px] text-slate-400">Upgrade to Premium or Gold plan to upload custom store logo branding.</p>
                </div>
              </div>
              <a href="/dashboard/plans.php" class="px-3 py-1.5 bg-brand-500 text-slate-950 font-black text-[11px] rounded-lg shrink-0">Upgrade Plan &rarr;</a>
            </div>
          <?php endif; ?>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
          <button type="submit" class="py-3 px-6 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md transition-all">
            Save Profile Settings
          </button>
        </div>

      </form>
    </div>

    <!-- Delivery & Fulfillment Settings Card -->
    <div class="app-card p-6 sm:p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-6 mt-6">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <div>
          <h3 class="text-base font-black text-slate-900">🚚 Delivery & Order Fulfillment Settings</h3>
          <p class="text-xs text-slate-500 font-medium mt-0.5">Control pickup vs delivery options, delivery fees, and minimum order requirements.</p>
        </div>
      </div>

      <form method="POST" action="/dashboard/settings.php" data-no-ajax class="space-y-5">
        <input type="hidden" name="action" value="update_delivery_settings">

        <!-- Master Delivery Toggle -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-between">
          <div>
            <h4 class="text-xs font-black text-slate-900 uppercase tracking-wider">Enable Home Delivery</h4>
            <p class="text-[11px] text-slate-500 font-medium">When turned OFF, storefront checkout forces Pickup mode only and hides delivery address fields.</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" name="delivery_enabled" value="1" <?= $tenant['delivery_enabled'] ? 'checked' : '' ?> class="sr-only peer">
            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
          </label>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1">Flat Delivery Fee (₹)</label>
            <div class="flex">
              <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-xs font-bold text-slate-600">₹</span>
              <input type="number" step="0.01" min="0" name="delivery_fee" value="<?= htmlspecialchars($tenant['delivery_fee'] ?? '0.00') ?>"
                     class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-r-xl text-xs font-bold text-slate-900" placeholder="0.00 (Free)">
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Set 0 for free delivery.</p>
          </div>

          <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1">Minimum Order for Delivery (₹)</label>
            <div class="flex">
              <span class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-slate-200 bg-slate-100 text-xs font-bold text-slate-600">₹</span>
              <input type="number" step="0.01" min="0" name="min_delivery_order" value="<?= htmlspecialchars($tenant['min_delivery_order'] ?? '0.00') ?>"
                     class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-r-xl text-xs font-bold text-slate-900" placeholder="e.g. 200.00">
            </div>
            <p class="text-[10px] text-slate-400 mt-1">Orders below this will prompt customers to add more items or pick up.</p>
          </div>
        </div>

        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Delivery Area / Radius Note</label>
          <input type="text" name="delivery_area_note" value="<?= htmlspecialchars($tenant['delivery_area_note'] ?? '') ?>" placeholder="e.g. Delivering within 3km of shop (MG Road area)"
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
          <p class="text-[10px] text-slate-400 mt-1">Displayed to customers near the checkout delivery toggle.</p>
        </div>

        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Custom Order Thank You / Confirmation Message</label>
          <textarea name="order_thank_you_msg" rows="2" placeholder="e.g. Thanks for ordering from Laxmi Kirana! We will confirm your delivery time on WhatsApp shortly."
                    class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900"><?= htmlspecialchars($tenant['order_thank_you_msg'] ?? '') ?></textarea>
          <p class="text-[10px] text-slate-400 mt-1">Displayed on the customer's screen right after placing an order.</p>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
          <button type="submit" class="py-3 px-6 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md transition-all">
            Save Delivery Settings
          </button>
    </div>

    <!-- Security & Password Reset Card -->
    <div class="app-card p-6 sm:p-8 rounded-2xl bg-white border border-slate-200 shadow-sm space-y-6">
      <div class="flex items-center space-x-3 border-b border-slate-100 pb-3">
        <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm">
          🔐
        </div>
        <div>
          <h3 class="text-base font-black text-slate-900">Security & Account Password</h3>
          <p class="text-xs text-slate-500">Update your merchant account password to keep your store dashboard secure</p>
        </div>
      </div>

      <form method="POST" action="/dashboard/settings.php" data-no-ajax class="space-y-4">
        <input type="hidden" name="action" value="change_password">

        <div>
          <label class="block text-xs font-black uppercase text-slate-700 mb-1">Current Password *</label>
          <input type="password" name="current_password" required placeholder="Enter your current password"
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1">New Password *</label>
            <input type="password" name="new_password" required minlength="6" placeholder="Minimum 6 characters"
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
          </div>

          <div>
            <label class="block text-xs font-black uppercase text-slate-700 mb-1">Confirm New Password *</label>
            <input type="password" name="confirm_password" required minlength="6" placeholder="Confirm new password"
                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900">
          </div>
        </div>

        <div class="pt-4 border-t border-slate-100 flex justify-end">
          <button type="submit" class="py-3 px-6 bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-xs rounded-xl shadow-md transition-all flex items-center space-x-2">
            <span>Update Password</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
          </button>
        </div>
      </form>
    </div>

  </div>

</div>

<script>
function previewLogoFile(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const prev = document.getElementById('logoPreview');
      if (prev) {
        prev.src = e.target.result;
      }
    }
    reader.readAsDataURL(file);
  }
}

</script>

<?php require_once __DIR__ . '/footer.php'; ?>

