<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!is_tenant_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$tenantId = get_logged_tenant_id();
$pdo      = getDBConnection();

// Read query params for v2 filtering, search, sorting & pagination
$status    = trim($_GET['status'] ?? 'ALL');
$search    = trim($_GET['search'] ?? '');
$startDate = trim($_GET['start_date'] ?? '');
$endDate   = trim($_GET['end_date'] ?? '');
$sortBy    = trim($_GET['sort_by'] ?? 'created_at'); // created_at, total, status
$sortOrder = strtoupper(trim($_GET['sort_order'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';
$page      = max(1, (int)($_GET['page'] ?? 1));
$limit     = 10;
$offset    = ($page - 1) * $limit;

// Base query SCOPED by tenant_id
$whereClauses = ["o.tenant_id = ?"];
$params       = [$tenantId];

if ($status !== 'ALL' && !empty($status)) {
    $whereClauses[] = "o.status = ?";
    $params[]       = $status;
}

if (!empty($search)) {
    if (is_numeric($search)) {
        $whereClauses[] = "(o.id = ? OR o.customer_contact LIKE ?)";
        $params[]       = (int)$search;
        $params[]       = "%{$search}%";
    } else {
        $whereClauses[] = "o.customer_contact LIKE ?";
        $params[]       = "%{$search}%";
    }
}

if (!empty($startDate)) {
    $whereClauses[] = "DATE(o.created_at) >= ?";
    $params[]       = $startDate;
}

if (!empty($endDate)) {
    $whereClauses[] = "DATE(o.created_at) <= ?";
    $params[]       = $endDate;
}

$whereSql = implode(" AND ", $whereClauses);

// Allowed sort columns for SQL safety
$allowedSortCols = ['created_at' => 'o.created_at', 'total' => 'o.total', 'status' => 'o.status', 'id' => 'o.id'];
$sortColumn = $allowedSortCols[$sortBy] ?? 'o.created_at';

// Total matching count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM orders o WHERE {$whereSql}");
$countStmt->execute($params);
$totalRecords = (int)$countStmt->fetchColumn();
$totalPages   = ceil($totalRecords / $limit);

// Fetch Paginated Orders
$ordersQuery = "
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
    FROM orders o 
    WHERE {$whereSql}
    ORDER BY {$sortColumn} {$sortOrder}
    LIMIT {$limit} OFFSET {$offset}
";
$ordersStmt = $pdo->prepare($ordersQuery);
$ordersStmt->execute($params);
$orders = $ordersStmt->fetchAll();

// Attach order items detail breakdown
foreach ($orders as &$ord) {
    $itemStmt = $pdo->prepare("
        SELECT oi.*, p.name as product_name, p.photo_url 
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $itemStmt->execute([$ord['id']]);
    $ord['items'] = $itemStmt->fetchAll();
    $ord['formatted_time'] = date('d M Y, h:i A', strtotime($ord['created_at']));
}

echo json_encode([
    'success'       => true,
    'orders'        => $orders,
    'total_records' => $totalRecords,
    'total_pages'   => $totalPages,
    'current_page'  => $page,
    'limit'         => $limit
]);
