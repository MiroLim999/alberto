<?php
session_start();
include "db_connect.php";

// ── Auth guard: cashier or admin ──
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || ($role !== 'cashier' && $role !== 'admin')) {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$order_id = intval($_POST['order_id'] ?? 0);
if (!$order_id) {
    echo "error";
    exit;
}

// ✅ Update status instead of deleting (prepared statement)
$stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND status = 'pending'");
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    echo $stmt->affected_rows > 0 ? "success" : "not_pending";
} else {
    echo "error";
}
$stmt->close();
?>
