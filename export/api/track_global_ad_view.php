<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$globalAdId = (int)($input['global_ad_id'] ?? $_GET['global_ad_id'] ?? 0);
$tenantId   = (int)($input['tenant_id'] ?? $_GET['tenant_id'] ?? 0);

if ($globalAdId <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("INSERT INTO global_ad_views (global_ad_id, tenant_id, viewed_at) VALUES (?, ?, NOW())");
$stmt->execute([$globalAdId, $tenantId > 0 ? $tenantId : null]);

echo json_encode(['success' => true]);
