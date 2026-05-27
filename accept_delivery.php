<?php
session_start();
include "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo "invalid_method";
  exit;
}

$order_id  = intval($_POST['order_id'] ?? 0);
$driver_id = intval($_SESSION['user_id'] ?? 0);

if (!$order_id || !$driver_id) {
  echo "missing_data";
  exit;
}

// Only accept if the order is still available (status = 'completed' and no driver yet)
$check = $conn->query("
  SELECT order_id FROM orders
  WHERE order_id = '$order_id'
    AND status = 'completed'
    AND order_type = 'DELIVERY'
  LIMIT 1
");

if (!$check || $check->num_rows === 0) {
  echo "already_taken";
  exit;
}

$stmt = $conn->prepare("
  UPDATE orders
  SET status = 'out_for_delivery',
      driver_id = ?,
      updated_at = NOW()
  WHERE order_id = ?
    AND status = 'completed'
");

$stmt->bind_param("ii", $driver_id, $order_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo "success";
} else {
  echo "no_rows_updated";
}

$stmt->close();
$conn->close();