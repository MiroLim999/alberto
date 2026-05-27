
<?php
include "db_connect.php";

$order_id = $_GET['order_id'];

// ✅ get order info
$orderQuery = "SELECT * FROM orders WHERE order_id = '$order_id'";
$orderResult = $conn->query($orderQuery);
$order = $orderResult->fetch_assoc();

// ✅ get items
$itemsQuery = "SELECT * FROM order_items WHERE order_id = '$order_id'";
$itemsResult = $conn->query($itemsQuery);

$items = [];

while ($row = $itemsResult->fetch_assoc()) {
  $items[] = $row;
}

// ✅ return JSON
echo json_encode([
  "order" => $order,
  "items" => $items
]);
?>