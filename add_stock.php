<?php
// =============================================
// add_stock.php
// Accepts pizza_name (for backward compat with admin UI)
// or pizza_id for direct FK-based update
// =============================================

include "db_connect.php";

$add = intval($_POST['add'] ?? 0);

if ($add <= 0) {
    echo "error: invalid amount";
    exit;
}

if (!empty($_POST['pizza_id'])) {
    // Preferred: update by pizza_id (FK-safe)
    $pizza_id = (int)$_POST['pizza_id'];
    $conn->query("
        UPDATE pizzas
        SET stock = stock + $add
        WHERE pizza_id = $pizza_id
    ");
} elseif (!empty($_POST['pizza_name'])) {
    // Fallback: update by name (admin modal still sends name)
    $pizza_name = $conn->real_escape_string($_POST['pizza_name']);
    $conn->query("
        UPDATE pizzas
        SET stock = stock + $add
        WHERE pizza_name = '$pizza_name'
    ");
} else {
    echo "error: missing pizza identifier";
    exit;
}

echo "success";
?>
