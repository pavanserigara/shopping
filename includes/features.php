<?php
/**
 * Feature Gating & Plan Management Helper (v6 Upgrade)
 * Local Shop OS
 */

if (!defined('FEATURE_REGISTRY')) {
    define('FEATURE_REGISTRY', [
        'product_management' => [
            'name' => 'Product Catalog Management',
            'desc' => 'Add, edit, and manage product inventory items'
        ],
        'product_image_gallery' => [
            'name' => 'Multi-Photo Product Gallery',
            'desc' => 'Upload multiple photos and galleries per product'
        ],
        'order_management' => [
            'name' => 'Order Inbox & Status Updates',
            'desc' => 'View, filter, and process customer WhatsApp orders'
        ],
        'sales_reports' => [
            'name' => 'Sales Analytics & Reports',
            'desc' => 'View total sales volume, daily order counts, and GMV'
        ],
        'shop_ads' => [
            'name' => 'Storefront Promotional Ads',
            'desc' => 'Create custom top header carousels and mid-page ad banners'
        ],
        'ad_analytics' => [
            'name' => 'Ad Impression Analytics',
            'desc' => 'Track total and daily customer ad impression counters'
        ],
        'shop_logo_upload' => [
            'name' => 'Custom Shop Logo Upload',
            'desc' => 'Upload custom shop logo branding to replace initial badges'
        ],
        'shop_directory_listing' => [
            'name' => 'Public Merchant Directory Listing',
            'desc' => 'Be featured in the public local shop directory (/shops.php)'
        ],
        'qr_code_generator' => [
            'name' => 'Branded QR Code Studio',
            'desc' => 'Generate premium print-ready QR codes with custom themes for counter displays & WhatsApp sharing'
        ],
        'coupons' => [
            'name' => 'Discount Coupons & Promo Codes',
            'desc' => 'Create percentage or flat-rate discount coupon codes for customer orders'
        ]
    ]);
}

/**
 * Check if a tenant is currently in active trial mode
 */
function is_tenant_in_trial($tenant) {
    if (!$tenant) return false;
    $status = $tenant['plan_status'] ?? '';
    if ($status !== 'trial') return false;

    $trialEnds = $tenant['trial_ends_at'] ?? null;
    if (empty($trialEnds)) return true; // unlimited trial if null

    return strtotime($trialEnds) >= time();
}

/**
 * Check if a tenant has access to a specific feature_key
 */
function tenant_has_feature($pdo, $tenant, $featureKey) {
    if (!$tenant) return false;

    // Rule 1: Trial grants full access to ALL features (non-negotiable pitch guarantee)
    if (is_tenant_in_trial($tenant)) {
        return true;
    }

    $tenantId = (int)$tenant['id'];
    $planId   = !empty($tenant['plan_id']) ? (int)$tenant['plan_id'] : null;

    // Rule 2: If no plan assigned, fallback to default plan (e.g. Free Tier)
    if (!$planId) {
        $defStmt = $pdo->query("SELECT id FROM plans WHERE is_default = 1 LIMIT 1");
        $planId  = (int)$defStmt->fetchColumn();
    }

    if (!$planId) {
        return false;
    }

    // Rule 3: Check if feature_key exists in plan_features for the plan
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM plan_features WHERE plan_id = ? AND feature_key = ?");
    $checkStmt->execute([$planId, $featureKey]);
    return ((int)$checkStmt->fetchColumn()) > 0;
}

/**
 * Render standardized Locked Feature Card Component (v6)
 */
function render_locked_feature_notice($featureKey) {
    $featureInfo = FEATURE_REGISTRY[$featureKey] ?? [
        'name' => 'Premium Feature',
        'desc' => 'Advanced storefront tool'
    ];
    $featureName = htmlspecialchars($featureInfo['name']);
    ?>
    <div class="app-card p-6 sm:p-8 rounded-3xl bg-slate-900 text-white border border-slate-800 shadow-2xl my-6 text-center max-w-2xl mx-auto space-y-4">
      <div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-brand-400 border border-amber-500/30 font-black text-3xl flex items-center justify-center mx-auto shadow-inner">
        🔒
      </div>
      <div>
        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-brand-400 border border-amber-500/20 inline-block mb-2">Feature Locked</span>
        <h3 class="text-xl font-black text-white"><?= $featureName ?></h3>
        <p class="text-xs text-slate-400 font-medium mt-1 max-w-md mx-auto">
          This feature isn't included in your current subscription plan. Please upgrade your plan to unlock <strong><?= $featureName ?></strong> and boost your shop sales.
        </p>
      </div>
      <div class="pt-2">
        <a href="/dashboard/plans.php" class="px-6 py-3 bg-brand-500 hover:bg-brand-400 text-slate-950 font-black text-xs rounded-xl shadow-lg inline-flex items-center space-x-2 transition-all">
          <span>Explore Subscription Plans & Upgrade</span>
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </a>
      </div>
    </div>
    <?php
}
