<?php
// =============================================
// add_pizza.php — Save New Pizza to Database
// =============================================

include "db_connect.php";

// ─── 1. COLLECT INPUTS ───────────────────────
$name          = trim($_POST['name']          ?? '');
$category      = trim($_POST['category']      ?? '');
$is_new_cat    = ($_POST['is_new_category']   ?? '0') === '1';
$ingredients   = trim($_POST['ingredients']   ?? '');

// Prices: always store a number; empty fields come in as "0"
$p9q  = floatval($_POST['p9q']  ?? 0);
$p11q = floatval($_POST['p11q'] ?? 0);
$p9m  = floatval($_POST['p9m']  ?? 0);
$p11m = floatval($_POST['p11m'] ?? 0);

// ─── 2. BASIC VALIDATION ─────────────────────
if ($name === '' || $category === '' || $ingredients === '') {
    echo "error: missing required fields";
    exit;
}

// ─── 3. INSERT NEW CATEGORY IF NEEDED ────────
// Only runs when the admin typed a brand-new category
if ($is_new_cat) {
    $stmtCat = $conn->prepare(
        "INSERT IGNORE INTO categories (category_name) VALUES (?)"
    );
    $stmtCat->bind_param("s", $category);
    $stmtCat->execute();
    $stmtCat->close();
}

// ─── 4. HANDLE IMAGE UPLOAD ──────────────────
$image_path = "menu/Other Flavors/Default.png"; // fallback

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

    $originalName = basename($_FILES['image']['name']); // e.g. Pizza Supreme.png
    $targetDir    = "menu/" . $category . "/";          // e.g. menu/Bestsellers/

    // Create folder if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetPath = $targetDir . $originalName;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image_path = $targetPath; // e.g. menu/Bestsellers/Pizza Supreme.png
    }
}

// ─── 5. INSERT INTO pizzas ───────────────────
$stmtPizza = $conn->prepare(
    "INSERT INTO pizzas (pizza_name, category, ingredients, image_path)
     VALUES (?, ?, ?, ?)"
);
$stmtPizza->bind_param("ssss", $name, $category, $ingredients, $image_path);

if (!$stmtPizza->execute()) {
    echo "error: failed to insert pizza - " . $stmtPizza->error;
    exit;
}

$pizza_id = $conn->insert_id; // the new pizza's auto-incremented ID
$stmtPizza->close();

// ─── 6. INSERT ALL 4 PRICE VARIANTS ──────────
// All 4 variants are always inserted (price is 0 if the field was left empty)
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

// ─── 7. SUCCESS ──────────────────────────────
echo "success";
?>