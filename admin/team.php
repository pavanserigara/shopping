<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

require_super_admin_auth();

$pdo = getDBConnection();

$action = $_POST['action'] ?? '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'create_admin') {
        $email = trim($_POST['email'] ?? '');
        $role = $_POST['role'] ?? 'super_admin';
        
        if (empty($email)) {
            $errors[] = "Email is required.";
        }
        
        $check = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
        $check->execute([$email]);
        if ($check->fetch()) {
            $errors[] = "User with this email already exists.";
        }
        
        if (empty($errors)) {
            $newPass = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#'), 0, 10);
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO admin_users (role, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$role, $email, $hash]);
            
            respond_flash('success', "New $role created with email $email. Temporary password: <strong>" . htmlspecialchars($newPass) . "</strong>", '/admin/team.php', ['reload' => true]);
        } else {
            respond_flash('error', implode(', ', $errors), '/admin/team.php');
        }
    }
    
    if ($action === 'toggle_active') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId === (int)($_SESSION['user_id'] ?? 0)) {
            respond_flash('error', "You cannot deactivate your own account.", '/admin/team.php');
        } else {
            $pdo->prepare("UPDATE admin_users SET is_active = NOT is_active WHERE id = ?")->execute([$userId]);
            respond_flash('success', "Team member access toggled.", '/admin/team.php', ['reload' => true]);
        }
    }
}

$sql = "
    SELECT * FROM admin_users 
    WHERE role IN ('super_admin', 'support_admin')
    ORDER BY id ASC
";
$stmt = $pdo->query($sql);
$team = $stmt->fetchAll();
$pageTitle = "Admin Team & Staff — Super Admin";
require_once __DIR__ . '/header.php';
?>

<?php display_flash(); ?>

<main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1 flex flex-col lg:flex-row gap-8">
  <div class="flex-1">
    <div class="mb-6">
      <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">🛡️ Platform Team</h1>
      <p class="text-xs text-slate-500 font-medium mt-1">Manage super admins and support staff across the platform.</p>
    </div>
    
    <div class="app-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
          <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 font-black text-[11px] uppercase tracking-wider">
            <tr>
              <th class="px-5 py-3.5 rounded-tl-xl">ID</th>
              <th class="px-5 py-3.5">Admin Email</th>
              <th class="px-5 py-3.5">Role Level</th>
              <th class="px-5 py-3.5">Status</th>
              <th class="px-5 py-3.5 text-right rounded-tr-xl">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <?php foreach ($team as $u): ?>
              <tr class="hover:bg-slate-50/50 transition-colors">
                <td class="px-5 py-3 text-slate-400 font-mono text-xs">#<?= $u['id'] ?></td>
                <td class="px-5 py-3 text-slate-900 font-bold"><?= htmlspecialchars($u['email']) ?></td>
                <td class="px-5 py-3">
                  <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded-md text-[10px] uppercase tracking-wider font-black border border-slate-200">
                    <?= htmlspecialchars(str_replace('_', ' ', $u['role'])) ?>
                  </span>
                </td>
                <td class="px-5 py-3">
                  <?php if (isset($u['is_active']) && $u['is_active'] == 0): ?>
                    <span class="px-2.5 py-1 bg-rose-100 text-rose-800 border border-rose-200 rounded-full text-[10px] font-black uppercase tracking-wide">Suspended</span>
                  <?php else: ?>
                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-800 border border-emerald-200 rounded-full text-[10px] font-black uppercase tracking-wide">Active</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-3 text-right">
                  <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <form method="POST" class="inline-block" onsubmit="return confirm('Toggle access for this admin?');">
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <button type="submit" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-[11px] font-black text-slate-700 transition-colors">Toggle Access</button>
                  </form>
                  <?php else: ?>
                    <span class="text-[11px] font-bold text-slate-400 px-3 py-1 bg-slate-50 rounded-lg border border-slate-100">It's You</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <div class="w-full lg:w-80 shrink-0">
    <div class="app-card p-5 sm:p-6 sticky top-24">
      <h3 class="font-black text-slate-900 mb-4 text-lg">Invite New Admin</h3>
      
      <?php if (!empty($errors)): ?>
        <div class="bg-rose-50 border border-rose-100 text-rose-700 text-[11px] font-bold p-3 rounded-xl mb-4">
          <?= implode('<br>', array_map('htmlspecialchars', $errors)) ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" class="space-y-4">
        <input type="hidden" name="action" value="create_admin">
        <div>
          <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Email Address</label>
          <input type="email" name="email" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400 transition-colors">
        </div>
        <div>
          <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Admin Role</label>
          <select name="role" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400 transition-colors">
            <option value="super_admin">Super Admin (Full Access)</option>
            <option value="support_admin">Support Admin (Limited)</option>
          </select>
        </div>
        <button type="submit" class="w-full py-3 btn-cta rounded-xl shadow-sm text-sm">Send Invitation</button>
      </form>
    </div>
  </div>
</main>
<?php require_once __DIR__ . '/footer.php'; ?>
