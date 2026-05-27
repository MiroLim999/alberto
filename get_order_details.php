<?php
include "db_connect.php";

$order_id = (int)$_GET['order_id'];

// ── Order header (JOIN users + order_contacts for customer info) ──
$order = $conn->query("
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
    WHERE o.order_id = $order_id
    LIMIT 1
")->fetch_assoc();

// ── Items (JOIN pizza_variants + pizzas for name/size/cheese/price) ──
$r = $conn->query("
    SELECT
        oi.item_id, oi.order_id, oi.variant_id, oi.quantity,
        pv.pizza_id, p.pizza_name,
        pv.size, pv.cheese, pv.price,
        (oi.quantity * pv.price) AS total
    FROM order_items oi
    JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
    JOIN pizzas         p  ON pv.pizza_id   = p.pizza_id
    WHERE oi.order_id = $order_id
");
$items = [];
while ($row = $r->fetch_assoc()) $items[] = $row;

echo json_encode(["order" => $order, "items" => $items]);
?>
