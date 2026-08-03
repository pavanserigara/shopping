</main>

<footer class="bg-white border-t border-slate-200 py-4 mt-auto">
  <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500 font-medium">
    LocalShopOS Super Admin Master Console. Platform status: 99.9% Operational.
  </div>
</footer>

<script>
// Global AJAX Form Interceptor across Super Admin Console
document.addEventListener('submit', function(e) {
  const form = e.target;
  if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-no-ajax')) return;

  e.preventDefault();
  const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('input[type="submit"]');
  if (submitBtn) submitBtn.disabled = true;

  const formData = new FormData(form);
  formData.append('is_ajax', '1');

  fetch(form.action || window.location.href, {
    method: form.method || 'POST',
    body: formData,
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(res => res.text())
  .then(text => {
    let data;
    try {
      data = JSON.parse(text);
    } catch(err) {
      data = { success: true, reload: true };
    }
    if (submitBtn) submitBtn.disabled = false;
    if (data.success) {
      if (typeof showAdminToast === 'function') {
        showAdminToast(data.message || "Platform settings updated");
      }
      // Hide active modals if present
      const modal1 = document.getElementById('tenantControlModal');
      const modal2 = document.getElementById('logPaymentModal');
      const modal3 = document.getElementById('planModal');
      const modal4 = document.getElementById('resetPasswordModal');
      if (modal1) modal1.classList.add('hidden');
      if (modal2) modal2.classList.add('hidden');
      if (modal3) modal3.classList.add('hidden');
      if (modal4) modal4.classList.add('hidden');

      if (data.reload) {
        const freshUrl = window.location.pathname + '?_t=' + Date.now();
        setTimeout(() => window.location.replace(freshUrl), 800);
      }
    } else {
      if (typeof showAdminToast === 'function') {
        showAdminToast(data.error || data.message || "Failed to update platform settings", true);
      } else {
        alert(data.error || "Failed to update platform settings");
      }
    }
  })
  .catch(err => {
    if (submitBtn) submitBtn.disabled = false;
    console.error("AJAX submit error:", err);
    window.location.href = window.location.pathname;
  });
});
</script>
</body>
</html>
