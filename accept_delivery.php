<?php
session_start();
include "db_connect.php";

// ── Auth: driver only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'driver') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "invalid_method";
    exit;
}

$order_id  = intval($_POST['order_id'] ?? 0);
$driver_id = intval($_SESSION['user_id']);

if (!$order_id || !$driver_id) {
    echo "missing_data";
    exit;
}

// Only accept if order is still available (delivery + status pending/completed + no driver yet)
$check = $conn->prepare("
    SELECT order_id FROM orders
    WHERE order_id = ?
      AND status IN ('pending', 'completed')
      AND order_type = 'DELIVERY'
      AND driver_id IS NULL
    LIMIT 1
");
$check->bind_param("i", $order_id);
$check->execute();
$found = $check->get_result()->num_rows > 0;
$check->close();

if (!$found) {
    echo "already_taken";
    exit;
}

// Atomic update — claim this order for this driver
$stmt = $conn->prepare("
    UPDATE orders
    SET status = 'out_for_delivery',
        driver_id = ?,
        updated_at = NOW()
    WHERE order_id = ?
      AND driver_id IS NULL
      AND status IN ('pending', 'completed')
");
$stmt->bind_param("ii", $driver_id, $order_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo "success";
} else {
    echo "already_taken";
}

$stmt->close();
$conn->close();
