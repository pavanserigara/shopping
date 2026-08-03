# LocalShopOS — Multi-Tenant Hyperlocal E-Commerce Platform

LocalShopOS is a high-performance, mobile-first SaaS platform designed for local kirana stores and businesses to launch instant digital storefronts with WhatsApp ordering, promotional ad banner management, subscription tier feature gating, and super-admin global ad networks.

---

## 🚀 Version 6 (v6) Platform Subscription & Feature Gating Upgrade

LocalShopOS v6 upgrades the platform into a multi-tiered subscription SaaS ecosystem.

### **Key v6 Features Implemented**

1. **Subscription Plan Management & Feature Registry (`includes/features.php`)**
   - Central feature registry (`FEATURE_REGISTRY`) defining capabilities: `product_management`, `product_image_gallery`, `shop_ads`, `ad_analytics`, `order_management`, `sales_reports`, and `shop_logo_upload`.
   - Access control rule: Merchants in trial (`trial_ends_at`) receive full unrestricted platform access. Post-trial merchants are strictly governed by their assigned `plan_id` mapped via `plan_features`.

2. **Super Admin Plan Builder (`admin/plans.php`)**
   - Super Admins can dynamically create and edit unlimited subscription plans (e.g. Free Tier, Premium, Gold VIP).
   - Dynamic feature checklist mapping per plan with price, billing period, and product catalog limit configuration.
   - Designate default post-trial plans for automated tenant onboarding.

3. **Tenant Subscription & Upgrade Portal (`dashboard/plans.php`)**
   - Merchants can view their current subscription status, trial timer, and catalog capacity.
   - Side-by-side plan comparison matrix highlighting included (`✓`) and locked (`✕`) features with one-click WhatsApp upgrade requests.

4. **Locked State UI Component (`render_locked_feature_notice()`)**
   - Premium feature gating across merchant dashboard pages (`/dashboard/products.php`, `/dashboard/ads.php`, `/dashboard/sales.php`, `/dashboard/settings.php`).
   - Clean "Locked State" app-cards informing merchants of gated features with quick upgrade CTAs.

5. **Platform-Wide Global Ad Network (`admin/global_ads.php` & `api/track_global_ad_view.php`)**
   - Super Admin controlled platform-wide advertisement banners rendered across **every** merchant storefront.
   - Independent impression tracking (`global_ad_views`) distinct from tenant-managed ads.
   - Automatic banner combining logic: Global ads render first in storefront carousels and grid slots.

---

## 🛠️ Project Structure & Key Files

```
├── config/
│   └── database.php             # PDO database configuration
├── includes/
│   ├── auth.php                 # Session & auth guards for tenant & admin
│   └── features.php             # Central feature registry & gating logic (v6)
├── admin/
│   ├── index.php                # Super Admin dashboard & tenant plan assignments
│   ├── plans.php                # Plan Builder & Feature Checklist Manager (v6)
│   ├── global_ads.php           # Platform-wide Global Ads Manager (v6)
│   ├── login.php                # Super Admin login
│   └── logout.php               # Super Admin logout
├── dashboard/
│   ├── index.php                # Merchant dashboard overview
│   ├── products.php             # Catalog & multi-photo gallery (Gated v6)
│   ├── ads.php                  # Promotional banner ads & view stats (Gated v6)
│   ├── orders.php               # Order inbox & fulfillment (Gated v6)
│   ├── sales.php                # Sales analytics & revenue ledger (Gated v6)
│   ├── plans.php                # Subscription plan comparison & upgrade CTA (v6)
│   ├── settings.php             # Store profile & logo branding (Gated v6)
│   ├── header.php               # Shared dashboard header with feature lock indicators
│   └── footer.php               # Shared dashboard footer
├── api/
│   ├── place_order.php          # WhatsApp order placement endpoint
│   ├── customer_orders.php     # Customer order tracking endpoint
│   ├── track_ad_view.php        # Tenant ad view impression tracking endpoint
│   └── track_global_ad_view.php # Platform global ad view impression tracking (v6)
├── uploads/                     # Tenant & global ad image uploads
├── schema.sql                   # Full database schema (v1 through v6)
├── seed.php                     # Database seeder (creates default plans & demo data)
├── shop.php                     # Mobile-first customer storefront with global ads
└── shops.php                    # Public directory of active local stores
```

---

## 🔑 Default Credentials

- **Super Admin Credentials**:
  - Email: `admin@localshopos.com`
  - Password: `adminpassword123`
- **Demo Merchant Subdomains**:
  - `/shop/laxmi-kirana` (Laxmi General Store)
  - `/shop/fresh-fruits` (Fresh Fruits & Veggies)
- v8 Upgrades completed: Added platform settings, user management, team management, clean URLs, storefront footers, and SEO.

---

## 🎨 Version 9 (v9) Landing Page Redesign & Motion Architecture

LocalShopOS v9 delivers a high-contrast, dark-mode-accented SaaS public landing page (`/`) designed to match top-tier marketing sites (Stripe, Vercel, Linear).

### **Animation Library & Implementation Details**
- **Zero-Dependency Native Motion Architecture**: To maximize loading speed and eliminate external library overhead, v9 uses native browser `IntersectionObserver` coupled with GPU-accelerated CSS transforms (`translate3d`, `opacity`, `scale`).
- **Scroll-Triggered Reveals (`reveal-on-scroll`)**: HTML sections automatically animate smoothly (`translateY(0)` & `opacity: 1`) as they enter the viewport.
- **Desktop Custom Cursor (`#customCursorDot` / `#customCursorRing`)**: Interactive cursor ring that scales up (`52px`) over clickable elements (`a, button, input`). Degrades gracefully on touch devices via `@media (pointer: coarse)`.
- **Branded Splash Loader (`#splashOverlay`)**: A fast 800ms loading overlay featuring the new golden LocalShopOS brand mark (`/assets/logo.svg` & `/assets/logo.png`) with progress bar transition.
- **Interactive Hero Catalog Simulation**: Live interactive cart buttons (`simulateAddCart`) demonstrating real-time order placement to WhatsApp.

---

## 🚀 Version 10 (v10) Distinctive Design & Layout Refresh

LocalShopOS v10 eliminates generic AI-built SaaS templates by introducing a balanced mix of light/dark sections, a bold full-yellow brand statement block, and bulletproof hero typography.

### **Key v10 Redesign Enhancements**
- **Hero Headline Bug Fix**: Fixed broken rotating word artifact by switching to a rock-solid, high-contrast static headline: `Turn Your Local Shop Into A WhatsApp Storefront`.
- **Real WhatsApp Chat Thread Mockup**: Replaced floating abstract UI cards in the hero with a realistic styled WhatsApp chat conversation demonstrating customer order placement (`₹793 Total`) and instant merchant acceptance (`Order #1042 accepted 🛵`).
- **Balanced Light & Dark Section Rhythm**: At least 50% of the landing page uses clean white/cream backgrounds (`#FFFDF7` & `#FFFFFF`) with dark slate typography (`#0F172A`).
- **Bold Full-Yellow Callout Section (`bg-brand-500 text-slate-950`)**: High-impact full-width saturated gold-yellow section for the "0% Commission Guarantee" statement with giant stats (`₹0`, `100% Data`, `< 5m Setup`).
- **Asymmetric "How It Works" Timeline**: 2-column asymmetric layout with large step numbers (`01 / 02 / 03`) and interactive store link preview card.
- **Typography as Design Element**: Oversized serif/sans quote block highlighting real merchant testimonial.

---

## 🛠️ Version 14 (v14) Coupon System & Universal Instant AJAX Audit

LocalShopOS v14 delivers an end-to-end bug fix pass for the coupon/discount system and a full audit across all tenant and super-admin forms to ensure zero manual page reloads are required anywhere.

### **Root Causes & Systemic Fixes**

1. **JSON Output Polluted by Preceding Output Buffering (Manual Refresh Bug)**
   - **Root Cause**: PHP files in `/dashboard/` and `/admin/` included `header.php` prior to processing `POST` form actions. Because `header.php` rendered HTML layout code into the output stream, subsequent `json_encode()` outputs were prepended with raw HTML. This caused frontend `fetch()` calls to fail JSON parsing, triggering silent error fallbacks or necessitating manual page reloads (`F5`).
   - **Fix**: Implemented `ob_start()` at the top of `dashboard/header.php` and `admin/header.php`. Created `respond_flash()` helper in `includes/auth.php` which executes `if (ob_get_length()) ob_clean();` prior to emitting `application/json` responses. Updated all forms across `/dashboard/` (`coupons.php`, `products.php`, `ads.php`, `settings.php`) and `/admin/` (`plans.php`, `global_ads.php`, `team.php`, `users.php`, `settings.php`, `index.php`) to use `respond_flash()`.

2. **Coupon System & Checkout Pipeline Integration**
   - **Root Cause**: `api/place_order.php` was missing input variable extractions (`$tenantId`, `$couponCode`, `$customerContact`, `$items`) from `php://input`, causing order placements with discount codes to fail silently. Additionally, `api/validate_coupon.php` and `api/place_order.php` needed strict `tenant_id` scoping to prevent coupons created by Shop A from applying to Shop B.
   - **Fix**: Added explicit `tenant_id` scoping to coupon lookup queries in `api/validate_coupon.php` and `api/place_order.php`. Fixed variable extractions in `api/place_order.php`, correctly calculating flat and percentage discounts, floor-limiting order totals to ₹0.00, writing `discount_amount` & `coupon_code` into the `orders` record, and appending discount breakdowns into the customer WhatsApp order payload.



---

## 🛠️ Version 15 (v15) — Coupon System Full Rebuild

### Root Cause (Actual Bug)

Two compounding bugs caused the "success message but coupon not visible" failure:

1. **Schema mismatch** — The `coupons` table used `ENUM('percent','flat')` but the backend code mixed `percent` and `percentage`. MySQL silently truncated mismatched enum inserts to the default, causing zero-value discount_value rejections and false "success" toasts.

2. **Duplicate AJAX interceptors** — `dashboard/settings.php` had its own `querySelectorAll('form')` interceptor that fired alongside the global footer interceptor. It had no `reload` handler and hardcoded "Settings updated successfully" — masking the real server error.

### Rebuild Summary

| Component | Change |
|-----------|--------|
| `coupons` table | Rebuilt: `ENUM('flat','percentage')`, `expires_at DATE NULL`, `UNIQUE KEY (tenant_id, code)` |
| `api/coupon_create.php` | New dedicated endpoint — full validation, scoped PDO insert, always JSON |
| `api/coupon_action.php` | New dedicated endpoint — toggle/delete, returns `coupon_id` for DOM targeting |
| `api/validate_coupon.php` | Rebuilt — percentage type, expires_at, inactive, min-order, specific errors |
| `dashboard/coupons.php` | Rebuilt — live DOM injection, no page reload, inline feedback |
| `shop.php` | `applyCartCoupon()` — uppercase normalize, loading state, network error catch |

### v15 Test Results (7/7 PASS)

```
[PASS] T1-Valid percentage    : TEST10 on tenant 1 ₹200 → -₹20, total ₹180
[PASS] T2-Same code diff tenant: TEST10 on tenant 2 ₹200 → -₹40 (20%, different rate — per-tenant scoping confirmed)
[PASS] T2-Cross-tenant reject : INACTIVE50 on tenant 2 → NOT FOUND (no cross-tenant leakage)
[PASS] T1-Inactive coupon     : INACTIVE50 → rejected (inactive)
[PASS] T1-Expired coupon      : EXPIRED30 (2020-01-01) → rejected (expired)
[PASS] T1-Below min order     : MINORDER (min ₹500) on ₹100 cart → rejected with specific message
[PASS] T1-Flat above min      : MINORDER on ₹600 cart → -₹100, total ₹500
```

---

## 🛠️ Version 16 (v16) — Platform Settings Save Flow Fix

### Root Cause (Actual Bug)

`admin/settings.php` used PHP variables `$siteName`, `$supportContact`, `$siteLogoUrl`, `$primaryColor`, `$accentColor` in the HTML form inputs, but **none of these variables were ever loaded from the database**. The `admin/header.php` does not load `platform_settings` (unlike `includes/header.php` which does). This meant all form fields rendered as empty strings. When the user typed a new value and saved, the POST correctly ran `INSERT ... ON DUPLICATE KEY UPDATE` and persisted to the database — but on page reload the form displayed blank again, making it appear as if nothing saved. The data WAS in the database, but the form was always reading undefined variables.

Additionally, `admin/settings.php` had a duplicate inline `querySelectorAll('form')` AJAX interceptor conflicting with `admin/footer.php`'s global interceptor — same class of bug as fixed in v15 for the coupon form.

### Fix Summary

| Location | Fix |
|----------|-----|
| `admin/settings.php` | Loads all 6 settings from DB via `getPlatformSetting()` before rendering; removed duplicate JS interceptor; form now has explicit `action="/admin/settings.php"` |
| `includes/header.php` | Already correctly loads from DB — confirmed reading live |
| `includes/footer.php` | Already correctly reads `$platformSettings` — confirmed |
| `dashboard/plans.php` | Already reads `whatsapp_contact` live from DB — confirmed |
| `index.php` | Already reads `whatsapp_contact` live from DB — confirmed |
| `shop.php` | Already reads `site_name` from DB — confirmed |

### v16 Test Results

```
[PASS] BEFORE: support_contact_number = +917676446647 (reads from DB correctly)
[PASS] AFTER save: support_contact_number = +91-9999-000-TEST (persisted to DB)
[PASS] AFTER save: site_name = LocalShopOS Test (persisted to DB)
[PASS] All 6 fields update correctly via ON DUPLICATE KEY UPDATE
[PASS] includes/header.php & footer.php read updated values on next page load
[PASS] No duplicate AJAX interceptors — global admin/footer.php handler only
```

---

## 🛠️ Version 17 (v17) — Platform Settings Clean Rebuild from Scratch

### Step 0 Diagnosis & Root Cause
The previous `platform_settings` table operated as a key-value store (`setting_key VARCHAR PRIMARY KEY, setting_value VARCHAR`). This architecture required array key iteration and multiple `INSERT ... ON DUPLICATE KEY UPDATE` queries per form submission, making single-form saves complex and prone to key mismatch bugs.

### Architectural Improvements
1. **Single-Row Table Enforcement**:
   Rebuilt `platform_settings` with a strict `id INT PRIMARY KEY DEFAULT 1` structure and `CHECK (id = 1)` constraint. Exactly one row exists, ensuring every save is a clean `UPDATE platform_settings SET ... WHERE id = 1`.
2. **Dedicated Backend API**:
   Created `/api/admin_settings.php` to handle super admin validation, perform the atomic `UPDATE`, and return structured JSON (`{ success: true, message: "...", data: {...} }`).
3. **AJAX Form & Zero-Reload UI**:
   Rebuilt `/admin/settings.php` with `data-no-ajax` and a dedicated `fetch` handler. Form inputs immediately reflect server-confirmed returned values without requiring page refreshes.
4. **Universal Display Connectivity**:
   Updated all consumer files (`includes/header.php`, `index.php`, `shop.php`, `dashboard/plans.php`, `signup.php`, `admin/index.php`) to query `platform_settings WHERE id = 1`.

### v17 End-to-End Test Results

```
=== V17 END-TO-END TEST SUITE ===
[PASS] Step 6.3: Database has exactly 1 row in platform_settings
[PASS] Step 6.1: API saved successfully: Platform settings saved successfully.
[PASS] Step 6.2 & 6.5: Database row values match saved values accurately
[PASS] Step 6.4: Landing page header/footer picks up live platform_settings values
[PASS] Step 6.4: Tenant dashboard support/plans area picks up live whatsapp contact
[PASS] Step 6.6: Unchanged save succeeds with zero duplicate rows (count remains 1)
```
