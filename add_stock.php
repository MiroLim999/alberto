<?php
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$add = intval($_POST['add'] ?? 0);
if ($add <= 0) {
    echo "error: invalid amount";
    exit;
}

if (!empty($_POST['pizza_id'])) {
    $pizza_id = intval($_POST['pizza_id']);
    $stmt = $conn->prepare("UPDATE pizzas SET stock = stock + ? WHERE pizza_id = ?");
    $stmt->bind_param("ii", $add, $pizza_id);
    $stmt->execute();
    $stmt->close();
} elseif (!empty($_POST['pizza_name'])) {
    $pizza_name = trim($_POST['pizza_name']);
    $stmt = $conn->prepare("UPDATE pizzas SET stock = stock + ? WHERE pizza_name = ?");
    $stmt->bind_param("is", $add, $pizza_name);
    $stmt->execute();
    $stmt->close();
} else {
    echo "error: missing pizza identifier";
    exit;
}

echo "success";
?>
