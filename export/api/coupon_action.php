<?php
/**
 * POST /api/coupon_action.php
 * Toggle active status or delete a coupon for the authenticated tenant.
 * Always returns JSON.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_tenant_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

$tenantId = get_logged_tenant_id();
$pdo      = getDBConnection();

$raw  = file_get_contents('php://input');
$json = json_decode($raw, true);
$p    = is_array($json) ? $json : $_POST;

$action   = trim($p['action'] ?? '');
$couponId = (int)($p['coupon_id'] ?? 0);

if ($couponId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid coupon ID.']);
    exit;
}

// Verify coupon belongs to this tenant
$check = $pdo->prepare("SELECT id, code, is_active FROM coupons WHERE id = ? AND tenant_id = ?");
$check->execute([$couponId, $tenantId]);
$coupon = $check->fetch();

if (!$coupon) {
    echo json_encode(['success' => false, 'error' => 'Coupon not found.']);
    exit;
}

if ($action === 'toggle') {
    $newActive = $coupon['is_active'] ? 0 : 1;
    $pdo->prepare("UPDATE coupons SET is_active = ? WHERE id = ? AND tenant_id = ?")
        ->execute([$newActive, $couponId, $tenantId]);
    $label = $newActive ? 'enabled' : 'disabled';
    echo json_encode([
        'success'   => true,
        'message'   => "Coupon '{$coupon['code']}' {$label}.",
        'is_active' => $newActive,
        'coupon_id' => $couponId,
    ]);

} elseif ($action === 'delete') {
    $pdo->prepare("DELETE FROM coupons WHERE id = ? AND tenant_id = ?")
        ->execute([$couponId, $tenantId]);
    echo json_encode([
        'success'   => true,
        'message'   => "Coupon '{$coupon['code']}' deleted.",
        'coupon_id' => $couponId,
    ]);

} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action. Use "toggle" or "delete".']);
}
