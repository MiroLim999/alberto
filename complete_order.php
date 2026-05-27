<?php
include "db_connect.php";

$order_id = $_POST['order_id'];

// ✅ Mark as completed
$sql = "UPDATE orders SET status = 'completed' WHERE order_id = '$order_id'";

if ($conn->query($sql)) {
  echo "success";
} else {
  echo "error";
}
?>