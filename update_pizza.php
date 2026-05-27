<?php
// =============================================
// update_pizza.php
// 3NF: category string → category_id FK lookup
// =============================================

include "db_connect.php";

$id          = (int)$_POST['pizza_id'];
$name        = $conn->real_escape_string($_POST['name']);
$category    = $conn->real_escape_string($_POST['category']);
$ingredients = $conn->real_escape_string($_POST['ingredients']);

// ── Resolve category_id ──────────────────────
$catRow = $conn->query(
    "SELECT category_id FROM categories WHERE category_name = '$category' LIMIT 1"
)->fetch_assoc();

if (!$catRow) {
    // Auto-create the category if it doesn't exist yet
    $conn->query("INSERT IGNORE INTO categories (category_name) VALUES ('$category')");
    $catRow = $conn->query(
        "SELECT category_id FROM categories WHERE category_name = '$category' LIMIT 1"
    )->fetch_assoc();
}

$category_id = (int)$catRow['category_id'];

// ── UPDATE BASIC INFO ────────────────────────
$conn->query("
    UPDATE pizzas
    SET pizza_name   = '$name',
        category_id  = $category_id,
        ingredients  = '$ingredients'
    WHERE pizza_id   = $id
");

// ── HANDLE IMAGE UPLOAD ──────────────────────
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $fileName = $_FILES['image']['name'];
    $tmpName  = $_FILES['image']['tmp_name'];
    $folder   = "menu/" . $category;

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $path = $folder . "/" . $fileName;
    move_uploaded_file($tmpName, $path);

    $conn->query("UPDATE pizzas SET image_path = '$path' WHERE pizza_id = $id");
}

// ── UPDATE PRICES ────────────────────────────
function updateVariant($conn, $id, $size, $cheese, $price) {
    if ($price === "") return;

    $check = $conn->query("
        SELECT variant_id FROM pizza_variants
        WHERE pizza_id = '$id' AND size = '$size' AND cheese = '$cheese'
        LIMIT 1
    ");

    if ($check->num_rows > 0) {
        $conn->query("
            UPDATE pizza_variants
            SET price = '$price'
            WHERE pizza_id = '$id' AND size = '$size' AND cheese = '$cheese'
        ");
    } else {
        $conn->query("
            INSERT INTO pizza_variants (pizza_id, size, cheese, price)
            VALUES ('$id', '$size', '$cheese', '$price')
        ");
    }
}

updateVariant($conn, $id, 9,  "Quickmelt",  $_POST['p9q']);
updateVariant($conn, $id, 11, "Quickmelt",  $_POST['p11q']);
updateVariant($conn, $id, 9,  "Mozzarella", $_POST['p9m']);
updateVariant($conn, $id, 11, "Mozzarella", $_POST['p11m']);
?>
