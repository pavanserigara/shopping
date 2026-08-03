<?php
/**
 * POST /api/validate_coupon.php
 * Apply a coupon at storefront checkout — strictly scoped to tenant.
 * Always returns JSON.
 */
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    // fallback for form-POST
    $input = $_POST;
}

$tenantId    = (int)($input['tenant_id'] ?? 0);
$couponCode  = strtoupper(trim($input['code'] ?? ''));
$orderAmount = max(0, (float)($input['subtotal'] ?? 0));

if ($tenantId <= 0 || empty($couponCode)) {
    echo json_encode(['success' => false, 'error' => 'Missing coupon code or shop identifier.']);
    exit;
}

$pdo = getDBConnection();

// Scope strictly to tenant — no cross-tenant leakage
$stmt = $pdo->prepare("
    SELECT * FROM coupons
    WHERE tenant_id = ? AND code = ?
");
$stmt->execute([$tenantId, $couponCode]);
$coupon = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$coupon) {
    echo json_encode(['success' => false, 'error' => "Invalid coupon code '{$couponCode}'. Please check and try again."]);
    exit;
}

if (!(int)$coupon['is_active']) {
    echo json_encode(['success' => false, 'error' => "Coupon '{$couponCode}' is currently inactive."]);
    exit;
}

if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < strtotime('today')) {
    echo json_encode(['success' => false, 'error' => "Coupon '{$couponCode}' expired on " . date('d M Y', strtotime($coupon['expires_at'])) . "."]);
    exit;
}

if ($orderAmount < (float)$coupon['min_order_amount']) {
    echo json_encode([
        'success' => false,
        'error'   => "Minimum order of ₹" . number_format($coupon['min_order_amount'], 2) . " required to use '{$couponCode}'. Your cart is ₹" . number_format($orderAmount, 2) . "."
    ]);
    exit;
}

// Calculate discount
$discount = 0.00;
if ($coupon['discount_type'] === 'percentage') {
    $discount = round(($orderAmount * (float)$coupon['discount_value']) / 100, 2);
} else {
    // flat
    $discount = (float)$coupon['discount_value'];
}
$discount  = min($discount, $orderAmount); // never go negative
$newTotal  = max(0, $orderAmount - $discount);

echo json_encode([
    'success'         => true,
    'code'            => $coupon['code'],
    'discount_type'   => $coupon['discount_type'],
    'discount_value'  => (float)$coupon['discount_value'],
    'discount_amount' => $discount,
    'new_total'       => $newTotal,
]);
