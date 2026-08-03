<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$input = json_decode(file_get_contents('php://input'), true);
$adId     = (int)($input['ad_id'] ?? $_GET['ad_id'] ?? 0);
$tenantId = (int)($input['tenant_id'] ?? $_GET['tenant_id'] ?? 0);

if ($adId <= 0 || $tenantId <= 0) {
    echo json_encode(['success' => false]);
    exit;
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("INSERT INTO ad_views (ad_id, tenant_id, viewed_at) VALUES (?, ?, NOW())");
$stmt->execute([$adId, $tenantId]);

echo json_encode(['success' => true]);
