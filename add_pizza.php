<?php
// =============================================
// add_pizza.php — Save New Pizza to Database
// Strict 3NF: ingredients split into ingredients + pizza_ingredients
// =============================================
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

// ─── 1. COLLECT INPUTS ───────────────────────
$name           = trim($_POST['name']            ?? '');
$category       = trim($_POST['category']        ?? '');
$is_new_cat     = ($_POST['is_new_category']     ?? '0') === '1';
$ingredientsStr = trim($_POST['ingredients']     ?? '');

$p9q  = floatval($_POST['p9q']  ?? 0);
$p11q = floatval($_POST['p11q'] ?? 0);
$p9m  = floatval($_POST['p9m']  ?? 0);
$p11m = floatval($_POST['p11m'] ?? 0);

if ($name === '' || $category === '' || $ingredientsStr === '') {
    echo "error: missing required fields";
    exit;
}

// ─── 2. RESOLVE category_id ──────────────────
if ($is_new_cat) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $stmt->close();
}

$stmt = $conn->prepare("SELECT category_id FROM categories WHERE category_name = ? LIMIT 1");
$stmt->bind_param("s", $category);
$stmt->execute();
$catRow = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$catRow) {
    echo "error: category not found";
    exit;
}
$category_id = (int)$catRow['category_id'];

// ─── 3. HANDLE IMAGE UPLOAD ──────────────────
$image_path = "menu/Other Flavors/Default.png";
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $originalName = basename($_FILES['image']['name']);
    $targetDir    = "menu/" . $category . "/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
    $targetPath   = $targetDir . $originalName;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $image_path = $targetPath;
    }
}

// ─── 4. INSERT INTO pizzas ───────────────────
$stmt = $conn->prepare(
    "INSERT INTO pizzas (pizza_name, category_id, image_path) VALUES (?, ?, ?)"
);
$stmt->bind_param("sis", $name, $category_id, $image_path);
if (!$stmt->execute()) {
    echo "error: failed to insert pizza - " . $stmt->error;
    exit;
}
$pizza_id = $conn->insert_id;
$stmt->close();

// ─── 5. INSERT INGREDIENTS + JUNCTION ────────
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

    // Look up or create ingredient
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

    $stmtPiInsert->bind_param("ii", $pizza_id, $ing_id);
    $stmtPiInsert->execute();
}
$stmtIngFind->close();
$stmtIngInsert->close();
$stmtPiInsert->close();

// ─── 6. INSERT 4 PRICE VARIANTS ──────────────
$stmt = $conn->prepare(
    "INSERT INTO pizza_variants (pizza_id, size, cheese, price) VALUES (?, ?, ?, ?)"
);
$variants = [
    [9,  'Quickmelt',  $p9q],
    [11, 'Quickmelt',  $p11q],
    [9,  'Mozzarella', $p9m],
    [11, 'Mozzarella', $p11m],
];
foreach ($variants as $v) {
    $stmt->bind_param("iisd", $pizza_id, $v[0], $v[1], $v[2]);
    if (!$stmt->execute()) {
        echo "error: failed to insert variant - " . $stmt->error;
        exit;
    }
}
$stmt->close();

$conn->close();
echo "success";
?>
