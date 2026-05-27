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

// Only the driver who accepted it can mark it delivered
$stmt = $conn->prepare("
  UPDATE orders
  SET status = 'delivered',
      updated_at = NOW()
  WHERE order_id = ?
    AND driver_id = ?
    AND status = 'out_for_delivery'
");

$stmt->bind_param("ii", $order_id, $driver_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
  echo "success";
} else {
  echo "no_rows_updated";
}

$stmt->close();
$conn->close();