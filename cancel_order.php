<?php
include "db_connect.php";

$order_id = $_POST['order_id'];

// ✅ Update status instead of deleting
$sql = "UPDATE orders SET status = 'cancelled' WHERE order_id = '$order_id'";

if ($conn->query($sql)) {
  echo "success";
} else {
  echo "error";
}
?>