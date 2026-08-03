# 🚀 LocalShopOS — Hostinger Deployment Guide

This guide will walk you through deploying **LocalShopOS** to **Hostinger** (or any cPanel / PHP MySQL shared hosting) step by step.

---

## 📁 Package Structure

Inside the `export/` directory, you will find:
* `database_demo.sql` — **Full database dump with pre-seeded demo data** (10 shops, products, coupons, ads, user accounts).
* `schema.sql` — Clean blank database schema (if you want to start empty without demo data).
* `config/database.php` — Database configuration file.
* `index.php`, `admin/`, `dashboard/`, `api/`, `uploads/`, `.htaccess` — Web application files.

---

## 🛠️ Step 1: Create MySQL Database on Hostinger

1. Log into your **Hostinger hPanel** (https://hpanel.hostinger.com).
2. Go to **Databases** ➔ **MySQL Databases**.
3. Create a new database:
   - **Database Name**: e.g., `u123456789_shop`
   - **MySQL Username**: e.g., `u123456789_admin`
   - **Password**: Enter a strong password and **copy it down**.
4. Click **Create**.

---

## 🗄️ Step 2: Import Database SQL in phpMyAdmin

1. In Hostinger hPanel, click **Enter phpMyAdmin** next to your newly created database.
2. Click on your database name in the left sidebar.
3. Click the **Import** tab at the top.
4. Click **Choose File** and select `export/database_demo.sql`.
5. Click **Go** at the bottom of the page.
6. You will see a success message: *"Import has been successfully finished, 11 queries executed."*

---

## ⚙️ Step 3: Update `config/database.php`

Open `config/database.php` in a text editor or Hostinger File Manager, and update lines 7–11 with your Hostinger database details:

```php
define('DB_HOST', 'localhost'); // Hostinger uses 'localhost' or your MySQL hostname
define('DB_PORT', '3306');
define('DB_NAME', 'u123456789_shop'); // Your Hostinger Database Name
define('DB_USER', 'u123456789_admin'); // Your Hostinger Database User
define('DB_PASS', 'YOUR_STRONG_HOSTINGER_PASSWORD'); // Your Database Password
```

---

## 📤 Step 4: Upload Application Files

### Option A: Via Hostinger File Manager (Recommended)
1. Zip the contents of the `export/` folder (or upload directly).
2. Go to **Hostinger hPanel** ➔ **File Manager**.
3. Open `public_html/`.
4. Upload the zip file and click **Extract**.
5. Ensure `index.php`, `.htaccess`, `config/`, etc., are directly in `public_html/`.

### Option B: Via FTP / FileZilla
1. Connect via FTP using your Hostinger FTP credentials.
2. Upload all files from `export/` directly into `/public_html/`.

---

## 🔑 Login Credentials

Once deployed, access your domain (e.g., `https://yourdomain.com`):

### 1. Super Admin Dashboard (`/admin/login.php`)
- **URL**: `https://yourdomain.com/admin/login.php`
- **Email**: `admin@localshopos.com`
- **Password**: `admin123`

### 2. Tenant Shop Admin Dashboards (`/login.php`)
You can log in to any of the 10 demo shop merchant accounts:

| Store Name | Subdomain / URL Path | Login Email | Password |
|---|---|---|---|
| Laxmi General Store | `/laxmi-kirana` | `ramesh@kirana.com` | `tenant123` |
| Fresh Farm Organics | `/fresh-fruits` | `contact@freshfarm.com` | `tenant123` |
| Gupta Bakery & Sweets | `/gupta-bakery` | `info@guptabakery.com` | `tenant123` |
| Apex Electronics | `/apex-electronics` | `sales@apexelectronics.com` | `tenant123` |
| Vogue Trends Apparel | `/vogue-trends` | `support@voguetrends.in` | `tenant123` |
| Green Leaf Pharmacy | `/greenleaf-meds` | `care@greenleafmeds.com` | `tenant123` |
| Royal Footwear | `/royal-footwear` | `sales@royalfootwear.in` | `tenant123` |
| Spice Garden | `/spice-garden` | `hello@spicegarden.com` | `tenant123` |
| Urban Nest Decor | `/urban-nest` | `sales@urbannest.in` | `tenant123` |
| Pet Joy Care Shop | `/pet-joy-care` | `hello@petjoycare.com` | `tenant123` |

---

## ⚡ Subdomain / URL Route Verification

LocalShopOS supports both:
1. **Subdomains**: `https://laxmi-kirana.yourdomain.com` (requires wildcard `*.yourdomain.com` DNS A record pointing to host server IP).
2. **Directory URLs**: `https://yourdomain.com/laxmi-kirana` or `https://yourdomain.com/shop.php?subdomain=laxmi-kirana` (works out of the box with the included `.htaccess`).

---

🎉 **Congratulations! Your multi-tenant WhatsApp e-commerce platform is now live on Hostinger!**
