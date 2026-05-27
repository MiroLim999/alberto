<?php
// =============================================
// save_order.php — Save order to DB (strict 3NF)
// orders            → no customer_name/mobile/email/total
// order_contacts    → guest contact info (only if user_id is NULL)
// order_items       → only variant_id + quantity (price is computed)
// =============================================

include "db_connect.php";

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$customer_name = trim($data['customer_name'] ?? '');
$mobile        = trim($data['mobile']        ?? '');
$email         = trim($data['email']         ?? '');
$branch_id     = intval($data['branch']      ?? 0);
$address       = trim($data['address']       ?? '');
$order_type    = trim($data['order_type']    ?? '');
$payment       = trim($data['payment']       ?? '');
$items         = $data['items']              ?? [];
$is_online     = intval($data['is_online']   ?? 0);

$status = $is_online ? 'pending' : 'completed';

if ($customer_name === '' || $mobile === '' || $branch_id === 0 || empty($items)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

session_start();
// Only attribute the order to the session user if they're a customer.
// Cashier/admin/driver sessions = walk-in order → user_id stays NULL
// and the form-supplied contact info goes into order_contacts.
$session_role = strtolower($_SESSION['role'] ?? '');
$user_id = ($session_role === 'customer' && isset($_SESSION['user_id']))
    ? intval($_SESSION['user_id'])
    : null;

// ── INSERT INTO orders ───────────────────────
$stmt = $conn->prepare("
    INSERT INTO orders (user_id, branch_id, address, order_type, payment_method, status)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param("iissss", $user_id, $branch_id, $address, $order_type, $payment, $status);
if (!$stmt->execute()) {
    echo json_encode(["status" => "error", "message" => "Failed to save order: " . $stmt->error]);
    exit;
}
$order_id = $conn->insert_id;
$stmt->close();

// ── INSERT order_contacts (guest orders only) ──
if ($user_id === null) {
    $stmt = $conn->prepare("
        INSERT INTO order_contacts (order_id, customer_name, mobile_number, email)
        VALUES (?, ?, ?, ?)
    ");
    $emailVal = $email !== '' ? $email : null;
    $stmt->bind_param("isss", $order_id, $customer_name, $mobile, $emailVal);
    if (!$stmt->execute()) {
        echo json_encode(["status" => "error", "message" => "Failed to save contact: " . $stmt->error]);
        exit;
    }
    $stmt->close();
}

// ── INSERT order_items (variant_id + quantity only) ──
$stmtItem = $conn->prepare(
    "INSERT INTO order_items (order_id, variant_id, quantity) VALUES (?, ?, ?)"
);
$stmtVariantLookup = $conn->prepare("
    SELECT pv.variant_id
    FROM pizza_variants pv
    JOIN pizzas p ON pv.pizza_id = p.pizza_id
    WHERE p.pizza_name = ? AND pv.size = ? AND pv.cheese = ?
    LIMIT 1
");

foreach ($items as $item) {
    $pizza_name = trim($item['pizza']    ?? '');
    $size       = trim($item['size']     ?? '');
    $cheese     = trim($item['cheese']   ?? '');
    $quantity   = intval($item['quantity'] ?? 1);

    $size_int = intval(rtrim($size, '"'));

    $stmtVariantLookup->bind_param("sis", $pizza_name, $size_int, $cheese);
    $stmtVariantLookup->execute();
    $variantRow = $stmtVariantLookup->get_result()->fetch_assoc();
    if (!$variantRow) {
        echo json_encode(["status" => "error", "message" => "Variant not found: $pizza_name $size_int $cheese"]);
        exit;
    }
    $variant_id = (int)$variantRow['variant_id'];

    $stmtItem->bind_param("iii", $order_id, $variant_id, $quantity);
    if (!$stmtItem->execute()) {
        echo json_encode(["status" => "error", "message" => "Failed to save item: " . $stmtItem->error]);
        exit;
    }
}
$stmtVariantLookup->close();
$stmtItem->close();

// ── DEDUCT STOCK (cashier orders only) ───────
if (!$is_online) {
    foreach ($items as $item) {
        $pizza_name = $conn->real_escape_string(trim($item['pizza'] ?? ''));
        $quantity   = intval($item['quantity'] ?? 1);
        $conn->query("
            UPDATE pizzas
            SET stock = GREATEST(stock - $quantity, 0)
            WHERE pizza_name = '$pizza_name'
        ");
    }
}

$conn->close();
echo json_encode(["status" => "success", "order_id" => $order_id, "order_status" => $status]);
?>
