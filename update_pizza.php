<?php
include "db_connect.php";

$id = $_POST['pizza_id'];
$name = $_POST['name'];
$category = $_POST['category'];
$ingredients = $_POST['ingredients'];

/* ✅ UPDATE BASIC INFO */
$conn->query("
  UPDATE pizzas 
  SET pizza_name='$name', category='$category', ingredients='$ingredients' 
  WHERE pizza_id='$id'
");

/* ✅ HANDLE IMAGE UPLOAD */
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

  $fileName = $_FILES['image']['name'];
  $tmpName = $_FILES['image']['tmp_name'];

  // ✅ folder based on category
  $folder = "menu/" . $category;

  if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
  }

  $path = $folder . "/" . $fileName;

  move_uploaded_file($tmpName, $path);

  // ✅ update image path
  $conn->query("
    UPDATE pizzas 
    SET image_path='$path' 
    WHERE pizza_id='$id'
  ");
}

/* ✅ UPDATE PRICES */
function updateVariant($conn, $id, $size, $cheese, $price) {
  if ($price === "") return;

  $check = $conn->query("
    SELECT * FROM pizza_variants 
    WHERE pizza_id='$id' AND size='$size' AND cheese='$cheese'
  ");

  if ($check->num_rows > 0) {
    $conn->query("
      UPDATE pizza_variants 
      SET price='$price' 
      WHERE pizza_id='$id' AND size='$size' AND cheese='$cheese'
    ");
  } else {
    $conn->query("
      INSERT INTO pizza_variants (pizza_id, size, cheese, price)
      VALUES ('$id','$size','$cheese','$price')
    ");
  }
}

updateVariant($conn, $id, 9, "Quickmelt", $_POST['p9q']);
updateVariant($conn, $id, 11, "Quickmelt", $_POST['p11q']);
updateVariant($conn, $id, 9, "Mozzarella", $_POST['p9m']);
updateVariant($conn, $id, 11, "Mozzarella", $_POST['p11m']);
?>