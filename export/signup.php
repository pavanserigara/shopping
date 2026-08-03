<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shopName       = trim($_POST['shop_name'] ?? '');
    $subdomain      = strtolower(trim($_POST['subdomain'] ?? ''));
    $whatsappNumber = trim($_POST['whatsapp_number'] ?? '');
    $category       = trim($_POST['category'] ?? 'Kirana & Grocery');
    $email          = trim($_POST['email'] ?? '');
    $password       = trim($_POST['password'] ?? '');

    // Basic Validations
    if (empty($shopName))       $errors[] = "Shop name is required.";
    if (empty($subdomain))      $errors[] = "Subdomain is required.";
    if (!preg_match('/^[a-z0-9-]+$/', $subdomain)) $errors[] = "Subdomain must contain only lowercase letters, numbers, and hyphens.";
    if (empty($whatsappNumber)) $errors[] = "WhatsApp phone number is required.";
    if (empty($email))          $errors[] = "Email address is required.";
    if (strlen($password) < 6)  $errors[] = "Password must be at least 6 characters.";

    $reservedSubdomains = ['shops', 'login', 'signup', 'admin', 'dashboard', 'settings', 'about', 'contact', 'pricing', 'terms', 'privacy', 'api', 'uploads', 'static', 'shop'];
    if (in_array($subdomain, $reservedSubdomains)) {
        $errors[] = "Subdomain '{$subdomain}' is reserved and cannot be used.";
    }

    $pdo = getDBConnection();

    // Check unique subdomain
    if (empty($errors)) {
        $checkSub = $pdo->prepare("SELECT id FROM tenants WHERE subdomain = ?");
        $checkSub->execute([$subdomain]);
        if ($checkSub->fetch()) {
            $errors[] = "Subdomain '{$subdomain}' is already taken. Please choose another.";
        }
    }

    // Check unique admin email
    if (empty($errors)) {
        $checkEmail = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
        $checkEmail->execute([$email]);
        if ($checkEmail->fetch()) {
            $errors[] = "Email '{$email}' is already registered.";
        }
    }

    // Fetch Global Platform Settings
    $defaultTrialDays = 15;
    $defaultProductLimit = 30;
    try {
        $settRow = $pdo->query("SELECT default_trial_days, default_product_limit FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
        if ($settRow) {
            if (isset($settRow['default_trial_days'])) $defaultTrialDays = (int)$settRow['default_trial_days'];
            if (isset($settRow['default_product_limit'])) $defaultProductLimit = (int)$settRow['default_product_limit'];
        }
    } catch (Exception $e) {}

    $trialEndsAt = date('Y-m-d H:i:s', strtotime("+{$defaultTrialDays} days"));

    // Create Tenant and Tenant Admin Account
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $tenantStmt = $pdo->prepare("
                INSERT INTO tenants (shop_name, subdomain, whatsapp_number, category, plan_status, product_limit, trial_ends_at, is_open)
                VALUES (?, ?, ?, ?, 'trial', ?, ?, 1)
            ");
            $tenantStmt->execute([$shopName, $subdomain, $whatsappNumber, $category, $defaultProductLimit, $trialEndsAt]);
            $tenantId = (int)$pdo->lastInsertId();

            $passHash = password_hash($password, PASSWORD_DEFAULT);
            $userStmt = $pdo->prepare("
                INSERT INTO admin_users (tenant_id, role, email, password_hash)
                VALUES (?, 'tenant_admin', ?, ?)
            ");
            $userStmt->execute([$tenantId, $email, $passHash]);
            $userId = (int)$pdo->lastInsertId();

            $pdo->commit();

            // Auto Log In
            $_SESSION['user_id']   = $userId;
            $_SESSION['tenant_id'] = $tenantId;
            $_SESSION['role']      = 'tenant_admin';
            $_SESSION['shop_name'] = $shopName;
            $_SESSION['subdomain'] = $subdomain;

            set_flash('success', "Congratulations! Your shop is live on trial at localshopos.com/{$subdomain}");
            header("Location: /dashboard/index.php");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Registration failed: " . $e->getMessage();
        }
    }
}

$pageTitle = "Create Your Shop — LocalShopOS";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 w-full min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative bg-[#070A11]">
  
  <!-- Ambient Glow -->
  <div class="absolute w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none -top-10 left-1/2 -translate-x-1/2"></div>

  <div class="max-w-lg w-full relative z-10">
    <div class="bento-card p-8 sm:p-10 bg-[#161F33] border-2 border-brand-500/50 shadow-[0_0_50px_rgba(245,180,0,0.2)] rounded-3xl">
      
      <div class="text-center mb-8">
        <span class="text-xs font-black uppercase tracking-wider text-brand-400 bg-brand-500/15 px-3.5 py-1.5 rounded-full border border-brand-500/40 inline-block mb-3">
          ⚡ 14-Day Free Trial • No Credit Card
        </span>
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Launch Your WhatsApp Store</h2>
        <p class="text-xs text-slate-200 mt-2 font-bold">Get your digital catalog online in less than 5 minutes</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-rose-500/20 border border-rose-500/50 text-rose-200 px-4 py-3 rounded-2xl text-xs space-y-1 font-bold">
          <?php foreach ($errors as $err): ?>
            <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/signup.php" class="space-y-5">
        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">Shop / Business Name *</label>
          <input type="text" name="shop_name" value="<?= htmlspecialchars($_POST['shop_name'] ?? '') ?>" required placeholder="e.g. Ramesh Kirana Store"
                 class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
        </div>

        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">Store Subdomain Link *</label>
          <div class="flex items-center">
            <span class="px-3.5 py-3 bg-[#0A1120] border border-r-0 border-white/20 text-brand-400 text-xs font-mono font-bold rounded-l-xl shrink-0">localshopos.com/</span>
            <input type="text" name="subdomain" value="<?= htmlspecialchars($_POST['subdomain'] ?? '') ?>" required placeholder="ramesh-kirana"
                   class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-r-xl text-xs text-white font-mono placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">WhatsApp Phone Number *</label>
          <div class="flex items-center space-x-2">
            <span class="px-3.5 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs font-black text-brand-400">+91</span>
            <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($_POST['whatsapp_number'] ?? '') ?>" required placeholder="9876543210"
                   class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
          </div>
        </div>

        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">Store Category</label>
          <select name="category" class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white font-bold focus:border-brand-400 focus:outline-none">
            <option value="Kirana & Grocery">Kirana & Grocery</option>
            <option value="Bakery & Dairy">Bakery & Dairy</option>
            <option value="Vegetables & Fruits">Vegetables & Fruits</option>
            <option value="Hardware & Tools">Hardware & Tools</option>
            <option value="Clothing & Apparel">Clothing & Apparel</option>
            <option value="General Store">General Store</option>
          </select>
        </div>

        <div class="pt-3 border-t border-white/15 space-y-4">
          <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">Owner Email Address *</label>
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="ramesh@gmail.com"
                   class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
          </div>

          <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">Create Password *</label>
            <input type="password" name="password" required placeholder="••••••••"
                   class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
          </div>
        </div>

        <button type="submit" class="w-full py-4 btn-gold-action text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-xl transition-all">
          Create My Shop Storefront &rarr;
        </button>
      </form>

      <div class="mt-8 pt-6 border-t border-white/15 text-center text-xs text-slate-200 font-semibold">
        Already registered your shop? <a href="/login.php" class="font-black text-brand-400 hover:underline">Log in to dashboard &rarr;</a>
      </div>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
