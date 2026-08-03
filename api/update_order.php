<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_tenant_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($input['order_id'] ?? 0);
$newStatus = trim($input['status'] ?? '');

$validStatuses = ['new', 'accepted', 'preparing', 'completed', 'cancelled'];

if ($orderId <= 0 || !in_array($newStatus, $validStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid order ID or status.']);
    exit;
}

$tenantId = get_logged_tenant_id();
$pdo      = getDBConnection();

$timestampCol = "";
if ($newStatus === 'accepted') {
    $timestampCol = ", accepted_at = NOW()";
} elseif ($newStatus === 'preparing') {
    $timestampCol = ", preparing_at = NOW()";
} elseif ($newStatus === 'completed') {
    $timestampCol = ", completed_at = NOW()";
}

$sql = "UPDATE orders SET status = ? {$timestampCol} WHERE id = ? AND tenant_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$newStatus, $orderId, $tenantId]);

if ($stmt->rowCount() > 0) {
    echo json_encode(['success' => true, 'new_status' => $newStatus, 'message' => "Order #{$orderId} status updated to " . strtoupper($newStatus)]);
} else {
    echo json_encode(['success' => false, 'error' => 'Order not found or no changes made.']);
}
