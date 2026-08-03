<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$tenantId = (int)($_GET['tenant_id'] ?? 0);
$phone    = trim($_GET['phone'] ?? '');

if ($tenantId <= 0 || empty($phone)) {
    echo json_encode(['success' => false, 'orders' => []]);
    exit;
}

$pdo = getDBConnection();

$stmt = $pdo->prepare("
    SELECT id, total, discount_amount, coupon_code, status, created_at, accepted_at, preparing_at, completed_at
    FROM orders
    WHERE tenant_id = ? AND customer_contact = ?
    ORDER BY id DESC
    LIMIT 15
");
$stmt->execute([$tenantId, $phone]);
$orders = $stmt->fetchAll();

// Fetch items for each order
foreach ($orders as &$o) {
    $itemsStmt = $pdo->prepare("
        SELECT oi.product_id, oi.quantity, oi.price_at_order, p.name as product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$o['id']]);
    $o['items'] = $itemsStmt->fetchAll();
    $o['formatted_date'] = date('d M Y, h:i A', strtotime($o['created_at']));
}

echo json_encode([
    'success' => true,
    'orders'  => $orders
]);
