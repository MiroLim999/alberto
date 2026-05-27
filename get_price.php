<?php
include "db_connect.php";

$pizza = $_POST['pizza'];
$size = $_POST['size'];
$cheese = $_POST['cheese'];

$sql = "
  SELECT pv.price
  FROM pizza_variants pv
  JOIN pizzas p ON pv.pizza_id = p.pizza_id
  WHERE p.pizza_name = ?
  AND pv.size = ?
  AND pv.cheese = ?
  LIMIT 1
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sis", $pizza, $size, $cheese);
$stmt->execute();

$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
  echo $row['price'];
} else {
  echo 0;
}

$conn->close();
?>