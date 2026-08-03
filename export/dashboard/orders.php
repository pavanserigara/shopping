<?php
$pageTitle = "Order Inbox Management — LocalShopOS";
require_once __DIR__ . '/header.php';
?>

<div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
  <div>
    <div class="flex items-center space-x-2">
      <h1 class="text-2xl font-black text-slate-900">Order Management Inbox</h1>
      <span class="flex h-2.5 w-2.5 relative">
        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-brand-500"></span>
      </span>
      <span class="text-xs text-amber-800 font-extrabold uppercase tracking-wider">Live Sync</span>
    </div>
    <p class="text-xs text-slate-500 mt-1">Filter, search, track, and update fulfillment status for customer WhatsApp orders</p>
  </div>
  
  <div class="flex items-center space-x-2">
    <button onclick="fetchOrders()" class="px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-brand-50 text-xs font-bold text-slate-900 rounded-xl transition-all flex items-center space-x-1.5 shadow-sm touch-target">
      <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
      <span>Refresh Orders</span>
    </button>
  </div>
</div>

<!-- Filters Bar & Search Box -->
<div class="app-card p-4 rounded-2xl mb-6 space-y-4 bg-white">
  
  <!-- Top Row: Status Pills Filter -->
  <div class="flex items-center space-x-2 overflow-x-auto pb-1">
    <button onclick="filterStatus('ALL', this)" class="status-pill active px-4 py-1.5 rounded-full text-xs font-black bg-slate-900 text-white shadow-sm transition-all whitespace-nowrap">All Orders</button>
    <button onclick="filterStatus('new', this)" class="status-pill px-4 py-1.5 rounded-full text-xs font-extrabold bg-amber-100 text-amber-900 border border-amber-300 hover:bg-amber-200 transition-all whitespace-nowrap">New (<span id="count-new">0</span>)</button>
    <button onclick="filterStatus('accepted', this)" class="status-pill px-4 py-1.5 rounded-full text-xs font-extrabold bg-blue-100 text-blue-900 border border-blue-300 hover:bg-blue-200 transition-all whitespace-nowrap">Accepted</button>
    <button onclick="filterStatus('preparing', this)" class="status-pill px-4 py-1.5 rounded-full text-xs font-extrabold bg-purple-100 text-purple-900 border border-purple-300 hover:bg-purple-200 transition-all whitespace-nowrap">Preparing</button>
    <button onclick="filterStatus('completed', this)" class="status-pill px-4 py-1.5 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-900 border border-emerald-300 hover:bg-emerald-200 transition-all whitespace-nowrap">Completed</button>
    <button onclick="filterStatus('cancelled', this)" class="status-pill px-4 py-1.5 rounded-full text-xs font-extrabold bg-rose-100 text-rose-900 border border-rose-300 hover:bg-rose-200 transition-all whitespace-nowrap">Cancelled</button>
  </div>

  <!-- Bottom Row: Search Input + Date Range + Sorting -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 pt-2 border-t border-slate-100">
    <div class="relative">
      <input type="text" id="orderSearchInput" onkeyup="debounceFetch()" placeholder="Search Order ID or Phone..."
             class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 focus:ring-2 focus:ring-brand-400">
      <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
    </div>

    <div class="flex items-center space-x-1.5">
      <input type="date" id="startDateInput" onchange="fetchOrders()" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900">
      <span class="text-xs text-slate-400 font-bold">to</span>
      <input type="date" id="endDateInput" onchange="fetchOrders()" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900">
    </div>

    <div>
      <select id="sortBySelect" onchange="fetchOrders()" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-900 font-bold">
        <option value="created_at-DESC">Sort: Newest Date First</option>
        <option value="created_at-ASC">Sort: Oldest Date First</option>
        <option value="total-DESC">Sort: Highest Total Bill</option>
        <option value="total-ASC">Sort: Lowest Total Bill</option>
        <option value="status-ASC">Sort: By Status</option>
      </select>
    </div>

    <div>
      <button onclick="resetOrderFilters()" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-colors">
        Reset Filters
      </button>
    </div>
  </div>
</div>

<!-- Orders Table View -->
<div class="app-card rounded-2xl overflow-hidden bg-white">
  <div id="ordersInboxContainer">
    <div class="text-center py-20">
      <div class="inline-block animate-spin w-8 h-8 border-4 border-brand-500 border-t-transparent rounded-full mb-3"></div>
      <p class="text-xs text-slate-500 font-bold">Loading incoming orders...</p>
    </div>
  </div>

  <div id="paginationBar" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
    <span id="pageStats">Showing 0 orders</span>
    <div class="flex items-center space-x-2">
      <button id="prevPageBtn" onclick="changePage(-1)" class="px-3 py-1.5 bg-slate-100 rounded-lg text-slate-800 font-bold disabled:opacity-40">Previous</button>
      <span id="currentPageNum" class="font-black text-slate-900 px-2">Page 1</span>
      <button id="nextPageBtn" onclick="changePage(1)" class="px-3 py-1.5 bg-slate-100 rounded-lg text-slate-800 font-bold disabled:opacity-40">Next</button>
    </div>
  </div>
</div>

<!-- Order Detail Expandable Modal -->
<div id="orderDetailModal" class="hidden fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="app-card w-full max-w-xl p-6 rounded-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto bg-white space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <div>
        <h3 class="text-base font-black text-slate-900 flex items-center gap-2">
          <span>Order Detail Breakdown</span>
          <span id="modalOrderId" class="font-mono text-amber-800">#0</span>
        </h3>
        <p id="modalOrderTime" class="text-xs text-slate-500 font-medium"></p>
      </div>
      <button onclick="document.getElementById('orderDetailModal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 font-bold text-lg">&times;</button>
    </div>

    <!-- Contact & Fulfillment Header Card -->
    <div class="bg-brand-50 p-4 rounded-xl border border-brand-200 space-y-3">
      <div class="flex items-center justify-between">
        <div>
          <span class="text-[10px] text-slate-500 block uppercase font-black">Customer Contact</span>
          <h4 id="modalCustomerPhone" class="font-black text-slate-900 text-sm"></h4>
        </div>
        <div class="flex items-center space-x-2">
          <span id="modalFulfillmentBadge" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase"></span>
          <span id="modalPaymentBadge" class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-900 text-white"></span>
        </div>
      </div>

      <!-- Delivery Address Display -->
      <div id="modalDeliveryAddressBox" class="hidden pt-2 border-t border-brand-200/80">
        <span class="text-[10px] text-slate-500 block uppercase font-black">📍 Home Delivery Address</span>
        <p id="modalDeliveryAddressText" class="text-xs font-bold text-slate-800 leading-snug mt-0.5"></p>
      </div>

      <div class="pt-2 flex justify-end">
        <a id="modalWhatsappLink" href="#" target="_blank" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm flex items-center space-x-1 touch-target">
          <span>💬 Chat on WhatsApp</span>
        </a>
      </div>
    </div>

    <h4 class="text-xs font-black text-slate-700 uppercase tracking-wider mb-2">Purchased Items:</h4>
    <div id="modalItemsList" class="space-y-2 mb-4"></div>

    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
      <div>
        <span class="text-xs text-slate-500 block font-bold">Total Bill <span id="modalDeliveryFeeNote" class="text-[10px] text-emerald-700"></span></span>
        <span id="modalOrderTotal" class="text-xl font-black text-emerald-700">₹0.00</span>
      </div>
      <div id="modalActionButtons"></div>
    </div>
  </div>
</div>

<script>
let currentStatusFilter = 'ALL';
let currentPage = 1;
let totalPages = 1;
let ordersCache = [];
let debounceTimer;

function debounceFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    currentPage = 1;
    fetchOrders();
  }, 400);
}

function fetchOrders() {
  const search    = document.getElementById('orderSearchInput').value.trim();
  const startDate = document.getElementById('startDateInput').value;
  const endDate   = document.getElementById('endDateInput').value;
  const sortVal   = document.getElementById('sortBySelect').value.split('-');
  const sortBy    = sortVal[0];
  const sortOrder = sortVal[1];

  const queryParams = new URLSearchParams({
    status: currentStatusFilter,
    search: search,
    start_date: startDate,
    end_date: endDate,
    sort_by: sortBy,
    sort_order: sortOrder,
    page: currentPage
  });

  fetch('/api/fetch_orders.php?' + queryParams.toString())
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        ordersCache = data.orders;
        totalPages  = data.total_pages;
        renderOrdersTable(data);
      }
    })
    .catch(err => console.error("Error fetching orders:", err));
}

function renderOrdersTable(data) {
  const container = document.getElementById('ordersInboxContainer');
  const orders = data.orders;

  document.getElementById('pageStats').innerText = `Showing ${orders.length} of ${data.total_records} orders`;
  document.getElementById('currentPageNum').innerText = `Page ${data.current_page} of ${data.total_pages || 1}`;
  document.getElementById('prevPageBtn').disabled = (data.current_page <= 1);
  document.getElementById('nextPageBtn').disabled = (data.current_page >= data.total_pages);

  if (orders.length === 0) {
    container.innerHTML = `
      <div class="text-center py-16">
        <div class="text-5xl mb-3">📬</div>
        <h3 class="text-base font-black text-slate-900">No orders matching criteria</h3>
        <p class="text-xs text-slate-500 mt-1 font-medium">Try resetting search keywords or date range filters.</p>
      </div>
    `;
    return;
  }

  let html = `
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs font-medium">
        <thead class="bg-brand-50 text-slate-700 uppercase font-black text-[11px] border-b border-brand-200">
          <tr>
            <th class="py-3 px-4">Order ID</th>
            <th class="py-3 px-4">Customer</th>
            <th class="py-3 px-4">Fulfillment</th>
            <th class="py-3 px-4">Payment</th>
            <th class="py-3 px-4">Date & Time</th>
            <th class="py-3 px-4">Total Bill</th>
            <th class="py-3 px-4">Status</th>
            <th class="py-3 px-4 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
  `;

  orders.forEach(order => {
    let badgeClass = 'bg-slate-200 text-slate-700';
    if (order.status === 'new')       badgeClass = 'bg-amber-100 text-amber-900 border border-amber-300 font-black animate-pulse';
    if (order.status === 'accepted')  badgeClass = 'bg-blue-100 text-blue-900 border border-blue-300';
    if (order.status === 'preparing') badgeClass = 'bg-purple-100 text-purple-900 border border-purple-300';
    if (order.status === 'completed') badgeClass = 'bg-emerald-100 text-emerald-900 border border-emerald-300';
    if (order.status === 'cancelled') badgeClass = 'bg-rose-100 text-rose-900 border border-rose-300';

    let isDel = (order.delivery_type === 'delivery');
    let fulBadge = isDel 
      ? '<span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-100 text-emerald-900 border border-emerald-300">🚚 Delivery</span>'
      : '<span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-slate-100 text-slate-800 border border-slate-300">🏬 Pickup</span>';

    let payLabel = '💵 COD';
    if (order.payment_mode === 'upi') payLabel = '📱 UPI';
    if (order.payment_mode === 'pickup_pay') payLabel = '🏬 Store Pay';

    html += `
      <tr class="hover:bg-brand-50/40 transition-colors">
        <td class="py-3.5 px-4 font-mono font-bold text-amber-800">#${order.id}</td>
        <td class="py-3.5 px-4">
          <div class="font-extrabold text-slate-900">+91 ${order.customer_contact}</div>
          ${isDel && order.delivery_address ? `<div class="text-[10px] text-slate-500 truncate max-w-[140px]">${order.delivery_address}</div>` : ''}
        </td>
        <td class="py-3.5 px-4">${fulBadge}</td>
        <td class="py-3.5 px-4 font-bold text-slate-700">${payLabel}</td>
        <td class="py-3.5 px-4 text-slate-500">${order.formatted_time}</td>
        <td class="py-3.5 px-4 font-black text-emerald-700">₹${parseFloat(order.total).toFixed(2)}</td>
        <td class="py-3.5 px-4">
          <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase ${badgeClass}">
            ${order.status}
          </span>
        </td>
        <td class="py-3.5 px-4 text-right">
          <button onclick="openOrderDetailModal(${order.id})" class="px-3 py-1.5 bg-brand-100 hover:bg-brand-200 text-slate-900 text-xs font-black rounded-lg transition-all border border-brand-300">
            View Details
          </button>
        </td>
      </tr>
    `;
  });

  html += `</tbody></table></div>`;
  container.innerHTML = html;
}

function openOrderDetailModal(orderId) {
  const order = ordersCache.find(o => o.id === orderId);
  if (!order) return;

  document.getElementById('modalOrderId').innerText = '#' + order.id;
  document.getElementById('modalOrderTime').innerText = order.formatted_time;
  document.getElementById('modalCustomerPhone').innerText = '+91 ' + order.customer_contact;
  document.getElementById('modalOrderTotal').innerText = '₹' + parseFloat(order.total).toFixed(2);
  document.getElementById('modalWhatsappLink').href = `https://wa.me/91${order.customer_contact}?text=Hi!%20Regarding%20your%20Order%20%23${order.id}%20at%20our%20store.`;

  // Fulfillment & Payment Badges
  const isDel = (order.delivery_type === 'delivery');
  const fulBadge = document.getElementById('modalFulfillmentBadge');
  const addrBox = document.getElementById('modalDeliveryAddressBox');
  const addrText = document.getElementById('modalDeliveryAddressText');

  if (isDel) {
    fulBadge.className = "px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-900 border border-emerald-300";
    fulBadge.innerText = "🚚 Home Delivery";
    if (addrBox && addrText) {
      addrText.innerText = order.delivery_address || 'No address provided';
      addrBox.classList.remove('hidden');
    }
  } else {
    fulBadge.className = "px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-slate-100 text-slate-800 border border-slate-300";
    fulBadge.innerText = "🏬 Store Pickup";
    if (addrBox) addrBox.classList.add('hidden');
  }

  const payBadge = document.getElementById('modalPaymentBadge');
  let payLabel = '💵 COD';
  if (order.payment_mode === 'upi') payLabel = '📱 UPI Pay';
  if (order.payment_mode === 'pickup_pay') payLabel = '🏬 Pay @ Store';
  payBadge.innerText = payLabel;

  const feeNote = document.getElementById('modalDeliveryFeeNote');
  if (feeNote) {
    feeNote.innerText = (isDel && parseFloat(order.delivery_fee) > 0) ? `(Includes ₹${parseFloat(order.delivery_fee).toFixed(2)} Delivery Fee)` : '';
  }

  const itemsContainer = document.getElementById('modalItemsList');
  itemsContainer.innerHTML = order.items.map(item => `
    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 rounded-lg bg-slate-200 overflow-hidden flex items-center justify-center shrink-0">
          ${item.photo_url ? `<img src="${item.photo_url}" class="w-full h-full object-cover">` : '🛍️'}
        </div>
        <div>
          <h5 class="text-xs font-bold text-slate-900">${item.product_name}</h5>
          <p class="text-[11px] text-slate-500 font-medium">${item.quantity} &times; ₹${parseFloat(item.price_at_order).toFixed(2)}</p>
        </div>
      </div>
      <span class="text-xs font-black text-emerald-700">₹${(item.quantity * item.price_at_order).toFixed(2)}</span>
    </div>
  `).join('');

  const btnContainer = document.getElementById('modalActionButtons');
  let btnsHtml = '';
  if (order.status === 'new') {
    btnsHtml = `
      <div class="flex items-center space-x-2">
        <button onclick="updateOrderStatus(${order.id}, 'cancelled')" class="px-3 py-2 text-rose-700 hover:bg-rose-50 text-xs font-bold rounded-xl">Cancel</button>
        <button onclick="updateOrderStatus(${order.id}, 'accepted')" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm">Accept Order</button>
      </div>
    `;
  } else if (order.status === 'accepted') {
    btnsHtml = `<button onclick="updateOrderStatus(${order.id}, 'preparing')" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-sm">Mark Preparing</button>`;
  } else if (order.status === 'preparing') {
    btnsHtml = `<button onclick="updateOrderStatus(${order.id}, 'completed')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm">Mark Completed</button>`;
  } else {
    btnsHtml = `<span class="text-xs font-black text-slate-600 uppercase">Status: ${order.status}</span>`;
  }
  btnContainer.innerHTML = btnsHtml;

  document.getElementById('orderDetailModal').classList.remove('hidden');
}

function updateOrderStatus(orderId, newStatus) {
  fetch('/api/update_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ order_id: orderId, status: newStatus })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('orderDetailModal').classList.add('hidden');
      if (typeof showAdminToast === 'function') {
        showAdminToast(data.message || `Order #${orderId} marked as ${newStatus}`);
      }
      fetchOrders();
    } else {
      if (typeof showAdminToast === 'function') {
        showAdminToast(data.error || "Update failed", true);
      } else {
        alert("Error: " + data.error);
      }
    }
  });
}

function filterStatus(status, btn) {
  currentStatusFilter = status;
  currentPage = 1;
  document.querySelectorAll('.status-pill').forEach(b => {
    b.className = "status-pill px-4 py-1.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-all whitespace-nowrap";
  });
  btn.className = "status-pill active px-4 py-1.5 rounded-full text-xs font-black bg-slate-900 text-white shadow-sm transition-all whitespace-nowrap";
  fetchOrders();
}

function changePage(delta) {
  currentPage += delta;
  if (currentPage < 1) currentPage = 1;
  if (currentPage > totalPages) currentPage = totalPages;
  fetchOrders();
}

function resetOrderFilters() {
  document.getElementById('orderSearchInput').value = '';
  document.getElementById('startDateInput').value = '';
  document.getElementById('endDateInput').value = '';
  document.getElementById('sortBySelect').value = 'created_at-DESC';
  currentStatusFilter = 'ALL';
  currentPage = 1;
  fetchOrders();
}

// Initial Fetch & Auto Refresh Polling
fetchOrders();
setInterval(fetchOrders, 10000);
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
