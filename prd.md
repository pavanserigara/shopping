# Build prompt — Local Shop OS

Paste everything below into Antigravity as your project brief.

---

You are building **Local Shop OS**, a multi-tenant SaaS platform that lets small local shops (kirana stores, restaurants, tailors, hardware shops) manage products and orders, and lets their customers order via WhatsApp or a simple storefront page — with a super admin panel for the platform owner.

## Tech stack (use exactly this)
- Backend: PHP with PDO, MySQL
- Frontend: JavaScript, AJAX, Tailwind CSS (no frontend framework — server-rendered pages with AJAX for dynamic parts)
- No paid APIs for MVP — WhatsApp ordering uses `wa.me` click-to-chat links, not the WhatsApp Business API
- No payment gateway for MVP — billing status is tracked manually by the super admin

## Multi-tenancy model
Single shared MySQL database. Every tenant-owned table has a `tenant_id` column. Every query touching tenant data MUST be scoped by `tenant_id` — enforce this at the data-access layer, not just in individual queries, so it's impossible to forget.

## Database schema to implement first
```sql
tenants
  id, shop_name, subdomain, whatsapp_number, plan_status, created_at

products
  id, tenant_id, name, price, stock_count, photo_url, category, is_active, created_at

orders
  id, tenant_id, customer_contact, total, status, created_at

order_items
  id, order_id, product_id, quantity, price_at_order

admin_users
  id, tenant_id (nullable — null means super admin), role, email, password_hash, created_at
```

## Roles
1. **Super admin** (tenant_id is null) — sees every tenant, can activate/suspend a tenant, sees platform-wide order/revenue stats, manages ad-campaign tracking.
2. **Tenant admin** (shop owner) — sees and manages only their own shop's products, orders, and sales report. Cannot see other tenants' data under any circumstance.
3. **Customer** — no login. Browses a public storefront and places an order.

## Build in this order — build and verify each phase before starting the next

### Phase 0 — Platform landing page
This is the public marketing page at the root domain (not a shop's storefront — this is the page that sells the *platform itself* to prospective shop owners).
- Hero section: headline + subheadline explaining the offer ("Get your shop online and take orders on WhatsApp in 5 minutes"), with a primary CTA button to "Start free trial"
- How it works: 3-step visual (add products → share your shop link → get orders on WhatsApp)
- Features section: product catalog, WhatsApp ordering, order inbox, sales reports — short, benefit-focused copy, not a feature dump
- Pricing section: free 15-day trial, then ₹499–999/month — one simple plan, no confusing tiers
- Social proof section: placeholder for shop owner testimonials/logos (leave as clearly marked placeholder content until real shops are onboarded)
- Footer: contact/WhatsApp number for support, simple links
- CTA buttons throughout link to the signup form from Phase 1
- Mobile-first — most visitors will click through from a WhatsApp or Instagram link on their phone
- Keep this page static/server-rendered (no dashboard logic) so it loads fast

### Phase 1 — Tenant signup + core dashboard shell
- Signup form: shop name, category, WhatsApp number, owner email, password
- On signup, auto-generate a unique `subdomain` slug from the shop name
- Tenant login (session-based auth, password hashed with `password_hash`)
- Empty dashboard shell with nav: Products, Orders, Sales, Settings

### Phase 2 — Product management
- Product list, add/edit/delete form (name, price, stock_count, photo upload, category, is_active toggle)
- Photo upload: store in `/uploads/{tenant_id}/`, save the path in `photo_url`
- All product queries scoped by the logged-in tenant's `tenant_id`

### Phase 3 — Public storefront + WhatsApp ordering
- Public page at `/shop/{subdomain}` — no login required
- Shows only `is_active = true` products for that tenant
- Simple cart (client-side, JS, no login) — add items, adjust quantity
- "Order via WhatsApp" button: builds a `https://wa.me/{tenant_whatsapp_number}?text={encoded cart summary}` link
- On click, also POST the order to the backend to create an `orders` + `order_items` record with status `new`, so it appears in the tenant's order inbox

**UI/UX bar for this page: it must look and feel like a real e-commerce site (Amazon/Flipkart-grade), not a basic form.** This is the single most important page for customer trust — build it accordingly.

- **Header**: shop name/logo on the left, a search bar (filters products client-side by name), a cart icon on the right with a live item-count badge. Sticky on scroll.
- **Category navigation**: horizontal scrollable chip/tab row under the header if the shop has multiple categories, so customers can filter fast.
- **Product grid**: responsive card grid — 4–5 columns on desktop, 2 on tablet, 2 (or 1 for large cards) on mobile. Each card: product photo (consistent aspect ratio, object-fit: cover, subtle zoom-on-hover), name, price in bold, stock status ("in stock" / "only X left" / "out of stock" grayed out), and a clear "Add to cart" button.
- **Product detail view**: clicking a card opens a larger view (modal or dedicated page) with a bigger image, full name, price, quantity stepper, and add-to-cart — mirror the Amazon/Flipkart product-page pattern, not a plain popup.
- **Cart**: slide-in drawer or dedicated page listing items with thumbnail, name, quantity +/- controls, per-item subtotal, and a running total pinned at the bottom, followed by the "Order via WhatsApp" CTA.
- **Empty/loading states**: skeleton loaders while products load, a proper empty-cart illustration/message, an empty-search-results message.
- **Visual polish**: consistent spacing scale, a real type hierarchy (product name vs price vs meta text), one accent color used consistently for CTAs and badges, subtle shadows/borders on cards (not flat/bare), rounded corners consistent throughout.
- **Responsiveness**: test and get right at 3 breakpoints minimum — mobile (~375px), tablet (~768px), desktop (~1280px+). Most traffic will be mobile — prioritize that layout first, then scale up.
- **Performance**: lazy-load product images below the fold, compress uploaded photos on the backend so the grid stays fast even on slow mobile connections.
- Use Tailwind CSS utility classes to achieve this — no separate CSS framework needed, but the output must not look like default unstyled Tailwind. Treat spacing, color, and typography choices deliberately, the way a real storefront brand would.

### Phase 4 — Order inbox + sales report
- Tenant dashboard: real-time-ish order inbox (poll every 10–15s via AJAX), each order shows items, total, customer contact, status
- Tenant can update order status: new → accepted → preparing → completed
- Sales report: totals for today / this week / this month, computed from `orders` where `status = completed`

### Phase 5 — Super admin panel
- Separate login flow for `admin_users` with `tenant_id IS NULL`
- Tenant list: shop name, signup date, plan_status, last order date
- Suspend/reactivate a tenant (toggle `plan_status`; a suspended tenant's storefront and dashboard both show a "temporarily unavailable" state)
- Platform-wide stats: total tenants, total orders, total GMV, most active shops

### Phase 6 — Shop open/closed toggle + polish
- Toggle on tenant dashboard that hides the storefront's ordering ability when off (shows "closed" banner instead)
- Basic empty states, form validation, mobile-responsive check on the storefront page specifically (this is what customers use on their phones)

## Non-functional requirements
- All SQL via PDO prepared statements — no string-concatenated queries, anywhere
- All tenant-scoped queries must include `WHERE tenant_id = ?` — flag this explicitly in code comments so it's auditable
- Mobile-first CSS for the storefront page (this is the highest-traffic, least-technical audience)
- No frontend framework, no build step required for the frontend — keep it deployable on cheap shared hosting

## What NOT to build in this pass
- Payment gateway integration
- WhatsApp Business API integration
- Multi-shop-per-owner support
- Native mobile apps
- Automated marketing/ad execution (super admin just tracks campaign notes manually for now)

## Deliverable
A working PHP/MySQL application with the folder structure, schema migration file, the public landing page, and all pages above, runnable locally with a standard LAMP-style setup, plus a short `README.md` explaining how to set it up and seed a demo tenant with sample products.