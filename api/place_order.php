<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$tenantId        = (int)($input['tenant_id'] ?? 0);
$customerContact = trim($input['customer_contact'] ?? '');
$couponCode      = strtoupper(trim($input['coupon_code'] ?? ''));
$items           = is_array($input['items'] ?? null) ? $input['items'] : [];

if ($tenantId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid tenant specified.']);
    exit;
}

$pdo = getDBConnection();
$tenantStmt = $pdo->prepare("SELECT * FROM tenants WHERE id = ?");
$tenantStmt->execute([$tenantId]);
$tenant = $tenantStmt->fetch();

if (!$tenant) {
    echo json_encode(['success' => false, 'error' => 'Shop not found.']);
    exit;
}

$deliveryType    = strtolower(trim($input['delivery_type'] ?? 'delivery'));
if ($deliveryType !== 'pickup') $deliveryType = 'delivery';

$deliveryAddress = trim($input['delivery_address'] ?? '');
$deliveryContact = trim($input['delivery_contact'] ?? $customerContact);
$paymentMode     = strtolower(trim($input['payment_mode'] ?? 'cod'));
if (!in_array($paymentMode, ['cod', 'upi', 'pickup_pay'])) $paymentMode = 'cod';

// Server-side enforcement of store delivery settings
$deliveryEnabled  = (int)($tenant['delivery_enabled'] ?? 1);
$minDeliveryOrder = (float)($tenant['min_delivery_order'] ?? 0);
$tenantDeliveryFee= (float)($tenant['delivery_fee'] ?? 0);

if ($deliveryType === 'delivery') {
    if (!$deliveryEnabled) {
        echo json_encode(['success' => false, 'error' => 'Home delivery is currently disabled for this shop. Please choose Pickup.']);
        exit;
    }
    if (empty($deliveryAddress)) {
        echo json_encode(['success' => false, 'error' => 'Please provide a valid delivery address.']);
        exit;
    }
}

try {
    $pdo->beginTransaction();

    $subtotal = 0.0;
    $orderItemsData = [];

    foreach ($items as $item) {
        $productId = (int)($item['product_id'] ?? 0);
        $qty       = max(1, (int)($item['quantity'] ?? 1));

        $prodStmt = $pdo->prepare("SELECT id, name, price, stock_count, is_active FROM products WHERE id = ? AND tenant_id = ?");
        $prodStmt->execute([$productId, $tenantId]);
        $prod = $prodStmt->fetch();

        if ($prod && $prod['is_active']) {
            $itemPrice = (float)$prod['price'];
            $itemSubtotal  = $itemPrice * $qty;
            $subtotal += $itemSubtotal;

            $orderItemsData[] = [
                'product_id'     => $prod['id'],
                'name'           => $prod['name'],
                'quantity'       => $qty,
                'price_at_order' => $itemPrice
            ];
        }
    }

    if (empty($orderItemsData)) {
        throw new Exception("None of the items in your cart are currently available.");
    }

    if ($deliveryType === 'delivery' && $minDeliveryOrder > 0 && $subtotal < $minDeliveryOrder) {
        throw new Exception("Minimum order amount for delivery is ₹" . number_format($minDeliveryOrder, 2) . ". Please add more items or choose Pickup.");
    }

    $discountAmount = 0.00;
    $appliedCoupon = null;

    if (!empty($couponCode)) {
        $cStmt = $pdo->prepare("SELECT * FROM coupons WHERE tenant_id = ? AND code = ? AND is_active = 1");
        $cStmt->execute([$tenantId, $couponCode]);
        $coupon = $cStmt->fetch();

        if ($coupon && $subtotal >= (float)$coupon['min_order_amount']) {
            $appliedCoupon = $coupon['code'];
            if ($coupon['discount_type'] === 'percent') {
                $discountAmount = ($subtotal * (float)$coupon['discount_value']) / 100.0;
            } else {
                $discountAmount = (float)$coupon['discount_value'];
            }
            $discountAmount = min($discountAmount, $subtotal);
        }
    }

    $appliedDeliveryFee = ($deliveryType === 'delivery') ? $tenantDeliveryFee : 0.00;
    $finalTotal = max(0, $subtotal - $discountAmount + $appliedDeliveryFee);

    $orderStmt = $pdo->prepare("
        INSERT INTO orders (tenant_id, customer_contact, total, discount_amount, coupon_code, status, delivery_type, delivery_address, delivery_contact, payment_mode, delivery_fee) 
        VALUES (?, ?, ?, ?, ?, 'new', ?, ?, ?, ?, ?)
    ");
    $orderStmt->execute([
        $tenantId, 
        $customerContact, 
        $finalTotal, 
        $discountAmount, 
        $appliedCoupon,
        $deliveryType,
        $deliveryAddress,
        $deliveryContact,
        $paymentMode,
        $appliedDeliveryFee
    ]);
    $orderId = (int)$pdo->lastInsertId();

    $itemInsertStmt = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price_at_order) 
        VALUES (?, ?, ?, ?)
    ");
    foreach ($orderItemsData as $orderItem) {
        $itemInsertStmt->execute([
            $orderId,
            $orderItem['product_id'],
            $orderItem['quantity'],
            $orderItem['price_at_order']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success'      => true,
        'order_id'     => $orderId,
        'total'        => $finalTotal,
        'discount'     => $discountAmount,
        'delivery_fee' => $appliedDeliveryFee,
        'coupon'       => $appliedCoupon,
        'thank_you_msg'=> !empty($tenant['order_thank_you_msg']) ? $tenant['order_thank_you_msg'] : ''
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
