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

// Only allow permanent delete if the pizza is already archived (deleted_at IS NOT NULL)
$check = $conn->prepare("SELECT pizza_id FROM pizzas WHERE pizza_id = ? AND deleted_at IS NOT NULL LIMIT 1");
$check->bind_param("i", $id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    echo "error: pizza is not archived — archive it first before permanent deletion";
    $check->close();
    exit;
}
$check->close();

// Hard delete — will fail if order_items still reference variants (RESTRICT FK)
// In that case the pizza should stay archived, not be permanently deleted.
$conn->begin_transaction();
try {
    $s1 = $conn->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = ?");
    $s1->bind_param("i", $id); $s1->execute(); $s1->close();

    $s2 = $conn->prepare("DELETE FROM pizza_variants WHERE pizza_id = ?");
    $s2->bind_param("i", $id); $s2->execute(); $s2->close();

    $s3 = $conn->prepare("DELETE FROM pizzas WHERE pizza_id = ?");
    $s3->bind_param("i", $id); $s3->execute(); $s3->close();

    $conn->commit();
    echo "success";
} catch (Exception $e) {
    $conn->rollback();
    echo "error: Cannot permanently delete — this pizza appears in existing orders. It will remain archived.";
}
?>
