<?php
// =============================================
// save_order.php — Save order to DB
// 3NF: order_items now stores pizza_id + variant_id FKs
// Online orders  → status = 'pending'
// Cashier orders → status = 'completed'
// =============================================

include "db_connect.php";

// ── 1. PARSE JSON BODY ───────────────────────
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

// ── 2. EXTRACT FIELDS ────────────────────────
$customer_name = trim($data['customer_name'] ?? '');
$mobile        = trim($data['mobile']        ?? '');
$email         = trim($data['email']         ?? '');
$branch_id     = intval($data['branch']      ?? 0);
$address       = trim($data['address']       ?? '');
$order_type    = trim($data['order_type']    ?? '');
$payment       = trim($data['payment']       ?? '');
$total         = floatval($data['total']     ?? 0);
$items         = $data['items']              ?? [];
$is_online     = intval($data['is_online']   ?? 0);

// ── 3. DETERMINE STATUS ──────────────────────
$status = $is_online ? 'pending' : 'completed';

// ── 4. BASIC VALIDATION ──────────────────────
if ($customer_name === '' || $mobile === '' || $branch_id === 0 || empty($items)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

// ── 5. GET user_id FROM SESSION ──────────────
session_start();
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// ── 6. INSERT INTO orders ────────────────────
$stmtOrder = $conn->prepare("
    INSERT INTO orders
        (user_id, customer_name, mobile_number, email, branch_id,
         address, order_type, payment_method, total_amount, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
$stmtOrder->bind_param(
    "isssisssds",
    $user_id, $customer_name, $mobile, $email, $branch_id,
    $address, $order_type, $payment, $total, $status
);

if (!$stmtOrder->execute()) {
    echo json_encode(["status" => "error", "message" => "Failed to save order: " . $stmtOrder->error]);
    exit;
}

$order_id = $conn->insert_id;
$stmtOrder->close();

// ── 7. INSERT INTO order_items (with pizza_id + variant_id) ──
$stmtItem = $conn->prepare("
    INSERT INTO order_items
        (order_id, pizza_id, variant_id, pizza_name, size, cheese, price, quantity, total)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Prepared statement to look up pizza_id by name
$stmtPizzaLookup = $conn->prepare(
    "SELECT pizza_id FROM pizzas WHERE pizza_name = ? LIMIT 1"
);

// Prepared statement to look up variant_id
$stmtVariantLookup = $conn->prepare(
    "SELECT variant_id FROM pizza_variants
     WHERE pizza_id = ? AND size = ? AND cheese = ? AND price = ?
     LIMIT 1"
);

foreach ($items as $item) {
    $pizza_name = trim($item['pizza']    ?? '');
    $size       = trim($item['size']     ?? '');
    $cheese     = trim($item['cheese']   ?? '');
    $price      = floatval($item['price']    ?? 0);
    $quantity   = intval($item['quantity']   ?? 1);
    $item_total = floatval($item['total']    ?? 0);

    // Strip trailing " from size if present
    $size_clean = rtrim($size, '"');
    $size_int   = intval($size_clean);

    // Look up pizza_id
    $pizza_id = null;
    $stmtPizzaLookup->bind_param("s", $pizza_name);
    $stmtPizzaLookup->execute();
    $pizzaRow = $stmtPizzaLookup->get_result()->fetch_assoc();
    if ($pizzaRow) {
        $pizza_id = (int)$pizzaRow['pizza_id'];
    }

    // Look up variant_id
    $variant_id = null;
    if ($pizza_id) {
        $stmtVariantLookup->bind_param("issd", $pizza_id, $size_int, $cheese, $price);
        $stmtVariantLookup->execute();
        $variantRow = $stmtVariantLookup->get_result()->fetch_assoc();
        if ($variantRow) {
            $variant_id = (int)$variantRow['variant_id'];
        }
    }

    $stmtItem->bind_param(
        "iiissssdid",
        $order_id, $pizza_id, $variant_id,
        $pizza_name, $size_clean, $cheese, $price, $quantity, $item_total
    );

    if (!$stmtItem->execute()) {
        echo json_encode(["status" => "error", "message" => "Failed to save item: " . $stmtItem->error]);
        exit;
    }
}

$stmtPizzaLookup->close();
$stmtVariantLookup->close();
$stmtItem->close();

// ── 8. DEDUCT STOCK (completed cashier orders only) ──
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

// ── 9. RESPOND ───────────────────────────────
echo json_encode([
    "status"       => "success",
    "order_id"     => $order_id,
    "order_status" => $status
]);
?>
