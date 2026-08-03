<?php
/**
 * POST /api/coupon_create.php
 * Create a coupon for the authenticated tenant.
 * Always returns JSON.
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Auth check
if (!is_tenant_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
    exit;
}

$tenantId = get_logged_tenant_id();
$pdo      = getDBConnection();

// Parse input — supports both JSON body and form-data
$raw = file_get_contents('php://input');
$json = json_decode($raw, true);
$p = is_array($json) ? $json : $_POST;

$code          = strtoupper(trim($p['code'] ?? ''));
$discountType  = trim($p['discount_type'] ?? '');
$discountValue = (float)($p['discount_value'] ?? 0);
$minOrder      = max(0, (float)($p['min_order_amount'] ?? 0));
$expiresAt     = !empty($p['expires_at']) ? $p['expires_at'] : null;
$isActive      = isset($p['is_active']) ? (int)(bool)$p['is_active'] : 1;

// Validate
$errors = [];
if (empty($code))                                          $errors[] = 'Coupon code is required.';
if (strlen($code) > 50)                                    $errors[] = 'Coupon code must be 50 characters or less.';
if (!preg_match('/^[A-Z0-9_\-]+$/', $code))               $errors[] = 'Coupon code may only contain letters, numbers, hyphens, and underscores.';
if (!in_array($discountType, ['flat', 'percentage']))      $errors[] = 'Discount type must be "flat" or "percentage".';
if ($discountValue <= 0)                                   $errors[] = 'Discount value must be greater than 0.';
if ($discountType === 'percentage' && $discountValue > 100) $errors[] = 'Percentage discount cannot exceed 100%.';
if ($expiresAt !== null && strtotime($expiresAt) < strtotime('today')) $errors[] = 'Expiry date cannot be in the past.';

if (!empty($errors)) {
    echo json_encode(['success' => false, 'error' => implode(' ', $errors)]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        INSERT INTO coupons (tenant_id, code, discount_type, discount_value, min_order_amount, is_active, expires_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$tenantId, $code, $discountType, $discountValue, $minOrder, $isActive, $expiresAt]);
    $newId = (int)$pdo->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => "Coupon '{$code}' created successfully!",
        'coupon'  => [
            'id'             => $newId,
            'code'           => $code,
            'discount_type'  => $discountType,
            'discount_value' => $discountValue,
            'min_order_amount' => $minOrder,
            'is_active'      => $isActive,
            'expires_at'     => $expiresAt,
        ]
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo json_encode(['success' => false, 'error' => "Coupon code '{$code}' already exists for your shop."]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
}
