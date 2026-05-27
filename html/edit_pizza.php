<?php
session_start();
include "db_connect.php";

if (!isset($_GET['id'])) {
    echo "No pizza selected.";
    exit;
}

$pizza_id = $_GET['id'];

/* ✅ FETCH PIZZA */
$pizzaQuery = $conn->query("SELECT * FROM pizzas WHERE pizza_id = '$pizza_id'");
$pizza = $pizzaQuery->fetch_assoc();

if (!$pizza) {
    echo "Pizza not found.";
    exit;
}

/* ✅ FETCH VARIANTS */
$variants = [];
$variantQuery = $conn->query("SELECT * FROM pizza_variants WHERE pizza_id = '$pizza_id'");

while ($v = $variantQuery->fetch_assoc()) {
    $key = $v['size'] . "_" . strtolower($v['cheese']);
    $variants[$key] = $v;
}

/* ✅ FETCH CATEGORIES */
$categoriesQuery = $conn->query("SELECT DISTINCT category_name FROM categories ORDER BY category_name");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Pizza</title>
  <link rel="stylesheet" href="css/style.css">
</head>

<body>

<!-- ✅ NAVBAR -->
<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">

  <div class="nav-links">

    <?php if (isset($_SESSION['user_id'])): ?>

      <a href="profile_customer.php">
        <?= $_SESSION['username']; ?>
      </a>

      <a href="logout.php">LOG OUT</a>

    <?php else: ?>

      <a href="signup.php">SIGN UP</a>
      <a href="login.php">LOG IN</a>

    <?php endif; ?>

  </div>
</header>

<!-- ✅ MAIN LAYOUT -->
<div class="admin-container">

  <!-- ✅ SIDEBAR -->
  <div class="sidebar">
    <h3>ADMIN</h3>

    <button onclick="showSection('home')">HOME</button>
    <button onclick="showSection('products')">PRODUCTS</button>
    <button onclick="showSection('users')">USERS</button>

  </div>

  <!-- ✅ MAIN CONTENT -->
  <div class="main-panel">

    <!-- ✅ HOME SECTION -->
    <div id="home" class="section active">
      <h2>Dashboard</h2>

      <div class="card-container">
        <div class="card">
          <h3>Daily Sales</h3>
          <p>₱0.00</p>
        </div>

        <div class="card">
          <h3>Monthly Sales</h3>
          <p>₱0.00</p>
        </div>

        <div class="card">
          <h3>Yearly Sales</h3>
          <p>₱0.00</p>
        </div>
      </div>

      <div class="card">
        <h3>Sales Chart (Coming Soon)</h3>
        <p>Toggle between Daily / Monthly / Yearly</p>
      </div>

    </div>

    <!-- ✅ PRODUCTS -->
    <div id="products" class="section">
      <h2>Products</h2>

      <button>Add New Pizza</button>

      <table border="1" width="100%">
        <tr>
          
<th>Pizza Name</th>
  <th>Category</th>
  <th>Ingredients</th>
  <th>Stock</th>
<th>STOCK IN</th>
  <th>EDIT</th>
  <th>DEL</th>


        </tr>

        <?php
        
$result = $conn->query("
  SELECT p.pizza_id, p.pizza_name, c.category_name AS category,
         p.ingredients, p.stock, p.image_path
  FROM pizzas p
  JOIN categories c ON p.category_id = c.category_id
");


        while ($row = $result->fetch_assoc()) {
          

$stock = $row['stock'];

$color = ($stock < 10) ? 'red' : 'green';

echo "<tr>
  <td>{$row['pizza_name']}</td>
  <td>{$row['category']}</td>
  <td>{$row['ingredients']}</td>
  <td style='color:$color'>$stock</td>

  <!-- ✅ STOCK IN -->
  <td style='text-align:center; color:green; cursor:pointer;'>
    
<i 
  class='fa-solid fa-plus'
  style='cursor:pointer; color:green;'
  onclick=\"openStockModal(
  '" . htmlspecialchars($row['pizza_name']) . "',
  '" . htmlspecialchars($row['image_path']) . "',
  " . $row['stock'] . "
)\"

></i>

  </td>

  <!-- ✅ EDIT -->
  <td style='text-align:center; cursor:pointer;'>
    
<a href='edit_pizza.php?id=" . $row['pizza_id'] . "'>
  <i class='fa-solid fa-pen'></i>
</a>

  </td>

  <!-- ✅ DELETE -->
  <td style='text-align:center; color:red; cursor:pointer;'>
    <i class='fa-solid fa-trash'></i>
  </td>
</tr>";


        }
        ?>
      </table>

    </div>

    <!-- ✅ USERS -->
    <div id="users" class="section">
      <h2>Users</h2>

      <button>Add User</button>

      <table border="1" width="100%">
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
        </tr>

        <?php
        $users = $conn->query("SELECT * FROM users");

        while ($u = $users->fetch_assoc()) {
          echo "<tr>
            <td>{$u['fullname']}</td>
            <td>{$u['email']}</td>
            <td>{$u['role']}</td>
          </tr>";
        }
        ?>

      </table>

    </div>

  </div>

</div>

</body>
</html>