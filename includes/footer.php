<?php
// Global Platform Footer (WCAG AA Compliant High Contrast Polish)
$siteName       = $platformSettings['site_name'] ?? 'LocalShopOS';
$supportContact = $platformSettings['support_contact_number'] ?? '18001234567';
$siteLogo       = !empty($platformSettings['site_logo_url']) ? $platformSettings['site_logo_url'] : '/assets/logo.png';
?>
<footer class="bg-dark-950 border-t border-white/15 pt-16 pb-12 text-slate-200 relative overflow-hidden shrink-0 mt-auto">
  <!-- Subtle Background Glow -->
  <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[800px] h-[300px] bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-10 mb-12">
      
      <!-- Brand & Mission Column -->
      <div class="md:col-span-2 space-y-4">
        <div class="flex items-center space-x-3">
          <img src="<?= htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($siteName) ?> Logo" class="h-9 w-auto object-contain rounded-xl shadow-md">
          <span class="font-black text-xl text-white tracking-tight"><?= htmlspecialchars($siteName) ?></span>
        </div>
        <p class="text-xs sm:text-sm leading-relaxed text-slate-200 max-w-sm font-medium">
          The simple, zero-commission SaaS operating system for Indian Kiranas, bakeries, and retail shops to build an online storefront and receive direct WhatsApp orders.
        </p>
        <div class="flex items-center space-x-3 pt-2">
          <span class="inline-flex items-center space-x-1.5 px-3.5 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs font-bold">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span>Platform Status: 99.9% Online</span>
          </span>
        </div>
      </div>

      <!-- Navigation Links -->
      <div>
        <h4 class="text-xs font-black uppercase tracking-wider text-white mb-4">Platform</h4>
        <ul class="space-y-2.5 text-xs sm:text-sm font-bold text-slate-200">
          <li><a href="#how-it-works" class="hover:text-brand-400 transition-colors">How It Works</a></li>
          <li><a href="#features" class="hover:text-brand-400 transition-colors">Key Features</a></li>
          <li><a href="#pricing" class="hover:text-brand-400 transition-colors">Subscription Plans</a></li>
          <li><a href="/shops.php" class="hover:text-brand-300 transition-colors text-brand-400 font-extrabold flex items-center gap-1">Explore All Shops &rarr;</a></li>
        </ul>
      </div>

      <!-- Merchant Accounts -->
      <div>
        <h4 class="text-xs font-black uppercase tracking-wider text-white mb-4">Merchants</h4>
        <ul class="space-y-2.5 text-xs sm:text-sm font-bold text-slate-200">
          <li><a href="/login.php" class="hover:text-brand-400 transition-colors">Merchant Login</a></li>
          <li><a href="/signup.php" class="hover:text-brand-400 transition-colors">Create Free Store</a></li>
          <li><a href="/admin/login.php" class="hover:text-brand-400 transition-colors">Super Admin Access</a></li>
        </ul>
      </div>

      <!-- Direct Contact & Support -->
      <div>
        <h4 class="text-xs font-black uppercase tracking-wider text-white mb-4">Support & Contact</h4>
        <p class="text-xs text-slate-200 mb-3 font-semibold">Have questions or need help setting up your store?</p>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $supportContact) ?>" target="_blank" class="inline-flex items-center space-x-2 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-400 text-slate-950 rounded-xl text-xs font-black transition-all shadow-md">
          <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-1.147 4.191 4.225-1.108z"/></svg>
          <span>WhatsApp Helpline</span>
        </a>
        <p class="text-xs font-mono font-bold text-slate-300 mt-2.5">Ph: <?= htmlspecialchars($supportContact) ?></p>
      </div>

    </div>

    <!-- Bottom Legal Bar -->
    <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-300 font-semibold gap-4">
      <p>&copy; <?= date('Y') ?> <?= htmlspecialchars($siteName) ?> Platform. All rights reserved.</p>
      <div class="flex items-center space-x-6 text-slate-200">
        <a href="#terms" onclick="alert('LocalShopOS Platform Terms: Merchants retain full ownership of customer data and product inventory.')" class="hover:text-brand-400 transition-colors">Terms of Service</a>
        <a href="#privacy" onclick="alert('Privacy Policy: LocalShopOS does not sell merchant or customer data.')" class="hover:text-brand-400 transition-colors">Privacy Policy</a>
        <a href="#contact" class="hover:text-brand-400 transition-colors">Contact Support</a>
      </div>
    </div>
  </div>
</footer>

<!-- Global JavaScript for Interactions & Motion -->
<script>
// Enable JS reveal mode dynamically
document.documentElement.classList.add('js-reveal');

// Custom Cursor Follower (Desktop only)
if (matchMedia('(pointer: fine)').matches) {
  const dot = document.getElementById('customCursorDot');
  const ring = document.getElementById('customCursorRing');
  
  if (dot && ring) {
    window.addEventListener('mousemove', (e) => {
      dot.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
      ring.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
    });
    
    document.querySelectorAll('a, button, input, select, textarea, [role="button"]').forEach(el => {
      el.addEventListener('mouseenter', () => document.body.classList.add('cursor-hover'));
      el.addEventListener('mouseleave', () => document.body.classList.remove('cursor-hover'));
    });
  }
}

// Scroll Reveal Observer
const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('is-visible');
    }
  });
}, { threshold: 0.05 });

document.querySelectorAll('.reveal-on-scroll').forEach(el => revealObserver.observe(el));

// Header Background Change on Scroll
window.addEventListener('scroll', () => {
  const header = document.getElementById('mainHeader');
  if (header) {
    if (window.scrollY > 40) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  }
});

// Mobile Nav Toggle
function toggleMobileNav() {
  const drawer = document.getElementById('mobileMenuDrawer');
  if (drawer) drawer.classList.toggle('hidden');
}
</script>

</body>
</html>
