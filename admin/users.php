<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_super_admin_auth();

$pdo = getDBConnection();

$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    
    if ($action === 'reset_password' && $userId > 0) {
        $newPass     = trim($_POST['new_password'] ?? '');
        $confirmPass = trim($_POST['confirm_password'] ?? '');
        
        if (empty($newPass)) {
            respond_flash('error', "New password cannot be empty.", '/admin/users.php');
        } elseif (strlen($newPass) < 6) {
            respond_flash('error', "Password must be at least 6 characters long.", '/admin/users.php');
        } elseif ($newPass !== $confirmPass) {
            respond_flash('error', "New password and confirm password do not match.", '/admin/users.php');
        } else {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$hash, $userId]);
            
            // Fetch target user email for log/flash
            $userEmailStmt = $pdo->prepare("SELECT email FROM admin_users WHERE id = ?");
            $userEmailStmt->execute([$userId]);
            $userEmail = $userEmailStmt->fetchColumn() ?: "ID #{$userId}";

            // Log the action
            $logStmt = $pdo->prepare("INSERT INTO admin_action_log (actor_admin_id, action, target_tenant_id) VALUES (?, ?, (SELECT tenant_id FROM admin_users WHERE id = ?))");
            $logStmt->execute([$_SESSION['user_id'], 'reset_password_user_' . $userId, $userId]);
            
            respond_flash('success', "Password for merchant user <strong>" . htmlspecialchars($userEmail) . "</strong> has been updated successfully!", '/admin/users.php', ['reload' => true]);
        }
    }
    
    if ($action === 'toggle_active' && $userId > 0) {
        $pdo->prepare("UPDATE admin_users SET is_active = NOT is_active WHERE id = ?")->execute([$userId]);
        respond_flash('success', "User active status toggled.", '/admin/users.php', ['reload' => true]);
    }
}

$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT au.*, t.shop_name 
    FROM admin_users au
    LEFT JOIN tenants t ON au.tenant_id = t.id
    WHERE au.role = 'tenant_admin'
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (au.email LIKE ? OR t.shop_name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY au.id DESC LIMIT 100";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();

$pageTitle = "User & Tenant Management — Super Admin";
require_once __DIR__ . '/header.php';
?>

<?php display_flash(); ?>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
  
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">👥 Tenant User Management</h1>
      <p class="text-xs text-slate-500 font-medium mt-1">Manage tenant (shop owner) access, suspend accounts, or set custom user passwords.</p>
    </div>
    <form method="GET" class="flex items-center space-x-2">
      <div class="relative w-full sm:w-64">
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search email or shop..." class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
      </div>
      <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-black text-xs shadow-sm transition-colors">Search</button>
    </form>
  </div>

  <div class="app-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm whitespace-nowrap">
        <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-black text-[11px] uppercase tracking-wider">
          <tr>
            <th class="px-5 py-3.5 rounded-tl-xl">User ID</th>
            <th class="px-5 py-3.5">Account Email</th>
            <th class="px-5 py-3.5">Shop / Storefront</th>
            <th class="px-5 py-3.5">Access Status</th>
            <th class="px-5 py-3.5 text-right rounded-tr-xl">Admin Controls</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php if(empty($users)): ?>
            <tr>
              <td colspan="5" class="px-5 py-12 text-center text-slate-400 font-bold text-sm">
                No users found matching your criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $u): ?>
              <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-5 py-3 text-slate-400 font-mono text-xs">#<?= $u['id'] ?></td>
                <td class="px-5 py-3 text-slate-900 font-bold"><?= htmlspecialchars($u['email']) ?></td>
                <td class="px-5 py-3 text-amber-800 font-bold text-xs">
                  <?= htmlspecialchars($u['shop_name'] ?? 'N/A') ?>
                </td>
                <td class="px-5 py-3">
                  <?php if (isset($u['is_active']) && $u['is_active'] == 0): ?>
                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 border border-rose-200 rounded-full text-[10px] font-black uppercase tracking-wide">Suspended</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-black uppercase tracking-wide">Active</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                  <form method="POST" class="inline-block">
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-black text-slate-700 transition-colors">Toggle Access</button>
                  </form>
                  <button type="button" 
                          onclick="openResetModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['email'], ENT_QUOTES) ?>')"
                          class="px-3.5 py-1.5 bg-brand-500 hover:bg-brand-400 text-slate-950 rounded-lg text-[11px] font-black shadow-sm transition-colors border border-brand-300">
                    🔑 Reset Password
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</main>

<!-- Super Admin User Password Reset Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/75 backdrop-blur-md transition-all duration-200">
  <div class="max-w-md w-full bg-[#161F33] border-2 border-brand-500/50 rounded-3xl p-6 sm:p-8 shadow-[0_0_50px_rgba(245,180,0,0.2)] text-white relative animate-fade-in">
    
    <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-6">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-2xl bg-brand-500/20 text-brand-400 border border-brand-500/30 flex items-center justify-center font-black text-lg">
          🔐
        </div>
        <div>
          <h3 class="text-base font-black text-white">Reset User Password</h3>
          <p class="text-xs text-brand-400 font-bold" id="resetModalUserEmail">user@example.com</p>
        </div>
      </div>
      <button type="button" onclick="closeResetModal()" class="text-slate-400 hover:text-white p-1 rounded-lg text-lg">✕</button>
    </div>

    <form method="POST" action="/admin/users.php" class="space-y-4">
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="user_id" id="resetModalUserId" value="0">

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-1.5">New Password *</label>
        <input type="password" name="new_password" id="resetModalNewPassword" required minlength="6" placeholder="Enter new password"
               class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-500 focus:border-brand-400 focus:outline-none font-bold">
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-1.5">Confirm New Password *</label>
        <input type="password" name="confirm_password" id="resetModalConfirmPassword" required minlength="6" placeholder="Confirm new password"
               class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-500 focus:border-brand-400 focus:outline-none font-bold">
      </div>

      <div class="pt-3 flex items-center space-x-3">
        <button type="button" onclick="closeResetModal()" class="w-1/3 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition-all">
          Cancel
        </button>
        <button type="submit" class="w-2/3 py-3 btn-gold-action text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-lg transition-all">
          Update Password &rarr;
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openResetModal(userId, userEmail) {
  document.getElementById('resetModalUserId').value = userId;
  document.getElementById('resetModalUserEmail').textContent = userEmail;
  document.getElementById('resetModalNewPassword').value = '';
  document.getElementById('resetModalConfirmPassword').value = '';
  document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function closeResetModal() {
  document.getElementById('resetPasswordModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

