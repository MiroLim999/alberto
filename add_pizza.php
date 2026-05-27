<?php
// =============================================
// add_pizza.php — Save New Pizza to Database
// 3NF: pizzas.category_id (FK) instead of category string
// =============================================

include "db_connect.php";

// ─── 1. COLLECT INPUTS ───────────────────────
$name        = trim($_POST['name']          ?? '');
$category    = trim($_POST['category']      ?? '');
$is_new_cat  = ($_POST['is_new_category']   ?? '0') === '1';
$ingredients = trim($_POST['ingredients']   ?? '');

$p9q  = floatval($_POST['p9q']  ?? 0);
$p11q = floatval($_POST['p11q'] ?? 0);
$p9m  = floatval($_POST['p9m']  ?? 0);
$p11m = floatval($_POST['p11m'] ?? 0);

// ─── 2. BASIC VALIDATION ─────────────────────
if ($name === '' || $category === '' || $ingredients === '') {
    echo "error: missing required fields";
    exit;
}

// ─── 3. RESOLVE category_id ──────────────────
// Insert new category if needed, then get its ID
if ($is_new_cat) {
    $stmtCat = $conn->prepare(
        "INSERT IGNORE INTO categories (category_name) VALUES (?)"
    );
    $stmtCat->bind_param("s", $category);
    $stmtCat->execute();
    $stmtCat->close();
}

$stmtGetCat = $conn->prepare(
    "SELECT category_id FROM categories WHERE category_name = ? LIMIT 1"
);
$stmtGetCat->bind_param("s", $category);
$stmtGetCat->execute();
$catResult = $stmtGetCat->get_result()->fetch_assoc();
$stmtGetCat->close();

if (!$catResult) {
    echo "error: category not found";
    exit;
}
$category_id = (int)$catResult['category_id'];

// ─── 4. HANDLE IMAGE UPLOAD ──────────────────
$image_path = "menu/Other Flavors/Default.png"; // fallback

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($_FILES['image']['name']);
    $targetDir    = "menu/" . $category . "/";

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . $originalName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image_path = $targetPath;
    }
}

// ─── 5. INSERT INTO pizzas (with category_id) ─
$stmtPizza = $conn->prepare(
    "INSERT INTO pizzas (pizza_name, category_id, ingredients, image_path)
     VALUES (?, ?, ?, ?)"
);
$stmtPizza->bind_param("siss", $name, $category_id, $ingredients, $image_path);

if (!$stmtPizza->execute()) {
    echo "error: failed to insert pizza - " . $stmtPizza->error;
    exit;
}

$pizza_id = $conn->insert_id;
$stmtPizza->close();

// ─── 6. INSERT ALL 4 PRICE VARIANTS ──────────
$stmtVariant = $conn->prepare(
    "INSERT INTO pizza_variants (pizza_id, size, cheese, price)
     VALUES (?, ?, ?, ?)"
);

$variants = [
    ['size' => 9,  'cheese' => 'Quickmelt',  'price' => $p9q],
    ['size' => 11, 'cheese' => 'Quickmelt',  'price' => $p11q],
    ['size' => 9,  'cheese' => 'Mozzarella', 'price' => $p9m],
    ['size' => 11, 'cheese' => 'Mozzarella', 'price' => $p11m],
];

foreach ($variants as $v) {
    $stmtVariant->bind_param("issd", $pizza_id, $v['size'], $v['cheese'], $v['price']);
    if (!$stmtVariant->execute()) {
        echo "error: failed to insert variant - " . $stmtVariant->error;
        exit;
    }
}

$stmtVariant->close();
$conn->close();

echo "success";
?>
