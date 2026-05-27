<?php
include "db_connect.php";

$id = $_POST['pizza_id'];

/* ✅ DELETE VARIANTS FIRST */
$conn->query("DELETE FROM pizza_variants WHERE pizza_id='$id'");

/* ✅ DELETE PIZZA */
$conn->query("DELETE FROM pizzas WHERE pizza_id='$id'");
?>