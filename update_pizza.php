<?php
// =============================================
// update_pizza.php — Strict 3NF (admin-only)
// =============================================
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$id              = intval($_POST['pizza_id'] ?? 0);
$name            = trim($_POST['name'] ?? '');
$category        = trim($_POST['category'] ?? '');
$ingredientsStr  = $_POST['ingredients'] ?? '';

if (!$id || $name === '' || $category === '') {
    echo "error: missing required fields";
    exit;
}

// ── Resolve category_id ──
$stmt = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
$stmt->bind_param("s", $category);
$stmt->execute();
$catRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$catRow) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $catRow = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
$category_id = (int)$catRow['category_id'];

// ── UPDATE basic info (prepared) ──
$stmt = $conn->prepare("UPDATE pizzas SET pizza_name = ?, category_id = ? WHERE pizza_id = ?");
$stmt->bind_param("sii", $name, $category_id, $id);
$stmt->execute();
$stmt->close();

// ── HANDLE image upload ──
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $fileName = basename($_FILES['image']['name']);
    $tmpName  = $_FILES['image']['tmp_name'];
    $folder   = "menu/" . $category;
    if (!is_dir($folder)) mkdir($folder, 0755, true);
    $path = $folder . "/" . $fileName;
    if (move_uploaded_file($tmpName, $path)) {
        $stmt = $conn->prepare("UPDATE pizzas SET image_path = ? WHERE pizza_id = ?");
        $stmt->bind_param("si", $path, $id);
        $stmt->execute();
        $stmt->close();
    }
}

// ── REPLACE ingredients ──
$stmt = $conn->prepare("DELETE FROM pizza_ingredients WHERE pizza_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$stmtIngFind   = $conn->prepare("SELECT ingredient_id FROM ingredients WHERE ingredient_name = ? LIMIT 1");
$stmtIngInsert = $conn->prepare("INSERT INTO ingredients (ingredient_name) VALUES (?)");
$stmtPiInsert  = $conn->prepare("INSERT IGNORE INTO pizza_ingredients (pizza_id, ingredient_id) VALUES (?, ?)");

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

// ── UPDATE prices (prepared statements) ──
function updateVariantSafe($conn, $id, $size, $cheese, $price) {
    if ($price === "" || $price === null) return;
    $price = (float)$price;

    $stmt = $conn->prepare("
        SELECT variant_id FROM pizza_variants
        WHERE pizza_id = ? AND size = ? AND cheese = ? LIMIT 1
    ");
    $stmt->bind_param("iis", $id, $size, $cheese);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if ($exists) {
        $stmt = $conn->prepare("
            UPDATE pizza_variants SET price = ?
            WHERE pizza_id = ? AND size = ? AND cheese = ?
        ");
        $stmt->bind_param("diis", $price, $id, $size, $cheese);
    } else {
        $stmt = $conn->prepare("
            INSERT INTO pizza_variants (pizza_id, size, cheese, price)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iisd", $id, $size, $cheese, $price);
    }
    $stmt->execute();
    $stmt->close();
}

updateVariantSafe($conn, $id, 9,  "Quickmelt",  $_POST['p9q']  ?? '');
updateVariantSafe($conn, $id, 11, "Quickmelt",  $_POST['p11q'] ?? '');
updateVariantSafe($conn, $id, 9,  "Mozzarella", $_POST['p9m']  ?? '');
updateVariantSafe($conn, $id, 11, "Mozzarella", $_POST['p11m'] ?? '');

echo "success";
?>
