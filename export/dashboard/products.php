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

// Fetch Tenant Info & Current Product Limit
$tCheckStmt = $pdo->prepare("SELECT t.*, p.name as plan_name FROM tenants t LEFT JOIN plans p ON t.plan_id = p.id WHERE t.id = ?");
$tCheckStmt->execute([$tenantId]);
$tenantInfo = $tCheckStmt->fetch();

$hasProdMgmt = tenant_has_feature($pdo, $tenantInfo, 'product_management');
$hasGallery  = tenant_has_feature($pdo, $tenantInfo, 'product_image_gallery');

$productLimit = (int)($tenantInfo['product_limit'] ?? 30);
$currentProductCount = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE tenant_id = {$tenantId}")->fetchColumn();
$isLimitReached = ($currentProductCount >= $productLimit);

$errors = [];
$categories = ['Groceries', 'Dairy & Milk', 'Beverages', 'Household Essentials', 'Snacks & Bakery', 'Personal Care', 'Electronics', 'Clothing', 'General'];

// Handle Product Creation, Updates, Deletions, and Image Gallery Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$hasProdMgmt) {
        respond_flash('error', "Product management feature is locked on your current plan.", '/dashboard/products.php');
    }

    $action = $_POST['action'] ?? '';

    // Action 1: Create Product with Multiple Images
    if ($action === 'create_product') {
        $name       = trim($_POST['name'] ?? '');
        $price      = (float)($_POST['price'] ?? 0);
        $stockCount = (int)($_POST['stock_count'] ?? 0);
        $category   = trim($_POST['category'] ?? 'General');
        $isActive   = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) $errors[] = "Product name is required.";
        if ($price <= 0)  $errors[] = "Price must be greater than 0.";

        // Product Limit Enforcement
        if ($isLimitReached) {
            $errors[] = "You've reached your product limit of {$productLimit} items — upgrade your subscription plan for higher limits.";
        }

        if (empty($errors)) {
            // Insert product record - SCOPED BY tenant_id
            $stmt = $pdo->prepare("
                INSERT INTO products (tenant_id, name, price, stock_count, category, is_active)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$tenantId, $name, $price, $stockCount, $category, $isActive]);
            $productId = (int)$pdo->lastInsertId();

            // Process Multiple Image Uploads (gated gallery limit to 1 image if no gallery feature)
            if (!empty($_FILES['product_photos']['name'][0])) {
                $uploadDir = __DIR__ . '/../uploads/' . $tenantId . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $primaryPhotoUrl = '';
                $rawCount = count($_FILES['product_photos']['name']);
                $imageCount = $hasGallery ? $rawCount : min(1, $rawCount);
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                for ($i = 0; $i < $imageCount; $i++) {
                    if ($_FILES['product_photos']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['product_photos']['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowedExts)) {
                            $filename = 'prod_' . $productId . '_' . time() . '_' . $i . '.' . $ext;
                            $targetFile = $uploadDir . $filename;

                            if (move_uploaded_file($_FILES['product_photos']['tmp_name'][$i], $targetFile)) {
                                $url = '/uploads/' . $tenantId . '/' . $filename;
                                $isPrimary = ($i === 0) ? 1 : 0;

                                if ($isPrimary) {
                                    $primaryPhotoUrl = $url;
                                }

                                $imgStmt = $pdo->prepare("
                                    INSERT INTO product_images (product_id, image_url, sort_order, is_primary)
                                    VALUES (?, ?, ?, ?)
                                ");
                                $imgStmt->execute([$productId, $url, $i, $isPrimary]);
                            }
                        }
                    }
                }

                if (!empty($primaryPhotoUrl)) {
                    $updateStmt = $pdo->prepare("UPDATE products SET photo_url = ? WHERE id = ? AND tenant_id = ?");
                    $updateStmt->execute([$primaryPhotoUrl, $productId, $tenantId]);
                }
            }

            respond_flash('success', "Product '{$name}' created successfully!", '/dashboard/products.php', ['reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/dashboard/products.php');
        }
    }

    // Action 2: Update Product Details
    if ($action === 'update_product') {
        $productId  = (int)($_POST['product_id'] ?? 0);
        $name       = trim($_POST['name'] ?? '');
        $price      = (float)($_POST['price'] ?? 0);
        $stockCount = (int)($_POST['stock_count'] ?? 0);
        $category   = trim($_POST['category'] ?? 'General');
        $isActive   = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) $errors[] = "Product name is required.";
        if ($price <= 0)  $errors[] = "Price must be greater than 0.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("
                UPDATE products 
                SET name = ?, price = ?, stock_count = ?, category = ?, is_active = ?
                WHERE id = ? AND tenant_id = ?
            ");
            $stmt->execute([$name, $price, $stockCount, $category, $isActive, $productId, $tenantId]);

            // Handle Additional Photo Uploads on Update
            if (!empty($_FILES['new_product_photos']['name'][0])) {
                $uploadDir = __DIR__ . '/../uploads/' . $tenantId . '/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $rawCount = count($_FILES['new_product_photos']['name']);
                $imageCount = $hasGallery ? $rawCount : min(1, $rawCount);
                $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                for ($i = 0; $i < $imageCount; $i++) {
                    if ($_FILES['new_product_photos']['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['new_product_photos']['name'][$i], PATHINFO_EXTENSION));
                        if (in_array($ext, $allowedExts)) {
                            $filename = 'prod_' . $productId . '_' . time() . '_' . $i . '.' . $ext;
                            $targetFile = $uploadDir . $filename;

                            if (move_uploaded_file($_FILES['new_product_photos']['tmp_name'][$i], $targetFile)) {
                                $url = '/uploads/' . $tenantId . '/' . $filename;
                                $imgStmt = $pdo->prepare("
                                    INSERT INTO product_images (product_id, image_url, sort_order, is_primary)
                                    VALUES (?, ?, ?, 0)
                                ");
                                $imgStmt->execute([$productId, $url, $i]);
                            }
                        }
                    }
                }
            }

            respond_flash('success', "Product updated successfully!", '/dashboard/products.php', ['reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/dashboard/products.php');
        }
    }

    // Action 3: Delete Product
    if ($action === 'delete_product') {
        $productId = (int)($_POST['product_id'] ?? 0);

        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$productId, $tenantId]);

        respond_flash('success', "Product deleted successfully.", '/dashboard/products.php', ['reload' => true]);
    }

    // Action 4: Delete Specific Image from Gallery
    if ($action === 'delete_image') {
        $imageId   = (int)($_POST['image_id'] ?? 0);
        $productId = (int)($_POST['product_id'] ?? 0);

        $checkStmt = $pdo->prepare("
            SELECT pi.* FROM product_images pi 
            JOIN products p ON pi.product_id = p.id 
            WHERE pi.id = ? AND p.tenant_id = ?
        ");
        $checkStmt->execute([$imageId, $tenantId]);
        $img = $checkStmt->fetch();

        if ($img) {
            $pdo->prepare("DELETE FROM product_images WHERE id = ?")->execute([$imageId]);
            
            // If primary, set another image as primary if exists
            if ($img['is_primary']) {
                $nextImgStmt = $pdo->prepare("SELECT image_url FROM product_images WHERE product_id = ? ORDER BY id ASC LIMIT 1");
                $nextImgStmt->execute([$productId]);
                $nextUrl = $nextImgStmt->fetchColumn() ?: null;

                $updatePrimary = $pdo->prepare("UPDATE products SET photo_url = ? WHERE id = ?");
                $updatePrimary->execute([$nextUrl, $productId]);
            }

            respond_flash('success', "Photo removed from gallery.", '/dashboard/products.php', ['reload' => true]);
        }
    }
}

$pageTitle = "Product Catalog Management — LocalShopOS";
require_once __DIR__ . '/header.php';

if (!$hasProdMgmt) {
    render_locked_feature_notice('product_management');
    require_once __DIR__ . '/footer.php';
    exit;
}

// Fetch All Products for logged-in Tenant
$productsStmt = $pdo->prepare("
    SELECT * FROM products 
    WHERE tenant_id = ? 
    ORDER BY category ASC, name ASC
");
$productsStmt->execute([$tenantId]);
$products = $productsStmt->fetchAll();

// Fetch Product Images mapped by product_id
$productIds = array_column($products, 'id');
$imagesMap = [];

if (!empty($productIds)) {
    $inClause = implode(',', array_fill(0, count($productIds), '?'));
    $imagesStmt = $pdo->prepare("
        SELECT * FROM product_images 
        WHERE product_id IN ($inClause) 
        ORDER BY is_primary DESC, id ASC
    ");
    $imagesStmt->execute($productIds);
    $allImages = $imagesStmt->fetchAll();

    foreach ($allImages as $img) {
        $imagesMap[$img['product_id']][] = $img;
    }
}

// Compute usage percentage
$usagePercent = min(100, round(($currentProductCount / $productLimit) * 100));
?>

<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-xl sm:text-2xl font-black text-slate-900">Product Catalog Management</h1>
    <p class="text-xs text-slate-600 mt-0.5">Manage item inventory, prices, stock counts, and multi-photo galleries.</p>
  </div>
  
  <?php if ($isLimitReached): ?>
    <div class="px-4 py-2 bg-amber-100 border border-amber-300 rounded-xl text-xs font-black text-amber-900 flex items-center space-x-1 self-start">
      <span>🔒 Product Limit Reached (<?= $currentProductCount ?>/<?= $productLimit ?>)</span>
    </div>
  <?php else: ?>
    <button onclick="openCreateModal()" class="px-4 py-2.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-sm hover:scale-[1.02] transition-all flex items-center space-x-1.5 self-start">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
      <span>Add New Product</span>
    </button>
  <?php endif; ?>
</div>

<!-- Product Limit Progress Indicator -->
<div class="app-card p-4 rounded-2xl mb-6 bg-white border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
  <div>
    <h4 class="text-xs font-black uppercase text-slate-700">Catalog Capacity Usage</h4>
    <p class="text-xs text-slate-500 font-medium">
      <strong><?= $currentProductCount ?></strong> of <strong><?= $productLimit ?></strong> maximum allowed items used.
    </p>
  </div>

  <div class="w-full sm:w-64 space-y-1">
    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200">
      <div class="h-full bg-brand-500 rounded-full" style="width: <?= $usagePercent ?>%;"></div>
    </div>
    <div class="text-[10px] font-black text-right text-slate-500"><?= $usagePercent ?>% Capacity</div>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="mb-6 bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs space-y-1">
    <?php foreach ($errors as $err): ?>
      <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Products Grid -->
<div class="app-card rounded-2xl overflow-hidden bg-white">
  <div class="p-5 border-b border-slate-100 flex items-center justify-between">
    <h3 class="text-base font-black text-slate-900">Your Store Catalog</h3>
    <span class="text-xs font-bold text-slate-500"><?= count($products) ?> Total Items</span>
  </div>

  <?php if (empty($products)): ?>
    <div class="text-center py-16">
      <div class="text-5xl mb-3">📦</div>
      <h4 class="text-base font-black text-slate-900">No products added yet</h4>
      <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Click "Add New Product" above to build your digital catalog.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 p-5">
      <?php foreach ($products as $p): 
        $imgs = $imagesMap[$p['id']] ?? [];
        $primaryImg = !empty($p['photo_url']) ? $p['photo_url'] : (!empty($imgs) ? $imgs[0]['image_url'] : '');
      ?>
        <div class="app-card rounded-xl overflow-hidden border border-slate-200 flex flex-col justify-between group bg-white shadow-sm">
          <div>
            <div class="h-40 bg-slate-100 overflow-hidden relative flex items-center justify-center">
              <?php if (!empty($primaryImg)): ?>
                <img src="<?= htmlspecialchars($primaryImg) ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
              <?php else: ?>
                <span class="text-3xl">🛍️</span>
              <?php endif; ?>

              <span class="absolute top-2 left-2 px-2 py-0.5 text-[9px] font-black bg-slate-900 text-white rounded-md uppercase">
                <?= htmlspecialchars($p['category']) ?>
              </span>

              <?php if (count($imgs) > 1): ?>
                <span class="absolute top-2 right-2 px-2 py-0.5 text-[9px] font-black bg-brand-500 text-slate-950 rounded-md shadow-sm">
                  🖼️ <?= count($imgs) ?> Photos
                </span>
              <?php endif; ?>
            </div>

            <div class="p-3.5 space-y-1">
              <h4 class="font-extrabold text-slate-900 text-sm line-clamp-1"><?= htmlspecialchars($p['name']) ?></h4>
              <div class="flex items-center justify-between pt-1">
                <span class="text-base font-black text-emerald-700">₹<?= number_format($p['price'], 2) ?></span>
                <span class="text-xs text-slate-600 font-bold">Stock: <?= (int)$p['stock_count'] ?></span>
              </div>
            </div>
          </div>

          <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
            <button onclick='openEditModal(<?= json_encode($p) ?>, <?= json_encode($imgs) ?>)' 
                    class="text-xs font-black text-amber-800 hover:underline">
              Edit & Gallery &rarr;
            </button>

            <form method="POST" action="/dashboard/products.php" class="inline" onsubmit="return confirm('Delete this product?')">
              <input type="hidden" name="action" value="delete_product">
              <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
              <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Create Product Modal -->
<div id="createProductModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-lg p-6 rounded-2xl shadow-2xl relative bg-white max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
      <h3 class="text-base font-black text-slate-900">Add New Product</h3>
      <button onclick="document.getElementById('createProductModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>

    <form method="POST" action="/dashboard/products.php" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="action" value="create_product">

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Product Title *</label>
        <input type="text" name="name" required placeholder="e.g. Fortune Chakki Fresh Atta 5kg"
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Price (₹) *</label>
          <input type="number" step="0.01" name="price" required placeholder="245.00"
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
        </div>

        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Stock Count</label>
          <input type="number" name="stock_count" value="10"
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
        </div>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Category</label>
        <select name="category" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Multiple Image Uploads (gated by product_image_gallery) -->
      <div>
        <div class="flex items-center justify-between mb-1">
          <label class="block text-xs font-black uppercase tracking-wider text-slate-700">Product Photos</label>
          <?php if (!$hasGallery): ?>
            <span class="text-[10px] font-black uppercase text-amber-800 bg-brand-100 px-2 py-0.5 rounded-md border border-brand-300">1 Photo Max (Free)</span>
          <?php else: ?>
            <span class="text-[10px] font-black uppercase text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded-md">Multi-Photo Unlocked</span>
          <?php endif; ?>
        </div>

        <input type="file" name="product_photos[]" accept="image/*" <?= $hasGallery ? 'multiple' : '' ?>
               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-500 file:text-slate-950">
        <p class="text-[10px] text-slate-400 mt-1">
          <?= $hasGallery ? 'Select multiple photo files to create an interactive product gallery slider.' : 'Upgrade plan to unlock multi-photo product galleries.' ?>
        </p>
      </div>

      <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
        <button type="button" onclick="document.getElementById('createProductModal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500">Cancel</button>
        <button type="submit" class="px-5 py-2.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md">Create Product</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-lg p-6 rounded-2xl shadow-2xl relative bg-white max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
      <h3 class="text-base font-black text-slate-900">Edit Product & Gallery</h3>
      <button onclick="document.getElementById('editProductModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
    </div>

    <form method="POST" action="/dashboard/products.php" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="action" value="update_product">
      <input type="hidden" name="product_id" id="editProductId">

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Product Title *</label>
        <input type="text" name="name" id="editProductName" required
               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Price (₹) *</label>
          <input type="number" step="0.01" name="price" id="editProductPrice" required
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold">
        </div>

        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Stock Count</label>
          <input type="number" name="stock_count" id="editProductStock"
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold">
        </div>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Category</label>
        <select name="category" id="editProductCategory" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Existing Image Gallery Grid -->
      <div class="pt-2 border-t border-slate-100">
        <h4 class="text-xs font-black uppercase text-slate-700 mb-2">Current Photo Gallery</h4>
        <div id="editGalleryContainer" class="flex items-center space-x-2 overflow-x-auto pb-2"></div>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-700 mb-1">Upload Additional Photos</label>
        <input type="file" name="new_product_photos[]" accept="image/*" <?= $hasGallery ? 'multiple' : '' ?>
               class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-brand-500 file:text-slate-950">
      </div>

      <div class="pt-4 border-t border-slate-100 flex justify-end space-x-2">
        <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')" class="px-4 py-2 text-xs font-bold text-slate-500">Cancel</button>
        <button type="submit" class="px-5 py-2.5 btn-cta text-white font-extrabold text-xs rounded-xl shadow-md">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCreateModal() {
  document.getElementById('createProductModal').classList.remove('hidden');
}

function openEditModal(prod, images) {
  document.getElementById('editProductId').value = prod.id;
  document.getElementById('editProductName').value = prod.name;
  document.getElementById('editProductPrice').value = prod.price;
  document.getElementById('editProductStock').value = prod.stock_count;
  document.getElementById('editProductCategory').value = prod.category;

  const container = document.getElementById('editGalleryContainer');
  container.innerHTML = '';

  if (images && images.length > 0) {
    images.forEach(img => {
      const div = document.createElement('div');
      div.className = 'w-16 h-16 rounded-xl overflow-hidden border border-slate-200 relative shrink-0 bg-slate-900 group';
      div.innerHTML = `
        <img src="${img.image_url}" class="w-full h-full object-cover">
        <form method="POST" action="/dashboard/products.php" class="absolute inset-0 bg-slate-950/70 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
          <input type="hidden" name="action" value="delete_image">
          <input type="hidden" name="image_id" value="${img.id}">
          <input type="hidden" name="product_id" value="${prod.id}">
          <button type="submit" class="text-rose-400 font-bold text-sm" title="Remove photo">&times;</button>
        </form>
      `;
      container.appendChild(div);
    });
  } else {
    container.innerHTML = `<span class="text-xs text-slate-400 font-medium">No photo files in gallery yet.</span>`;
  }

  document.getElementById('editProductModal').classList.remove('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
