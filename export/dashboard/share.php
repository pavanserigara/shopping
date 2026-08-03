<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/features.php';

require_tenant_auth();

$pdo = getDBConnection();
$tenantId = (int)$_SESSION['tenant_id'];

// Fetch Tenant Details
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
$stmt->execute([$tenantId]);
$tenant = $stmt->fetch();

$shopName = $tenant['shop_name'];
$subdomain = $tenant['subdomain'];
$logoUrl = $tenant['logo_url'] ?? null;
$phone = $tenant['phone'] ?? '';

// Determine Full Storefront URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';
$storeUrl = "{$protocol}{$host}/{$subdomain}";

// ── FEATURE GATE ── QR Code Studio is a subscription-gated feature ──
$canUseQrCode = tenant_has_feature($pdo, $tenant, 'qr_code_generator');

$pageTitle = "Branded QR Code Studio — Merchant Dashboard";
require_once __DIR__ . '/header.php';

// Show locked notice if plan does not include QR feature
if (!$canUseQrCode) {
    render_locked_feature_notice('qr_code_generator');
    require_once __DIR__ . '/footer.php';
    exit;
}
?>

<!-- QRCode.js Library (Local + CDN Fallback) -->
<script src="/assets/js/qrcode.min.js"></script>
<script>
  if (typeof QRCode === 'undefined') {
    document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"><\/script>');
  }
</script>

<style>
  @media print {
    body * { visibility: hidden; }
    #printableQrPoster, #printableQrPoster * { visibility: visible; }
    #printableQrPoster { position: absolute; left: 0; top: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
  }
</style>

<div class="max-w-5xl mx-auto space-y-8">
  
  <!-- Hero Section -->
  <div class="relative overflow-hidden bg-gradient-to-r from-slate-900 via-slate-800 to-amber-950 p-6 sm:p-8 rounded-3xl border border-amber-500/20 shadow-2xl text-white">
    <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    
    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
      <div>
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-black uppercase tracking-widest bg-amber-400/10 text-amber-400 border border-amber-400/30 mb-3">
          <span>✨ Studio Quality QR Generator</span>
        </div>
        <h1 class="text-2xl sm:text-4xl font-black tracking-tight text-white">
          Branded QR Counter Display
        </h1>
        <p class="text-xs sm:text-sm text-slate-300 font-medium mt-2 max-w-xl leading-relaxed">
          Create high-resolution, print-ready QR codes for your shop counter, flyers, or WhatsApp status. Fully customized with your shop logo & gold accents.
        </p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <button onclick="window.print()" class="px-5 py-3 bg-white text-slate-950 hover:bg-slate-100 font-black text-xs rounded-2xl shadow-xl flex items-center space-x-2 touch-target transition-all transform hover:-translate-y-0.5">
          <span>🖨️ Print Standee Poster</span>
        </button>
        <a href="<?= htmlspecialchars($storeUrl) ?>" target="_blank" class="px-5 py-3 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-xs rounded-2xl shadow-xl flex items-center space-x-2 touch-target transition-all transform hover:-translate-y-0.5">
          <span>Visit Shop &rarr;</span>
        </a>
      </div>
    </div>
  </div>

  <!-- Interactive Studio Workbench -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Left Column: Canvas Preview Box & Theme Switcher -->
    <div class="lg:col-span-7 space-y-6">
      
      <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-xl text-center space-y-6">
        
        <!-- Theme Selection Pills -->
        <div class="space-y-2">
          <span class="text-[11px] font-black uppercase text-slate-400 tracking-wider block">Select Visual Theme</span>
          <div class="flex flex-wrap items-center justify-center gap-2">
            <button onclick="switchTheme('gold')" id="btnThemeGold" class="px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-900 text-amber-400 border-2 border-amber-400 shadow">
              👑 Gold Luxury
            </button>
            <button onclick="switchTheme('emerald')" id="btnThemeEmerald" class="px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-100 text-slate-700 hover:bg-emerald-50 border border-slate-200">
              💚 WhatsApp Green
            </button>
            <button onclick="switchTheme('obsidian')" id="btnThemeObsidian" class="px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-100 text-slate-700 hover:bg-slate-800 border border-slate-200">
              🌑 Obsidian Dark
            </button>
            <button onclick="switchTheme('minimal')" id="btnThemeMinimal" class="px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200">
              ⚪ Minimal Retail
            </button>
          </div>
        </div>

        <!-- Hidden QRCode.js Engine Container -->
        <div id="qrcodeRaw" class="hidden"></div>

        <!-- Canvas Container with Glass Card Shadow -->
        <div id="printableQrPoster" class="inline-block relative p-4 sm:p-6 bg-gradient-to-b from-slate-900 to-slate-950 rounded-3xl border-4 border-amber-400/40 shadow-2xl transition-all">
          <canvas id="qrCanvas" class="w-72 h-72 sm:w-96 sm:h-96 mx-auto rounded-2xl shadow-2xl"></canvas>
        </div>

        <!-- Copy Link & Quick Actions Bar -->
        <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 flex items-center justify-between gap-3">
          <div class="flex items-center space-x-2 truncate">
            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
            <span class="text-xs font-mono font-bold text-slate-800 truncate"><?= htmlspecialchars($storeUrl) ?></span>
          </div>
          <button onclick="copyShopLink()" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-black text-xs rounded-xl shrink-0 shadow transition-all">
            Copy Link
          </button>
        </div>

        <!-- Download & Native Share Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
          <button onclick="downloadQrCode('png')" class="py-4 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black text-xs rounded-2xl shadow-lg flex items-center justify-center space-x-2 touch-target transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Download 1000x1000 PNG</span>
          </button>

          <button onclick="shareShopLink()" class="py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-2xl shadow-lg flex items-center justify-center space-x-2 touch-target transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
            <span>Share on WhatsApp</span>
          </button>
        </div>

      </div>

    </div>

    <!-- Right Column: Features & High-Resolution Display Tips -->
    <div class="lg:col-span-5 space-y-6">
      
      <div class="bg-white p-6 sm:p-7 rounded-3xl border border-slate-200 shadow-md space-y-5">
        <h3 class="font-black text-slate-900 text-base flex items-center gap-2">
          <span>🏆</span> High-Resolution Print Ready
        </h3>
        
        <div class="space-y-4">
          <div class="p-4 rounded-2xl bg-amber-50/60 border border-amber-200/60 flex items-start space-x-3">
            <div class="w-8 h-8 rounded-xl bg-amber-500 text-slate-950 font-black text-sm flex items-center justify-center shrink-0">
              1
            </div>
            <div>
              <h4 class="font-black text-xs text-amber-950 uppercase tracking-wide">Acrylic Desk Stand</h4>
              <p class="text-xs text-slate-600 font-medium mt-0.5">Click <strong>Print Standee Poster</strong> or download PNG to print an A5/A4 acrylic counter display.</p>
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-emerald-50/60 border border-emerald-200/60 flex items-start space-x-3">
            <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white font-black text-sm flex items-center justify-center shrink-0">
              2
            </div>
            <div>
              <h4 class="font-black text-xs text-emerald-950 uppercase tracking-wide">WhatsApp Status & Stories</h4>
              <p class="text-xs text-slate-600 font-medium mt-0.5">Post the high-res QR image directly on your business WhatsApp status for instant online orders.</p>
            </div>
          </div>

          <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-start space-x-3">
            <div class="w-8 h-8 rounded-xl bg-slate-900 text-white font-black text-sm flex items-center justify-center shrink-0">
              3
            </div>
            <div>
              <h4 class="font-black text-xs text-slate-900 uppercase tracking-wide">Shopping Bag Stickers</h4>
              <p class="text-xs text-slate-600 font-medium mt-0.5">Attach printed 2x2" QR stickers on every customer parcel to convert walk-ins to repeat online buyers.</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Scannability Specs -->
      <div class="bg-gradient-to-br from-slate-900 to-slate-950 p-6 rounded-3xl border border-slate-800 text-white space-y-3 shadow-xl">
        <div class="flex items-center justify-between">
          <span class="text-[11px] font-black uppercase text-amber-400 tracking-wider">Tech Spec</span>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Level H Error-Correction</span>
        </div>
        <h4 class="font-black text-sm text-white">Ultra-Crisp Mobile Camera Scan</h4>
        <p class="text-xs text-slate-400 leading-relaxed font-medium">
          Built with 1000x1000px high-contrast matrices. Even if printed on glossy paper under store lighting, smartphone cameras will decode your store link instantly.
        </p>
      </div>

    </div>

  </div>

</div>

<!-- QR Generator Engine Script -->
<script>
const STORE_URL = <?= json_encode($storeUrl) ?>;
const SHOP_NAME = <?= json_encode($shopName) ?>;
const LOGO_URL = <?= json_encode($logoUrl) ?>;

let currentTheme = 'gold';

const THEMES = {
  gold: {
    bg: '#0F172A',
    headerBg: '#F5B400',
    headerText: '#0F172A',
    qrDark: '#0F172A',
    qrLight: '#FFFFFF',
    cornerColor: '#F5B400',
    footerBg: '#0F172A',
    footerTitle: '#F5B400',
    footerSub: '#FFFFFF',
    badgeText: '✓ VERIFIED LOCALSHOP STORE'
  },
  emerald: {
    bg: '#064E3B',
    headerBg: '#059669',
    headerText: '#FFFFFF',
    qrDark: '#064E3B',
    qrLight: '#FFFFFF',
    cornerColor: '#10B981',
    footerBg: '#064E3B',
    footerTitle: '#34D399',
    footerSub: '#ECFDF5',
    badgeText: '💬 ORDER DIRECT ON WHATSAPP'
  },
  obsidian: {
    bg: '#18181B',
    headerBg: '#27272A',
    headerText: '#F4F4F5',
    qrDark: '#09090B',
    qrLight: '#FFFFFF',
    cornerColor: '#E4E4E7',
    footerBg: '#09090B',
    footerTitle: '#F4F4F5',
    footerSub: '#A1A1AA',
    badgeText: '🛍️ DIGITAL STOREFRONT'
  },
  minimal: {
    bg: '#FFFFFF',
    headerBg: '#F8FAFC',
    headerText: '#0F172A',
    qrDark: '#0F172A',
    qrLight: '#FFFFFF',
    cornerColor: '#475569',
    footerBg: '#F1F5F9',
    footerTitle: '#0F172A',
    footerSub: '#475569',
    badgeText: '🏪 SCAN TO ORDER'
  }
};

document.addEventListener('DOMContentLoaded', () => {
  generateBrandedQrCode();
});

function switchTheme(themeKey) {
  currentTheme = themeKey;
  
  // Update button active state classes
  const buttons = {
    gold: 'btnThemeGold',
    emerald: 'btnThemeEmerald',
    obsidian: 'btnThemeObsidian',
    minimal: 'btnThemeMinimal'
  };

  Object.keys(buttons).forEach(k => {
    const btn = document.getElementById(buttons[k]);
    if (!btn) return;
    if (k === themeKey) {
      btn.className = 'px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-900 text-amber-400 border-2 border-amber-400 shadow-md scale-105';
    } else {
      btn.className = 'px-4 py-2 rounded-xl text-xs font-black transition-all bg-slate-100 text-slate-700 hover:bg-slate-200 border border-slate-200';
    }
  });

  generateBrandedQrCode();
}

function generateBrandedQrCode() {
  const rawContainer = document.getElementById('qrcodeRaw');
  if (!rawContainer) return;
  rawContainer.innerHTML = '';

  if (typeof QRCode === 'undefined') {
    console.error('QRCode library failed to load.');
    return;
  }

  const theme = THEMES[currentTheme] || THEMES.gold;

  // Generate base QR matrix
  new QRCode(rawContainer, {
    text: STORE_URL,
    width: 650,
    height: 650,
    colorDark: theme.qrDark,
    colorLight: theme.qrLight,
    correctLevel: QRCode.CorrectLevel.H
  });

  // Poll for generated canvas/img element
  let attempts = 0;
  const pollTimer = setInterval(() => {
    attempts++;
    const qrSource = rawContainer.querySelector('canvas') || rawContainer.querySelector('img');
    
    if (qrSource || attempts > 30) {
      clearInterval(pollTimer);
      if (qrSource) {
        renderStudioCanvas(qrSource, theme);
      }
    }
  }, 50);
}

function renderStudioCanvas(qrSource, theme) {
  const canvas = document.getElementById('qrCanvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  
  // Set high-resolution 1000x1000 canvas output
  const size = 1000;
  canvas.width = size;
  canvas.height = size;

  // 1. Fill Outer Canvas Background
  ctx.fillStyle = theme.bg;
  ctx.fillRect(0, 0, size, size);

  // 2. Render Top Header Banner & Shop Name
  ctx.fillStyle = theme.headerBg;
  ctx.fillRect(0, 0, size, 140);

  ctx.fillStyle = theme.headerText;
  ctx.font = "900 44px 'Plus Jakarta Sans', sans-serif";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillText(SHOP_NAME, size / 2, 55);

  // Sub-badge under Header Text
  ctx.font = "800 20px 'Plus Jakarta Sans', sans-serif";
  ctx.fillStyle = theme.headerText;
  ctx.globalAlpha = 0.85;
  ctx.fillText(theme.badgeText, size / 2, 100);
  ctx.globalAlpha = 1.0;

  // 3. Render QR Code Matrix Container Box
  const qrSize = 650;
  const qrX = (size - qrSize) / 2;
  const qrY = 175;

  // Draw White background container with rounded corners
  ctx.fillStyle = "#FFFFFF";
  drawRoundedRect(ctx, qrX - 20, qrY - 20, qrSize + 40, qrSize + 40, 32);
  ctx.fill();

  // Draw Matrix
  ctx.drawImage(qrSource, qrX, qrY, qrSize, qrSize);

  // 4. Draw Custom Styled Corner Accent Frames
  const cornerSize = 165;
  ctx.lineWidth = 14;
  ctx.strokeStyle = theme.cornerColor;
  
  // Top-Left Corner Frame
  ctx.strokeRect(qrX + 6, qrY + 6, cornerSize, cornerSize);
  // Top-Right Corner Frame
  ctx.strokeRect(qrX + qrSize - cornerSize - 6, qrY + 6, cornerSize, cornerSize);
  // Bottom-Left Corner Frame
  ctx.strokeRect(qrX + 6, qrY + qrSize - cornerSize - 6, cornerSize, cornerSize);

  // 5. Draw Center Embedded Logo / Shop Initial Badge
  const centerSize = 150;
  const centerX = size / 2;
  const centerY = qrY + (qrSize / 2);

  ctx.save();
  ctx.beginPath();
  ctx.arc(centerX, centerY, centerSize / 2 + 12, 0, Math.PI * 2);
  ctx.fillStyle = "#FFFFFF";
  ctx.fill();
  ctx.lineWidth = 6;
  ctx.strokeStyle = theme.cornerColor;
  ctx.stroke();

  if (LOGO_URL && LOGO_URL.trim() !== '') {
    const logoImg = new Image();
    logoImg.crossOrigin = "Anonymous";
    logoImg.onload = () => {
      ctx.save();
      ctx.beginPath();
      ctx.arc(centerX, centerY, centerSize / 2, 0, Math.PI * 2);
      ctx.clip();
      ctx.drawImage(logoImg, centerX - centerSize / 2, centerY - centerSize / 2, centerSize, centerSize);
      ctx.restore();
      drawFooterBox(ctx, size, theme);
    };
    logoImg.onerror = () => {
      drawCenterInitial(ctx, centerX, centerY, centerSize, theme);
      drawFooterBox(ctx, size, theme);
    };
    logoImg.src = LOGO_URL;
  } else {
    drawCenterInitial(ctx, centerX, centerY, centerSize, theme);
    drawFooterBox(ctx, size, theme);
  }
}

function drawCenterInitial(ctx, centerX, centerY, centerSize, theme) {
  ctx.beginPath();
  ctx.arc(centerX, centerY, centerSize / 2, 0, Math.PI * 2);
  ctx.fillStyle = theme.cornerColor;
  ctx.fill();

  ctx.fillStyle = theme.headerText;
  ctx.font = "900 64px 'Plus Jakarta Sans', sans-serif";
  ctx.textAlign = "center";
  ctx.textBaseline = "middle";
  ctx.fillText(SHOP_NAME.charAt(0).toUpperCase(), centerX, centerY);
}

function drawFooterBox(ctx, size, theme) {
  ctx.fillStyle = theme.footerBg;
  ctx.fillRect(0, 860, size, 140);

  ctx.fillStyle = theme.footerTitle;
  ctx.font = "900 30px 'Plus Jakarta Sans', sans-serif";
  ctx.textAlign = "center";
  ctx.textBaseline = "alphabetic";
  ctx.fillText("SCAN TO ORDER ON WHATSAPP", size / 2, 910);

  ctx.fillStyle = theme.footerSub;
  ctx.font = "700 22px 'Plus Jakarta Sans', sans-serif";
  ctx.fillText(STORE_URL, size / 2, 952);
}

function drawRoundedRect(ctx, x, y, width, height, radius) {
  ctx.beginPath();
  ctx.moveTo(x + radius, y);
  ctx.lineTo(x + width - radius, y);
  ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
  ctx.lineTo(x + width, y + height - radius);
  ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
  ctx.lineTo(x + radius, y + height);
  ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
  ctx.lineTo(x, y + radius);
  ctx.quadraticCurveTo(x, y, x + radius, y);
  ctx.closePath();
}

function downloadQrCode(format) {
  const canvas = document.getElementById('qrCanvas');
  if (!canvas) return;
  const image = canvas.toDataURL("image/png").replace("image/png", "image/octet-stream");
  const link = document.createElement('a');
  link.download = `LocalShopOS-${SHOP_NAME.replace(/[^a-z0-9]/gi, '_')}-${currentTheme}-QRCode.png`;
  link.href = image;
  link.click();
  showAdminToast("✓ Downloaded 1000x1000px high-resolution QR code!");
}

function copyShopLink() {
  navigator.clipboard.writeText(STORE_URL).then(() => {
    showAdminToast("✓ Storefront link copied to clipboard!");
  }).catch(() => {
    showAdminToast("Failed to copy link.", true);
  });
}

function shareShopLink() {
  if (navigator.share) {
    navigator.share({
      title: `${SHOP_NAME} — Online WhatsApp Store`,
      text: `Order items directly online from ${SHOP_NAME}!`,
      url: STORE_URL,
    }).catch((err) => console.log('Share canceled:', err));
  } else {
    copyShopLink();
  }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
