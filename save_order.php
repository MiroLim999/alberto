<?php
// ============================================================
// save_order.php — Strict 3NF
// orders        → no customer/total columns
// order_contacts → guest contact info only
// order_items   → variant_id + quantity only
// ============================================================
include "db_connect.php";

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);
if (!$data) { echo json_encode(["status"=>"error","message"=>"Invalid JSON"]); exit; }

$customer_name = trim($data['customer_name'] ?? '');
$mobile        = trim($data['mobile']        ?? '');
$email         = trim($data['email']         ?? '');
$branch_id     = intval($data['branch']      ?? 0);
$address       = trim($data['address']       ?? '');
$order_type    = trim($data['order_type']    ?? '');
$payment       = trim($data['payment']       ?? '');
$items         = $data['items']              ?? [];
$is_online     = intval($data['is_online']   ?? 0);
$status        = $is_online ? 'pending' : 'completed';

if ($customer_name==='' || $mobile==='' || $branch_id===0 || empty($items)) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]); exit;
}

session_start();
$session_role = strtolower($_SESSION['role'] ?? '');
$user_id = ($session_role==='customer' && isset($_SESSION['user_id']))
    ? intval($_SESSION['user_id']) : null;

// ── INSERT orders ────────────────────────────────────────────
$stmt = $conn->prepare("
    INSERT INTO orders (user_id,branch_id,address,order_type,payment_method,status)
    VALUES (?,?,?,?,?,?)
");
$stmt->bind_param("iissss", $user_id,$branch_id,$address,$order_type,$payment,$status);
if (!$stmt->execute()) {
    echo json_encode(["status"=>"error","message"=>"Order failed: ".$stmt->error]); exit;
}
$order_id = $conn->insert_id;
$stmt->close();

// ── INSERT order_contacts (guests only) ──────────────────────
if ($user_id === null) {
    $stmt = $conn->prepare("
        INSERT INTO order_contacts (order_id,customer_name,mobile_number,email)
        VALUES (?,?,?,?)
    ");
    $emailVal = $email !== '' ? $email : null;
    $stmt->bind_param("isss", $order_id,$customer_name,$mobile,$emailVal);
    if (!$stmt->execute()) {
        echo json_encode(["status"=>"error","message"=>"Contact failed: ".$stmt->error]); exit;
    }
    $stmt->close();
}

// ── INSERT order_items ────────────────────────────────────────
$stmtItem    = $conn->prepare("INSERT INTO order_items (order_id,variant_id,quantity) VALUES (?,?,?)");
$stmtVariant = $conn->prepare("
    SELECT pv.variant_id FROM pizza_variants pv
    JOIN pizzas p ON pv.pizza_id=p.pizza_id
    WHERE p.pizza_name=? AND pv.size=? AND pv.cheese=?
    LIMIT 1
");

foreach ($items as $item) {
    $pizza_name = trim($item['pizza']    ?? '');
    $size_int   = intval(rtrim(trim($item['size'] ?? ''), '"'));
    $cheese     = trim($item['cheese']   ?? '');
    $quantity   = intval($item['quantity'] ?? 1);

    $stmtVariant->bind_param("sis", $pizza_name, $size_int, $cheese);
    $stmtVariant->execute();
    $vrow = $stmtVariant->get_result()->fetch_assoc();
    if (!$vrow) {
        echo json_encode(["status"=>"error","message"=>"Variant not found: $pizza_name $size_int\" $cheese"]); exit;
    }
    $variant_id = (int)$vrow['variant_id'];
    $stmtItem->bind_param("iii", $order_id, $variant_id, $quantity);
    if (!$stmtItem->execute()) {
        echo json_encode(["status"=>"error","message"=>"Item failed: ".$stmtItem->error]); exit;
    }
}
$stmtVariant->close();
$stmtItem->close();

// ── Deduct stock (cashier completed orders only) ──────────────
if (!$is_online) {
    foreach ($items as $item) {
        $pname = $conn->real_escape_string(trim($item['pizza'] ?? ''));
        $qty   = intval($item['quantity'] ?? 1);
        $conn->query("UPDATE pizzas SET stock=GREATEST(stock-$qty,0) WHERE pizza_name='$pname'");
    }
}

$conn->close();
echo json_encode(["status"=>"success","order_id"=>$order_id,"order_status"=>$status]);
?>
