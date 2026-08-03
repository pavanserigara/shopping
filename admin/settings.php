<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_super_admin_auth();

$pdo = getDBConnection();

// Ensure single-row settings table exists with id = 1
$settings = $pdo->query("SELECT * FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
if (!$settings) {
    $pdo->exec("INSERT INTO platform_settings (id, site_name, support_contact_number, whatsapp_contact, site_logo_url, primary_color, accent_color) VALUES (1, 'LocalShopOS', '+917676446647', '917676446647', '/assets/logo.png', '#f5b400', '#f5b400')");
    $settings = $pdo->query("SELECT * FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
}

// Handle Form Submission (Native Browser Submit, No AJAX required)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteName       = trim($_POST['site_name'] ?? '');
    $supportContact = trim($_POST['support_contact_number'] ?? '');
    $whatsappContact = trim($_POST['whatsapp_contact'] ?? '');
    $siteLogoUrl    = trim($_POST['site_logo_url'] ?? '/assets/logo.png');
    $primaryColor   = trim($_POST['primary_color'] ?? '#f5b400');
    $accentColor    = trim($_POST['accent_color'] ?? '#f5b400');

    $errors = [];

    if (empty($siteName)) {
        $errors[] = "Site Name cannot be empty.";
    }
    if (empty($supportContact)) {
        $errors[] = "Support contact number cannot be empty.";
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) {
        $errors[] = "Primary color must be a valid 6-digit HEX color code (e.g. #F5B400).";
    }
    if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $accentColor)) {
        $errors[] = "Accent color must be a valid 6-digit HEX color code (e.g. #F5B400).";
    }

    if (!empty($errors)) {
        set_flash('error', implode(' ', $errors));
        header("Location: /admin/settings.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE platform_settings SET 
                site_name = ?, 
                support_contact_number = ?, 
                whatsapp_contact = ?, 
                site_logo_url = ?, 
                primary_color = ?, 
                accent_color = ? 
            WHERE id = 1
        ");
        $stmt->execute([
            $siteName,
            $supportContact,
            $whatsappContact,
            $siteLogoUrl,
            $primaryColor,
            $accentColor
        ]);

        set_flash('success', 'Platform settings saved successfully.');
        header("Location: /admin/settings.php?_t=" . time());
        exit;
    } catch (Exception $e) {
        set_flash('error', 'Database error: ' . $e->getMessage());
        header("Location: /admin/settings.php");
        exit;
    }
}

// Read fresh values from single-row platform_settings (id = 1)
$settings = $pdo->query("SELECT * FROM platform_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);

$siteName       = $settings['site_name'] ?? 'LocalShopOS';
$supportContact = $settings['support_contact_number'] ?? '+917676446647';
$whatsappContact = $settings['whatsapp_contact'] ?? '917676446647';
$siteLogoUrl    = $settings['site_logo_url'] ?? '/assets/logo.png';
$primaryColor   = $settings['primary_color'] ?? '#f5b400';
$accentColor    = $settings['accent_color'] ?? '#f5b400';

$pageTitle = "Platform Settings — Super Admin";
require_once __DIR__ . '/header.php';
?>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1">
  <div class="mb-6">
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">⚙️ Platform Settings</h1>
    <p class="text-xs text-slate-500 font-medium mt-1">Configure global branding and contact information across all stores.</p>
  </div>

  <div class="app-card p-6 sm:p-8">
    <h2 class="text-lg font-black text-slate-900 mb-6 border-b border-slate-100 pb-4">Branding & Contact Info</h2>

    <form method="POST" action="/admin/settings.php" data-no-ajax class="space-y-5">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
          <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Site Name</label>
          <input type="text" id="site_name" name="site_name" value="<?= htmlspecialchars($siteName) ?>" required
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400 transition-colors"
                 placeholder="LocalShopOS">
        </div>

        <div>
          <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Support Contact Number (Display)</label>
          <input type="text" id="support_contact_number" name="support_contact_number" value="<?= htmlspecialchars($supportContact) ?>" required
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400 transition-colors"
                 placeholder="+91-1800-123-456">
          <p class="text-[10px] text-slate-400 mt-1">Shown in landing page footer & contact section</p>
        </div>
      </div>

      <!-- WhatsApp Contact Number -->
      <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200">
        <label class="block text-[11px] font-black text-emerald-900 mb-1.5 uppercase tracking-wide">📱 WhatsApp Contact Number (Upgrade & Support)</label>
        <p class="text-[10px] text-emerald-700 font-semibold mb-2">This number receives upgrade requests from merchant dashboards & landing page CTAs. Enter with country code, no + or spaces.</p>
        <input type="text" id="whatsapp_contact" name="whatsapp_contact" value="<?= htmlspecialchars($whatsappContact) ?>"
               class="w-full px-4 py-2.5 bg-white border border-emerald-300 rounded-xl text-sm font-bold focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-400 transition-colors"
               placeholder="919876543210">
        <p class="text-[10px] text-emerald-600 mt-1 font-bold">Example: <code>919876543210</code> for India +91 number</p>
      </div>

      <div>
        <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Site Logo URL (SVG/PNG)</label>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
            <img id="logoPreview" src="<?= htmlspecialchars($siteLogoUrl) ?>" alt="Logo Preview"
                 class="w-6 h-6 object-contain" onerror="this.src='/assets/logo.png'">
          </div>
          <input type="text" id="site_logo_url" name="site_logo_url" value="<?= htmlspecialchars($siteLogoUrl) ?>"
                 class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-bold focus:outline-none focus:border-brand-400 focus:ring-1 focus:ring-brand-400 transition-colors"
                 placeholder="/assets/logo.png">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-5 pt-2">
        <div>
          <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Primary Color (Brand)</label>
          <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1.5 pr-4">
            <input type="color" id="primary_color" name="primary_color" value="<?= htmlspecialchars($primaryColor) ?>"
                   class="w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
            <span id="primaryColorLabel" class="text-xs font-mono font-bold text-slate-500 flex-1 uppercase"><?= htmlspecialchars($primaryColor) ?></span>
          </div>
        </div>
        <div>
          <label class="block text-[11px] font-black text-slate-700 mb-1.5 uppercase tracking-wide">Accent Color (CTA)</label>
          <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl p-1.5 pr-4">
            <input type="color" id="accent_color" name="accent_color" value="<?= htmlspecialchars($accentColor) ?>"
                   class="w-8 h-8 rounded cursor-pointer border-0 p-0 bg-transparent">
            <span id="accentColorLabel" class="text-xs font-mono font-bold text-slate-500 flex-1 uppercase"><?= htmlspecialchars($accentColor) ?></span>
          </div>
        </div>
      </div>

      <div class="pt-6 mt-6 border-t border-slate-100 text-right">
        <button type="submit" id="saveSettingsBtn" class="px-8 py-3 btn-cta rounded-xl shadow-sm text-sm font-black transition-all">
          Save Platform Settings
        </button>
      </div>
    </form>
  </div>
</main>

<script>
// Live previews for color pickers and logo URL input
document.getElementById('primary_color').addEventListener('input', function() {
  document.getElementById('primaryColorLabel').textContent = this.value.toUpperCase();
});
document.getElementById('accent_color').addEventListener('input', function() {
  document.getElementById('accentColorLabel').textContent = this.value.toUpperCase();
});
document.getElementById('site_logo_url').addEventListener('input', function() {
  document.getElementById('logoPreview').src = this.value || '/assets/logo.png';
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
