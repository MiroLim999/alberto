<?php
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$id = intval($_POST['user_id'] ?? 0);
if (!$id) { echo "error: invalid id"; exit; }

// Don't allow self-deletion
if ($id === intval($_SESSION['user_id'])) {
    echo "error: cannot delete yourself";
    exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
$stmt->bind_param("i", $id);
echo $stmt->execute() ? "success" : "error: " . $stmt->error;
$stmt->close();
?>
