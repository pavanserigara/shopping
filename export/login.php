<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$errors = [];

if (is_tenant_logged_in()) {
    header("Location: /dashboard/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email)) $errors[] = "Email address is required.";
    if (empty($password)) $errors[] = "Password is required.";

    if (empty($errors)) {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT u.*, t.shop_name, t.subdomain, t.plan_status 
            FROM admin_users u
            JOIN tenants t ON u.tenant_id = t.id
            WHERE u.email = ? AND u.role = 'tenant_admin'
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            if ($user['plan_status'] === 'suspended' || (isset($user['is_active']) && (int)$user['is_active'] === 0)) {
                $errors[] = "Your shop account is currently suspended or disabled. Please contact platform support.";
            } else {
                $_SESSION['user_id']     = $user['id'];
                $_SESSION['tenant_id']   = $user['tenant_id'];
                $_SESSION['role']        = $user['role'];
                $_SESSION['shop_name']   = $user['shop_name'];
                $_SESSION['subdomain']   = $user['subdomain'];

                set_flash('success', "Welcome back to your merchant dashboard!");
                header("Location: /dashboard/index.php");
                exit;
            }
        } else {
            $errors[] = "Invalid email or password credentials.";
        }
    }
}

$pageTitle = "Merchant Login — LocalShopOS";
require_once __DIR__ . '/includes/header.php';
?>

<main class="flex-1 w-full min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 relative bg-[#070A11]">
  
  <!-- Subtle Ambient Glow -->
  <div class="absolute w-96 h-96 bg-brand-500/10 rounded-full blur-3xl pointer-events-none -top-10 left-1/2 -translate-x-1/2"></div>

  <div class="max-w-md w-full relative z-10">
    <div class="bento-card p-8 sm:p-10 bg-[#161F33] border-2 border-brand-500/50 shadow-[0_0_50px_rgba(245,180,0,0.2)] rounded-3xl">
      
      <div class="text-center mb-8">
        <div class="w-14 h-14 rounded-2xl bg-brand-500 text-slate-950 font-black text-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
          🏪
        </div>
        <h2 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Merchant Portal Login</h2>
        <p class="text-xs text-slate-200 mt-2 font-bold">Manage your products, orders, and shop settings</p>
      </div>

      <?php if (!empty($errors)): ?>
        <div class="mb-6 bg-rose-500/20 border border-rose-500/50 text-rose-200 px-4 py-3 rounded-2xl text-xs space-y-1 font-bold">
          <?php foreach ($errors as $err): ?>
            <p class="flex items-center"><svg class="w-4 h-4 mr-1.5 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg><?= htmlspecialchars($err) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="/login.php" class="space-y-5">
        <div>
          <label class="block text-xs font-black uppercase tracking-wider text-slate-200 mb-1.5">Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required placeholder="ramu@kirana.com"
                 class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-black uppercase tracking-wider text-slate-200">Password</label>
          </div>
          <input type="password" name="password" required placeholder="••••••••"
                 class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-400 focus:border-brand-400 focus:outline-none font-bold">
        </div>

        <button type="submit" class="w-full py-4 btn-gold-action text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-xl transition-all">
          Sign In To Dashboard &rarr;
        </button>
      </form>

      <div class="mt-8 pt-6 border-t border-white/15 text-center text-xs text-slate-200 font-semibold">
        Don't have a shop online yet? <a href="/signup.php" class="font-black text-brand-400 hover:underline">Start Free Trial &rarr;</a>
      </div>

    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
