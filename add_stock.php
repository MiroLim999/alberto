
<?php
include "db_connect.php";

$pizza_name = $_POST['pizza_name'];
$add = intval($_POST['add']);

$conn->query("
  UPDATE pizzas
  SET stock = stock + $add
  WHERE pizza_name = '$pizza_name'
");
?>
