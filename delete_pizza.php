<?php
include "db_connect.php";

$id = (int)$_POST['pizza_id'];

// Junction rows + variants are removed via ON DELETE CASCADE,
// but we'll be explicit for clarity.
$conn->query("DELETE FROM pizza_ingredients WHERE pizza_id = $id");
$conn->query("DELETE FROM pizza_variants    WHERE pizza_id = $id");
$conn->query("DELETE FROM pizzas            WHERE pizza_id = $id");
?>
