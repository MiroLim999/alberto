<?php
session_start();
include "db_connect.php";

// ── Auth: any logged-in staff or order owner ──
$role = strtolower($_SESSION['role'] ?? '');
$logged_user = intval($_SESSION['user_id'] ?? 0);

if (!$logged_user) {
    http_response_code(403);
    echo json_encode(["error" => "forbidden"]);
    exit;
}

$order_id = intval($_GET['order_id'] ?? 0);
if (!$order_id) {
    echo json_encode(["error" => "invalid id"]);
    exit;
}

// ── Order header (prepared) ──
$stmt = $conn->prepare("
    SELECT
        o.order_id, o.user_id, o.branch_id, o.address,
        o.order_type, o.payment_method, o.status,
        o.created_at, o.driver_id, o.updated_at,
        COALESCE(u.username,      oc.customer_name) AS customer_name,
        COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
        COALESCE(u.email,         oc.email)         AS email,
        b.branch_name,
        b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_id = ?
    LIMIT 1
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    echo json_encode(["error" => "not found"]);
    exit;
}

// Customers can only view their own orders; staff can view any
if ($role === 'customer' && intval($order['user_id']) !== $logged_user) {
    http_response_code(403);
    echo json_encode(["error" => "forbidden"]);
    exit;
}

// ── Items (prepared) ──
$stmt = $conn->prepare("
    SELECT
        oi.item_id, oi.order_id, oi.variant_id, oi.quantity,
        pv.pizza_id, p.pizza_name,
        pv.size, pv.cheese, pv.price,
        (oi.quantity * pv.price) AS total
    FROM order_items oi
    JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
    JOIN pizzas         p  ON pv.pizza_id   = p.pizza_id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$r = $stmt->get_result();
$items = [];
while ($row = $r->fetch_assoc()) $items[] = $row;
$stmt->close();

echo json_encode(["order" => $order, "items" => $items]);
?>
