<?php
// =============================================
// update_pizza.php
// Strict 3NF: ingredients via junction table
// =============================================

include "db_connect.php";

$id              = (int)$_POST['pizza_id'];
$name            = $conn->real_escape_string($_POST['name']);
$category        = $conn->real_escape_string($_POST['category']);
$ingredientsStr  = $_POST['ingredients'] ?? '';

// ── Resolve category_id ──────────────────────
$catRow = $conn->query(
    "SELECT category_id FROM categories WHERE category_name = '$category' LIMIT 1"
)->fetch_assoc();

if (!$catRow) {
    $conn->query("INSERT IGNORE INTO categories (category_name) VALUES ('$category')");
    $catRow = $conn->query(
        "SELECT category_id FROM categories WHERE category_name = '$category' LIMIT 1"
    )->fetch_assoc();
}
$category_id = (int)$catRow['category_id'];

// ── UPDATE basic info ────────────────────────
$conn->query("
    UPDATE pizzas
    SET pizza_name  = '$name',
        category_id = $category_id
    WHERE pizza_id  = $id
");

// ── HANDLE image upload ──────────────────────
if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $fileName = $_FILES['image']['name'];
    $tmpName  = $_FILES['image']['tmp_name'];
    $folder   = "menu/" . $category;
    if (!is_dir($folder)) mkdir($folder, 0777, true);
    $path = $folder . "/" . $fileName;
    move_uploaded_file($tmpName, $path);
    $conn->query("UPDATE pizzas SET image_path = '$path' WHERE pizza_id = $id");
}

// ── REPLACE ingredients ──────────────────────
$conn->query("DELETE FROM pizza_ingredients WHERE pizza_id = $id");

$stmtIngFind = $conn->prepare(
    "SELECT ingredient_id FROM ingredients WHERE ingredient_name = ? LIMIT 1"
);
$stmtIngInsert = $conn->prepare(
    "INSERT INTO ingredients (ingredient_name) VALUES (?)"
);
$stmtPiInsert = $conn->prepare(
    "INSERT IGNORE INTO pizza_ingredients (pizza_id, ingredient_id) VALUES (?, ?)"
);

foreach (explode(',', $ingredientsStr) as $part) {
    $clean = trim($part);
    $clean = preg_replace('/^&\s*/', '', $clean);
    $clean = preg_replace('/^and\s+/i', '', $clean);
    $clean = preg_replace('/\s+/', ' ', $clean);
    $clean = trim($clean);
    if ($clean === '') continue;
    $clean = ucfirst($clean);

    $stmtIngFind->bind_param("s", $clean);
    $stmtIngFind->execute();
    $row = $stmtIngFind->get_result()->fetch_assoc();
    if ($row) {
        $ing_id = (int)$row['ingredient_id'];
    } else {
        $stmtIngInsert->bind_param("s", $clean);
        $stmtIngInsert->execute();
        $ing_id = $conn->insert_id;
    }
    $stmtPiInsert->bind_param("ii", $id, $ing_id);
    $stmtPiInsert->execute();
}
$stmtIngFind->close();
$stmtIngInsert->close();
$stmtPiInsert->close();

// ── UPDATE prices ────────────────────────────
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
