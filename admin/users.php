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
        // Fetch current user & tenant status
        $uStmt = $pdo->prepare("SELECT au.is_active, au.tenant_id, t.plan_status FROM admin_users au LEFT JOIN tenants t ON au.tenant_id = t.id WHERE au.id = ?");
        $uStmt->execute([$userId]);
        $uRow = $uStmt->fetch();

        if ($uRow) {
            $newActive = $uRow['is_active'] ? 0 : 1;
            $pdo->prepare("UPDATE admin_users SET is_active = ? WHERE id = ?")->execute([$newActive, $userId]);

            if (!empty($uRow['tenant_id'])) {
                $newPlanStatus = $newActive ? 'active' : 'suspended';
                $pdo->prepare("UPDATE tenants SET plan_status = ? WHERE id = ?")->execute([$newPlanStatus, $uRow['tenant_id']]);
            }

            $msg = $newActive ? "User account activated successfully." : "User account suspended successfully.";
            respond_flash('success', $msg, '/admin/users.php', ['reload' => true]);
        } else {
            respond_flash('error', "User not found.", '/admin/users.php');
        }
    }
}

$search = trim($_GET['q'] ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 5;

// Count Total Matching Users
$countSql = "
    SELECT COUNT(*) 
    FROM admin_users au
    LEFT JOIN tenants t ON au.tenant_id = t.id
    WHERE au.role = 'tenant_admin'
";
$countParams = [];
if (!empty($search)) {
    $countSql .= " AND (au.email LIKE ? OR t.shop_name LIKE ? OR t.subdomain LIKE ?)";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
    $countParams[] = "%$search%";
}

$countStmt = $pdo->prepare($countSql);
$countStmt->execute($countParams);
$totalUsers = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalUsers / $limit));
if ($page > $totalPages) $page = $totalPages;
$offset = ($page - 1) * $limit;

// Fetch Paginated User Records
$sql = "
    SELECT au.*, t.shop_name, t.subdomain, t.plan_status 
    FROM admin_users au
    LEFT JOIN tenants t ON au.tenant_id = t.id
    WHERE au.role = 'tenant_admin'
";
$params = [];
if (!empty($search)) {
    $sql .= " AND (au.email LIKE ? OR t.shop_name LIKE ? OR t.subdomain LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$sql .= " ORDER BY au.id DESC LIMIT $limit OFFSET $offset";

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
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search email, shop, subdomain..." class="w-full pl-9 pr-8 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400">
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        <?php if (!empty($search)): ?>
          <a href="/admin/users.php" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700 text-xs font-black">✕</a>
        <?php endif; ?>
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
                No registered users found matching your criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $u): 
              $isSuspended = ((isset($u['is_active']) && (int)$u['is_active'] === 0) || ($u['plan_status'] ?? '') === 'suspended');
            ?>
              <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-5 py-3 text-slate-400 font-mono text-xs">#<?= $u['id'] ?></td>
                <td class="px-5 py-3 text-slate-900 font-bold"><?= htmlspecialchars($u['email']) ?></td>
                <td class="px-5 py-3 text-amber-800 font-bold text-xs">
                  <div><?= htmlspecialchars($u['shop_name'] ?? 'N/A') ?></div>
                  <?php if (!empty($u['subdomain'])): ?>
                    <span class="text-[10px] text-slate-400 font-mono">/<?= htmlspecialchars($u['subdomain']) ?></span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3">
                  <?php if ($isSuspended): ?>
                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 border border-rose-200 rounded-full text-[10px] font-black uppercase tracking-wide">Suspended</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-black uppercase tracking-wide">Active</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-right space-x-2">
                  <form method="POST" class="inline-block">
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="px-3.5 py-1.5 <?= $isSuspended ? 'bg-emerald-100 hover:bg-emerald-200 text-emerald-800 border border-emerald-300' : 'bg-rose-100 hover:bg-rose-200 text-rose-800 border border-rose-300' ?> rounded-lg text-[11px] font-black transition-colors">
                      <?= $isSuspended ? '🟢 Activate User' : '🔴 Suspend User' ?>
                    </button>
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

    <!-- Pagination Controls (10 per page) -->
    <div class="px-5 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs font-bold text-slate-500">
      <div>
        Showing <span class="text-slate-900 font-black"><?= $totalUsers > 0 ? ($offset + 1) : 0 ?></span> to <span class="text-slate-900 font-black"><?= min($offset + $limit, $totalUsers) ?></span> of <span class="text-slate-900 font-black"><?= $totalUsers ?></span> registered users
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="flex items-center space-x-1">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-bold transition-colors">&laquo; Prev</a>
          <?php endif; ?>

          <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="px-3 py-1.5 rounded-lg text-xs font-black transition-colors <?= $i === $page ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100' ?>">
              <?= $i ?>
            </a>
          <?php endfor; ?>

          <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" class="px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-lg text-xs font-bold transition-colors">Next &raquo;</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

</main>

<!-- Super Admin User Password Reset Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-all duration-200">
  <div class="max-w-md w-full bg-[#161F33] border-2 border-amber-500/50 rounded-3xl p-6 sm:p-8 shadow-[0_0_50px_rgba(245,180,0,0.25)] text-white relative">
    
    <div class="flex items-center justify-between border-b border-white/10 pb-4 mb-5">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-2xl bg-amber-400/20 text-amber-400 border border-amber-400/30 flex items-center justify-center font-black text-lg">
          🔐
        </div>
        <div>
          <h3 class="text-base font-black text-white">Set User Password</h3>
          <p class="text-xs text-amber-400 font-bold" id="resetModalUserEmail">user@example.com</p>
        </div>
      </div>
      <button type="button" onclick="closeResetModal()" class="text-slate-400 hover:text-white p-1 rounded-lg text-lg">✕</button>
    </div>

    <form method="POST" action="/admin/users.php" data-no-ajax class="space-y-4">
      <input type="hidden" name="action" value="reset_password">
      <input type="hidden" name="user_id" id="resetModalUserId" value="0">

      <div class="flex justify-between items-center">
        <label class="block text-xs font-black uppercase tracking-wider text-slate-300">New Password *</label>
        <button type="button" onclick="generateAutoPassword()" class="text-[11px] font-black text-amber-400 hover:text-amber-300 flex items-center space-x-1">
          <span>🎲 Generate Random</span>
        </button>
      </div>

      <div class="relative">
        <input type="password" name="new_password" id="resetModalNewPassword" required minlength="6" placeholder="Enter new password"
               class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none font-bold pr-10">
        <button type="button" onclick="togglePasswordVisibility('resetModalNewPassword')" class="absolute right-3 top-3 text-slate-400 hover:text-white text-xs font-bold">
          👁️
        </button>
      </div>

      <div>
        <label class="block text-xs font-black uppercase tracking-wider text-slate-300 mb-1.5">Confirm New Password *</label>
        <div class="relative">
          <input type="password" name="confirm_password" id="resetModalConfirmPassword" required minlength="6" placeholder="Confirm new password"
                 class="w-full px-4 py-3 bg-[#0A1120] border border-white/20 rounded-xl text-xs text-white placeholder-slate-500 focus:border-amber-400 focus:outline-none font-bold pr-10">
          <button type="button" onclick="togglePasswordVisibility('resetModalConfirmPassword')" class="absolute right-3 top-3 text-slate-400 hover:text-white text-xs font-bold">
            👁️
          </button>
        </div>
      </div>

      <div class="pt-3 flex items-center space-x-3">
        <button type="button" onclick="closeResetModal()" class="w-1/3 py-3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs rounded-xl transition-all">
          Cancel
        </button>
        <button type="submit" class="w-2/3 py-3 bg-amber-400 hover:bg-amber-300 text-slate-950 font-black text-xs uppercase tracking-wider rounded-xl shadow-lg transition-all border border-amber-300 flex items-center justify-center space-x-1">
          <span>Save New Password &rarr;</span>
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

function generateAutoPassword() {
  const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#';
  let pass = '';
  for (let i = 0; i < 10; i++) {
    pass += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  const p1 = document.getElementById('resetModalNewPassword');
  const p2 = document.getElementById('resetModalConfirmPassword');
  p1.value = pass;
  p2.value = pass;
  p1.type = 'text';
  p2.type = 'text';
}

function togglePasswordVisibility(inputId) {
  const input = document.getElementById(inputId);
  if (input) {
    input.type = input.type === 'password' ? 'text' : 'password';
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

