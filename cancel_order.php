<?php
include "db_connect.php";

$order_id = intval($_POST['order_id'] ?? 0);
if (!$order_id) {
    echo "error";
    exit;
}

// ✅ Update status instead of deleting (prepared statement)
$stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error";
}
$stmt->close();
?>
