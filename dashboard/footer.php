</main>
<footer class="bg-white border-t border-brand-200 py-6 mt-auto">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:flex sm:items-center sm:justify-between text-xs text-slate-500 font-medium">
    <p>&copy; <?= date('Y') ?> LocalShopOS Merchant Portal.</p>
    <p class="mt-2 sm:mt-0 font-bold text-amber-800">Shop Status: <span class="text-emerald-700">Online & Active</span></p>
  </div>
</footer>

<script>
// Global AJAX Form Interceptor across Merchant Dashboard
document.addEventListener('submit', function(e) {
  const form = e.target;
  if (!form || form.tagName !== 'FORM' || form.hasAttribute('data-no-ajax')) return;

  e.preventDefault();
  const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('input[type="submit"]');
  const originalBtnHtml = submitBtn ? submitBtn.innerHTML : '';
  if (submitBtn) { submitBtn.disabled = true; submitBtn.innerHTML = '⏳ Saving...'; }

  // Hide coupon form inline error from previous attempt
  const inlineErr = document.getElementById('couponFormError');
  if (inlineErr) { inlineErr.classList.add('hidden'); inlineErr.textContent = ''; }

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
      // Non-JSON response: treat as success + reload
      data = { success: true, reload: true, message: 'Saved successfully' };
    }

    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnHtml; }

    if (data.success) {
      const msg = data.message || 'Saved successfully';
      if (typeof showAdminToast === 'function') showAdminToast(msg);

      // Handle store open/close toggle button UI update
      if (data.is_open !== undefined && submitBtn) {
        if (data.is_open) {
          submitBtn.className = "w-full py-3 px-4 rounded-xl text-xs font-black text-white shadow-md flex items-center justify-center space-x-2 transition-all bg-emerald-600 hover:bg-emerald-700";
          submitBtn.innerHTML = '<span>🟢 STORE IS CURRENTLY OPEN</span>';
        } else {
          submitBtn.className = "w-full py-3 px-4 rounded-xl text-xs font-black text-white shadow-md flex items-center justify-center space-x-2 transition-all bg-rose-600 hover:bg-rose-700";
          submitBtn.innerHTML = '<span>🔴 STORE IS CLOSED</span>';
        }
      }

      // Reload page to show fresh DB data
      if (data.reload) {
        document.querySelectorAll('[id$="Modal"]').forEach(m => m.classList.add('hidden'));
        const freshUrl = window.location.pathname + '?_t=' + Date.now();
        setTimeout(() => window.location.replace(freshUrl), 800);
      }
    } else {
      const errMsg = data.error || data.message || 'Something went wrong. Please try again.';

      // Show inline error inside coupon form panel if it exists, else toast
      if (inlineErr && form.id === 'couponCreateForm') {
        inlineErr.textContent = '⚠ ' + errMsg;
        inlineErr.classList.remove('hidden');
      } else if (typeof showAdminToast === 'function') {
        showAdminToast(errMsg, true);
      } else {
        alert(errMsg);
      }
    }
  })
  .catch(err => {
    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnHtml; }
    console.error('AJAX submit error:', err);
    window.location.href = window.location.pathname;
  });
});
</script>
</body>
</html>
