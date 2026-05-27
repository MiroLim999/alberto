<?php
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$id = intval($_POST['pizza_id'] ?? 0);
if (!$id) { echo "error: invalid id"; exit; }

// Restore — clear the deleted_at timestamp
$stmt = $conn->prepare("UPDATE pizzas SET deleted_at = NULL WHERE pizza_id = ? AND deleted_at IS NOT NULL");
$stmt->bind_param("i", $id);
echo $stmt->execute() && $stmt->affected_rows > 0 ? "success" : "error: " . $stmt->error;
$stmt->close();
?>
