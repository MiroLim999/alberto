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

// Junction rows + variants are removed via ON DELETE CASCADE in the schema,
// but we delete explicitly for clarity using prepared statements.
$stmt = $conn->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM pizza_variants WHERE pizza_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$stmt = $conn->prepare("DELETE FROM pizzas WHERE pizza_id = ?");
$stmt->bind_param("i", $id);
echo $stmt->execute() ? "success" : "error: " . $stmt->error;
$stmt->close();
?>
