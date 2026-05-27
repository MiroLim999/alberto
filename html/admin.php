<?php
session_start();
include "db_connect.php";

$categories = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");

// ✅ sample user (replace with session later)
$user_name = "Admin";

// ═══════════════════════════════════════════
// DASHBOARD QUERIES
// ═══════════════════════════════════════════

// ── SALES ──────────────────────────────────
$dailySales = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) AS total
  FROM orders
  WHERE status = 'completed'
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$monthlySales = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) AS total
  FROM orders
  WHERE status = 'completed'
    AND MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at)  = YEAR(CURDATE())
")->fetch_assoc()['total'];

$yearlySales = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) AS total
  FROM orders
  WHERE status = 'completed'
    AND YEAR(created_at) = YEAR(CURDATE())
")->fetch_assoc()['total'];

// ── ORDERS ─────────────────────────────────
$totalOrders   = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='completed'")->fetch_assoc()['c'];
$pendingOrders = $conn->query("SELECT COUNT(*) AS c FROM orders WHERE status='pending'")->fetch_assoc()['c'];

// ── INVENTORY ──────────────────────────────
$totalPizzas   = $conn->query("SELECT COUNT(*) AS c FROM pizzas")->fetch_assoc()['c'];
$outOfStock    = $conn->query("SELECT COUNT(*) AS c FROM pizzas WHERE stock = 0")->fetch_assoc()['c'];
$lowStock      = $conn->query("SELECT COUNT(*) AS c FROM pizzas WHERE stock > 0 AND stock < 10")->fetch_assoc()['c'];
$goodStock     = $conn->query("SELECT COUNT(*) AS c FROM pizzas WHERE stock >= 10")->fetch_assoc()['c'];

// ── LOW STOCK PIZZAS (for inventory table) ──
$lowStockPizzas = $conn->query("
  SELECT pizza_name, category, stock
  FROM pizzas
  WHERE stock < 10
  ORDER BY stock ASC
  LIMIT 10
");

// ── TOP SELLING PIZZAS (for bar chart) ──
$topPizzas = $conn->query("
  SELECT oi.pizza_name, SUM(oi.quantity) AS total_sold
  FROM order_items oi
  JOIN orders o ON oi.order_id = o.order_id
  WHERE o.status = 'completed'
  GROUP BY oi.pizza_name
  ORDER BY total_sold DESC
  LIMIT 10
");

$chartLabels = [];
$chartData   = [];
while ($row = $topPizzas->fetch_assoc()) {
  $chartLabels[] = $row['pizza_name'];
  $chartData[]   = (int)$row['total_sold'];
}

// ── MONTHLY SALES TREND (last 6 months) ──
$monthlySalesTrend = $conn->query("
  SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
         MONTH(created_at) AS month_num,
         YEAR(created_at)  AS year_num,
         SUM(total_amount) AS total
  FROM orders
  WHERE status = 'completed'
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
  GROUP BY year_num, month_num, month_label
  ORDER BY year_num ASC, month_num ASC
");

$trendLabels = [];
$trendData   = [];
while ($row = $monthlySalesTrend->fetch_assoc()) {
  $trendLabels[] = $row['month_label'];
  $trendData[]   = (float)$row['total'];
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>

    /* ✅ LAYOUT */
    .admin-container {
      display: flex;
      height: calc(100vh - 70px);
    }

    /* ✅ SIDEBAR */
    .sidebar {
      width: 220px;
      background: #fff;
      padding: 20px;
      border-right: 1px solid #ccc;
    }

    .sidebar h3 {
      margin-bottom: 15px;
      color: orangered;
    }

    .sidebar button {
      display: block;
      width: 100%;
      padding: 10px;
      margin-bottom: 10px;
      border: none;
      background: #eee;
      cursor: pointer;
      text-align: left;
      border-radius: 6px;
    }

    .sidebar button:hover {
      background: orange;
    }

    /* ✅ MAIN PANEL */
    .main-panel {
      flex: 1;
      padding: 20px;
      overflow-y: auto;
    }

    /* ✅ DASH CARDS */
    .card-container {
      display: flex;
      gap: 20px;
      margin-bottom: 20px;
    }

    .card {
      flex: 1;
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* ✅ HIDE SECTIONS */
    .section {
      display: none;
    }

    .active {
      display: block;
    }

  </style>
</head>

<body>

<?php
$currentUser = null;

if (isset($_SESSION['user_id'])) {
  $id = $_SESSION['user_id'];
  $res = $conn->query("SELECT * FROM users WHERE user_id='$id'");
  $currentUser = $res->fetch_assoc();
}
?>

<!-- ✅ NAVBAR -->
<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">

  <div class="nav-links">

    <?php if (isset($_SESSION['user_id'])): ?>

      <a href="#"
   onclick="openEditUser(
     <?= $currentUser['user_id'] ?>,
     '<?= $currentUser['username'] ?>',
     '<?= $currentUser['password'] ?>',
     '<?= $currentUser['role'] ?>',
     '<?= $currentUser['birth_date'] ?>',
     '<?= $currentUser['gender'] ?>',
     '<?= $currentUser['mobile_number'] ?>',
     '<?= $currentUser['email'] ?>'
   )">
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
      <h2 style="margin-bottom:18px;">📊 Dashboard</h2>

      <!-- ── ROW 1: SALES STAT CARDS ─────────────────── -->
      <div class="card-container" style="margin-bottom:18px;">

        <div class="card" style="border-left:5px solid #f4a700;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Daily Sales</div>
          <div style="font-size:26px; font-weight:700; color:#333; margin:6px 0;">
            ₱<?= number_format($dailySales, 2) ?>
          </div>
          <div style="font-size:12px; color:#aaa;">Today</div>
        </div>

        <div class="card" style="border-left:5px solid #f47c00;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Monthly Sales</div>
          <div style="font-size:26px; font-weight:700; color:#333; margin:6px 0;">
            ₱<?= number_format($monthlySales, 2) ?>
          </div>
          <div style="font-size:12px; color:#aaa;"><?= date('F Y') ?></div>
        </div>

        <div class="card" style="border-left:5px solid #e03e00;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Yearly Sales</div>
          <div style="font-size:26px; font-weight:700; color:#333; margin:6px 0;">
            ₱<?= number_format($yearlySales, 2) ?>
          </div>
          <div style="font-size:12px; color:#aaa;"><?= date('Y') ?></div>
        </div>

      </div>

      <!-- ── ROW 2: ORDER + INVENTORY STAT CARDS ──────── -->
      <div class="card-container" style="margin-bottom:18px;">

        <div class="card" style="border-left:5px solid #2196F3;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Completed Orders</div>
          <div style="font-size:26px; font-weight:700; color:#2196F3; margin:6px 0;">
            <?= number_format($totalOrders) ?>
          </div>
          <div style="font-size:12px; color:#aaa;">All time</div>
        </div>

        <div class="card" style="border-left:5px solid #FF9800;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Pending Orders</div>
          <div style="font-size:26px; font-weight:700; color:#FF9800; margin:6px 0;">
            <?= number_format($pendingOrders) ?>
          </div>
          <div style="font-size:12px; color:#aaa;">Awaiting cashier</div>
        </div>

        <div class="card" style="border-left:5px solid #4CAF50;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Good Stock</div>
          <div style="font-size:26px; font-weight:700; color:#4CAF50; margin:6px 0;">
            <?= $goodStock ?> / <?= $totalPizzas ?>
          </div>
          <div style="font-size:12px; color:#aaa;">Pizzas with stock ≥ 10</div>
        </div>

        <div class="card" style="border-left:5px solid #f44336;">
          <div style="font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Low / Out of Stock</div>
          <div style="font-size:26px; font-weight:700; color:#f44336; margin:6px 0;">
            <?= $lowStock + $outOfStock ?>
          </div>
          <div style="font-size:12px; color:#aaa;">
            <?= $outOfStock ?> out · <?= $lowStock ?> low (< 10)
          </div>
        </div>

      </div>

      <!-- ── ROW 3: CHARTS SIDE BY SIDE ───────────────── -->
      <div style="display:flex; gap:18px; margin-bottom:18px;">

        <!-- Top Selling Pizzas Bar Chart -->
        <div class="card" style="flex:1.4; min-width:0;">
          <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:#555;">
            🍕 Top Selling Pizzas
          </div>
          <canvas id="topPizzasChart" height="220"></canvas>
        </div>

        <!-- Monthly Sales Trend Line Chart -->
        <div class="card" style="flex:1; min-width:0;">
          <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:#555;">
            📈 Monthly Sales Trend (Last 6 Months)
          </div>
          <canvas id="salesTrendChart" height="220"></canvas>
        </div>

      </div>

      <!-- ── ROW 4: INVENTORY STATUS TABLE ─────────────── -->
      <div class="card">
        <div style="font-size:13px; font-weight:700; margin-bottom:12px; color:#555;">
          📦 Inventory Alert — Pizzas Running Low or Out of Stock
        </div>

        <?php if ($lowStock + $outOfStock === 0): ?>
          <p style="color:green; font-size:13px;">✅ All pizzas are well-stocked!</p>
        <?php else: ?>
          <table style="width:100%; border-collapse:collapse; font-size:13px;">
            <tr style="background:#f5f5f5; text-align:left;">
              <th style="padding:8px; border-bottom:2px solid #ddd;">Pizza</th>
              <th style="padding:8px; border-bottom:2px solid #ddd;">Category</th>
              <th style="padding:8px; border-bottom:2px solid #ddd;">Stock</th>
              <th style="padding:8px; border-bottom:2px solid #ddd;">Status</th>
            </tr>
            <?php while ($p = $lowStockPizzas->fetch_assoc()): ?>
              <?php
                $s = (int)$p['stock'];
                $statusLabel = $s === 0 ? "Out of Stock" : "Low Stock";
                $statusColor = $s === 0 ? "#f44336" : "#FF9800";
                $rowBg       = $s === 0 ? "#fff5f5" : "#fffbf0";
              ?>
              <tr style="background:<?= $rowBg ?>;">
                <td style="padding:8px; border-bottom:1px solid #eee;"><?= htmlspecialchars($p['pizza_name']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #eee;"><?= htmlspecialchars($p['category']) ?></td>
                <td style="padding:8px; border-bottom:1px solid #eee; font-weight:700; color:<?= $statusColor ?>;">
                  <?= $s ?>
                </td>
                <td style="padding:8px; border-bottom:1px solid #eee;">
                  <span style="
                    background:<?= $statusColor ?>;
                    color:#fff;
                    font-size:11px;
                    padding:2px 8px;
                    border-radius:12px;
                    font-weight:600;
                  "><?= $statusLabel ?></span>
                </td>
              </tr>
            <?php endwhile; ?>
          </table>
        <?php endif; ?>
      </div>

    </div>

    <!-- ✅ PRODUCTS -->
    <div id="products" class="section">
      <h2>Products</h2>

<div style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">

  <label>Pizza:</label>
  <input type="text" id="searchPizza" placeholder="Search pizza..." onkeyup="searchPizza()">

  <button onclick="searchPizza()">SEARCH</button>

  <select id="filterCategory" onchange="filterPizza()">
    <option value="all">All</option>
    <option value="alphabetical">Alphabetical (A-Z)</option>

    <?php
    $filterCats = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");
    while ($fc = $filterCats->fetch_assoc()) {
      echo "<option value='" . htmlspecialchars($fc['category_name']) . "'>"
           . htmlspecialchars($fc['category_name']) .
           "</option>";
    }
    ?>
  </select>

  <button onclick="showSection('addPizza')">Add New Pizza</button>

</div>

      <table border="1" width="100%" id="pizzaTable">
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

  <td style='text-align:center; cursor:pointer;'>
    <i class='fa-solid fa-pen'
       onclick='openEditPizza(" . $row['pizza_id'] . ")'>
    </i>
  </td>

  <td style='text-align:center; color:red; cursor:pointer;'>
  <i class='fa-solid fa-trash'
     onclick='confirmDelete(" . $row['pizza_id'] . ")'>
  </i>
</td>
</tr>";

        }
        ?>
      </table>

    </div>

    <!-- ✅ EDIT PIZZA SECTION -->
<div id="editPizza" class="section">

  <h2>Edit Pizza</h2>

  <div class="signup-form">

<!-- ✅ Pizza Image -->
<label>Pizza Image:</label>

<img id="editImagePreview" style="width:150px; display:block; margin-bottom:10px;">

<input type="file" id="editImage">


  <!-- ✅ Pizza Name -->
  <label>Pizza Name:</label>
  <input type="text" id="editName">

  <!-- ✅ Category -->
  <label>Category:</label>
  <select id="editCategory">
  <?php 
  while ($cat = $categories->fetch_assoc()) {
    echo "<option value='" . htmlspecialchars($cat['category_name']) . "'>" 
         . htmlspecialchars($cat['category_name']) . 
         "</option>";
  }
  ?>
</select>

  <!-- ✅ Ingredients -->
  <label>Ingredients:</label>
  <textarea id="editIngredients"></textarea>

  <!-- ✅ PRICES -->
  <h3 style="margin-top:20px;">PRICES:</h3>

  <label>9" Quickmelt:</label>
  <input type="number" id="p9q">

  <label>11" Quickmelt:</label>
  <input type="number" id="p11q">

  <label>9" Mozzarella:</label>
  <input type="number" id="p9m">

  <label>11" Mozzarella:</label>
  <input type="number" id="p11m">

  <!-- ✅ BUTTONS -->
  <div style="display:flex; gap:10px; margin-top:20px;">
    <button type="button" onclick="revertEdit()">REVERT TO ORIGINAL</button>
    <button type="button" onclick="saveEdit()">SAVE CHANGES</button>
  </div>

</div>

</div>

<!-- ✅ ADD NEW PIZZA SECTION -->
<div id="addPizza" class="section">

  <h2>Add New Pizza</h2>

  <div class="signup-form">

    <!-- ✅ DEFAULT IMAGE -->
    <label>Pizza Image:</label>

    <img id="addImagePreview" 
         src="menu/Other Flavors/Default.png"
         style="width:150px; display:block; margin-bottom:10px;">

    <input type="file" id="addImage">

    <!-- ✅ NAME -->
    <label>Pizza Name:</label>
    <input type="text" id="addName">

    <!-- ✅ CATEGORY DROPDOWN -->
    <label>Category:</label>
    <select id="addCategory">
      <option value="" disabled selected>Choose a pizza category...</option>
      <?php
      $categories2 = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");
      while ($cat = $categories2->fetch_assoc()) {
        echo "<option value='" . htmlspecialchars($cat['category_name']) . "'>"
             . htmlspecialchars($cat['category_name']) .
             "</option>";
      }
      ?>
    </select>

    <!-- ✅ NEW CATEGORY -->
    <label>Create New Category:</label>
    <input type="text" id="newCategory">

    <!-- ✅ INGREDIENTS -->
    <label>Ingredients:</label>
    <textarea id="addIngredients"></textarea>

    <!-- ✅ PRICES -->
    <h3 style="margin-top:20px;">PRICES:</h3>

    <label>9" Quickmelt:</label>
    <input type="number" id="add_p9q">

    <label>11" Quickmelt:</label>
    <input type="number" id="add_p11q">

    <label>9" Mozzarella:</label>
    <input type="number" id="add_p9m">

    <label>11" Mozzarella:</label>
    <input type="number" id="add_p11m">

    <!-- ✅ BUTTON -->
    <div style="display:flex; gap:10px; margin-top:20px;">

  <button type="button" onclick="resetAddPizza()">RESET</button>

  <button type="button" onclick="showSection('products')">CANCEL</button>

  <button type="button" onclick="saveNewPizza()">SAVE NEW PIZZA</button>

</div>

  </div>

</div>



    <!-- ✅ USERS -->
    <div id="users" class="section">
      <h2>Users</h2>

<div style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">

  <label>User:</label>
  <input type="text" id="searchUser" placeholder="Search user..." onkeyup="searchUser()">

  <button onclick="searchUser()">SEARCH</button>

  <select id="filterUser" onchange="filterUser()">
    <option value="all">All</option>
    <option value="alphabetical">Alphabetical (A-Z)</option>

    <?php
    $roles = $conn->query("SELECT DISTINCT role FROM users");

    while ($r = $roles->fetch_assoc()) {
      echo "<option value='" . htmlspecialchars($r['role']) . "'>"
           . htmlspecialchars($r['role']) .
           "</option>";
    }
    ?>
  </select>

  <button onclick="showSection('addUser')">Add User</button>

</div>


      <table border="1" width="100%" id="userTable">
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Password</th>
          <th>Role</th>
          <th>Birth Date</th>
          <th>Gender</th>
          <th>Mobile Number</th>
          <th>Email</th>
          <th>Created</th>
          <th>EDIT</th>
          <th>DEL</th>
        </tr>

        <?php
          $users = $conn->query("SELECT * FROM users");

          while ($u = $users->fetch_assoc()) {

            echo "<tr>
              <td>{$u['user_id']}</td>
              <td>{$u['username']}</td>
              <td>{$u['password']}</td>
              <td>{$u['role']}</td>
              <td>{$u['birth_date']}</td>
              <td>{$u['gender']}</td>
              <td>{$u['mobile_number']}</td>
              <td>{$u['email']}</td>
              <td>{$u['created_at']}</td>

              <td style='text-align:center; cursor:pointer;'>
                <i class='fa-solid fa-pen'
   onclick='openEditUser(
     {$u['user_id']},
     \"{$u['username']}\",
     \"{$u['password']}\",
     \"{$u['role']}\",
     \"{$u['birth_date']}\",
     \"{$u['gender']}\",
     \"{$u['mobile_number']}\",
     \"{$u['email']}\"
   )'>
</i>
              </td>

              <td style='text-align:center; cursor:pointer;'>

                ";
                
                if ($u['user_id'] == $_SESSION['user_id']) {

                  echo "<i class='fa-solid fa-ban'
          style='color:red; cursor:not-allowed;' 
          title='You cannot delete your own account'>
      </i>";

                } else {

                  echo "<i class='fa-solid fa-trash'
                          style='color:red; cursor:pointer;'
                          onclick='confirmDeleteUser(" . $u['user_id'] . ")'>
                        </i>";

                }

                echo "

              </td>
            </tr>";

          }
          ?>

      </table>

    </div>

    <!-- ✅ ADD USER SECTION -->
<div id="addUser" class="section">

  <h2>Add User</h2>

  <div class="signup-form">

    <!-- Username -->
    <label>Username:</label>
    <input type="text" id="addUserName">

    <!-- Password -->
    <label>Password:</label>
    <div style="position:relative;">

  <input type="password" id="addPassword" style="width:100%;">

  <i class="fa-solid fa-eye"
     onclick="toggleAddPassword()"
     style="
       position:absolute;
       right:10px;
       top:50%;
       transform:translateY(-50%);
       cursor:pointer;
     ">
  </i>

</div>

    <!-- Role -->
    <label>Role:</label>
    <div class="inline-group">
      <label><input type="radio" name="addRole" value="Admin"> Admin</label>
      <label><input type="radio" name="addRole" value="Cashier"> Cashier</label>
      <label><input type="radio" name="addRole" value="Customer"> Customer</label>
    </div>

    <!-- Birth Date -->
    <label>Birth Date:</label>
    <div style="display:flex; gap:10px;">
      <select id="dobMonth"></select>
<select id="dobDay"></select>
<select id="dobYear"></select>
    </div>

    <!-- Gender -->
    <label>Gender:</label>
    <div class="inline-group">
      <label><input type="radio" name="addGender" value="Male"> Male</label>
      <label><input type="radio" name="addGender" value="Female"> Female</label>
      <label><input type="radio" name="addGender" value="Other"> Other</label>
    </div>

    <!-- Mobile -->
    <label>Mobile Number:</label>
    <input
  type="text" 
  id="addMobile"
  oninput="restrictAddMobile(this)"
>

    <!-- Email -->
    <label>Email:</label>
    <input type="text" id="addEmail">

    <!-- BUTTONS -->
    <div style="display:flex; gap:10px; margin-top:20px;">
      <button type="button" onclick="clearAddUser()">CLEAR</button>
      <button type="button" onclick="showSection('users')">BACK</button>
      <button type="button" onclick="addUser()">ADD USER</button>
    </div>

  </div>

</div>

<!-- ✅ EDIT USER SECTION -->
<div id="editUser" class="section">

  <h2>Edit User</h2>

  <div class="signup-form">

    <label>Username:</label>
    <input type="text" id="editUserName">

    <label>Password:</label>
    <div style="position:relative;">
      <input type="password" id="editPassword" style="width:100%;">
      <i class="fa-solid fa-eye"
         onclick="toggleEditPassword()"
         style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">
      </i>
    </div>

    <label>Role:</label>
    <div class="inline-group">
      <label><input type="radio" name="editRole" value="Admin"> Admin</label>
      <label><input type="radio" name="editRole" value="Cashier"> Cashier</label>
      <label><input type="radio" name="editRole" value="Customer"> Customer</label>
    </div>

    <label>Birth Date:</label>
    <div style="display:flex; gap:10px;">
      <select id="editMonth"></select>
      <select id="editDay"></select>
      <select id="editYear"></select>
    </div>

    <label>Gender:</label>
    <div class="inline-group">
      <label><input type="radio" name="editGender" value="Male"> Male</label>
      <label><input type="radio" name="editGender" value="Female"> Female</label>
      <label><input type="radio" name="editGender" value="Other"> Other</label>
    </div>

    <label>Mobile Number:</label>
    <input type="text" id="editMobile" oninput="restrictAddMobile(this)">

    <label>Email:</label>
    <input type="text" id="editEmail">

    <div style="display:flex; gap:10px; margin-top:20px;">
      <button type="button" onclick="revertUser()">REVERT TO ORIGINAL</button>
      <button type="button" onclick="showSection('users')">BACK</button>
      <button type="button" onclick="saveUserChanges()">SAVE CHANGES</button>
    </div>

  </div>

</div>

  </div>

</div>

<!-- ✅ SWITCH SECTIONS -->
<script>
function showSection(section) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById(section).classList.add('active');
}


window.onload = function () {
  // ✅ restore active section after page reload
  const saved = sessionStorage.getItem("activeSection");
  if (saved) {
    showSection(saved);
    sessionStorage.removeItem("activeSection");
  }

  // ✅ populate Birth Date dropdowns
  populateAddUserDOB();
};

</script>

<div id="stockModal" class="modal">

  <div class="modal-content">

    <h3>Stock In</h3>

    <img id="modalPizzaImg" style="width:240px; display:block; margin:auto;">

    <p id="modalPizzaName" style="text-align:center; font-weight:bold;"></p>

    <p style="text-align:center;">
      Current Stock: 
      <span id="modalStock"></span>
    </p>

    <label>Add Stock</label>
    <input type="number" id="stockInput" min="1">

    <div style="display:flex; gap:10px; margin-top:15px;">
      <button onclick="closeStockModal()">CANCEL</button>
      <button onclick="addStock()">ADD STOCK</button>
    </div>

  </div>

</div>

<script>
let currentPizza = "";
let currentStock = 0;

function openStockModal(name, image, stock) {
  currentPizza = name;
  currentStock = stock;

  document.getElementById("modalPizzaName").innerText = name;
  document.getElementById("modalPizzaImg").src = image;

  let stockSpan = document.getElementById("modalStock");
  stockSpan.innerText = stock;

  // ✅ color logic
  stockSpan.style.color = (stock < 10) ? "red" : "green";

  document.getElementById("stockModal").style.display = "block";
}

function closeStockModal() {
  document.getElementById("stockModal").style.display = "none";
  document.getElementById("stockInput").value = "";
}

function addStock() {
  const addValue = parseInt(document.getElementById("stockInput").value);

  if (!addValue || addValue <= 0) {
    alert("Enter valid stock amount.");
    return;
  }

  fetch("add_stock.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `pizza_name=${encodeURIComponent(currentPizza)}&add=${addValue}`
  })
  .then(res => res.text())
  .then(() => {
  sessionStorage.setItem("activeSection", "products");
  location.reload();
});
}
</script>

<script>

let currentPizzaId = null;

function openEditPizza(id) {

currentPizzaId = id;

  fetch("get_pizza_details.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "pizza_id=" + id
  })
  .then(res => res.json())
  .then(data => {

    const pizza = data.pizza;
    const prices = data.prices;

    document.getElementById("editImagePreview").src = pizza.image_path;

    // ✅ MAIN INFO
    document.getElementById("editName").value = pizza.pizza_name;
    document.getElementById("editCategory").value = pizza.category;
    document.getElementById("editIngredients").value = pizza.ingredients;

    // ✅ PRICES (HANDLE MISSING VARIANTS)
    document.getElementById("p9q").value = prices["9_quickmelt"] || "";
    document.getElementById("p11q").value = prices["11_quickmelt"] || "";
    document.getElementById("p9m").value = prices["9_mozzarella"] || "";
    document.getElementById("p11m").value = prices["11_mozzarella"] || "";

    // ✅ store original values
originalPizza = {
  name: pizza.pizza_name,
  category: pizza.category,
  ingredients: pizza.ingredients,
  p9q: prices["9_quickmelt"] || "",
  p11q: prices["11_quickmelt"] || "",
  p9m: prices["9_mozzarella"] || "",
  p11m: prices["11_mozzarella"] || "",
  image: pizza.image_path
};

    // ✅ SWITCH VIEW
    showSection("editPizza");

  });

}

function cancelEdit() {
  showSection("products");
}

</script>

<script>
function saveEdit() {

  const formData = new FormData();

  formData.append("pizza_id", currentPizzaId);
  formData.append("name", document.getElementById("editName").value);
  formData.append("category", document.getElementById("editCategory").value);
  formData.append("ingredients", document.getElementById("editIngredients").value);

  formData.append("p9q", document.getElementById("p9q").value);
  formData.append("p11q", document.getElementById("p11q").value);
  formData.append("p9m", document.getElementById("p9m").value);
  formData.append("p11m", document.getElementById("p11m").value);

  const imageInput = document.getElementById("editImage");

  if (imageInput.files.length > 0) {
    formData.append("image", imageInput.files[0]);
  }

  fetch("update_pizza.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(() => {
    sessionStorage.setItem("activeSection", "products");
    location.reload();
  });

}

function revertEdit() {

  document.getElementById("editName").value = originalPizza.name;
  document.getElementById("editCategory").value = originalPizza.category;
  document.getElementById("editIngredients").value = originalPizza.ingredients;

  document.getElementById("p9q").value = originalPizza.p9q;
  document.getElementById("p11q").value = originalPizza.p11q;
  document.getElementById("p9m").value = originalPizza.p9m;
  document.getElementById("p11m").value = originalPizza.p11m;

  document.getElementById("editImagePreview").src = originalPizza.image;

  // ✅ clear file input (important)
  document.getElementById("editImage").value = "";
}

function confirmDelete(id) {

  const confirmAction = confirm("Are you sure you want to delete this product?");

  if (!confirmAction) {
    return; // ✅ user clicked NO
  }

  // ✅ proceed delete
  fetch("delete_pizza.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "pizza_id=" + id
  })
  .then(res => res.text())
  .then(() => {
    // ✅ stay in products tab after delete
    sessionStorage.setItem("activeSection", "products");
    location.reload();
  });

}

function resetAddPizza() {
  const catDropdown = document.getElementById("addCategory");

  document.getElementById("addImagePreview").src = "menu/Other Flavors/Default.png";
  document.getElementById("addImage").value = "";
  document.getElementById("addName").value = "";
  document.getElementById("newCategory").value = "";
  document.getElementById("addIngredients").value = "";

  catDropdown.selectedIndex = 0;
  catDropdown.dataset.selected = "";
  catDropdown.disabled = false;
  document.getElementById("newCategory").disabled = false;

  document.getElementById("add_p9q").value = "";
  document.getElementById("add_p11q").value = "";
  document.getElementById("add_p9m").value = "";
  document.getElementById("add_p11m").value = "";
}

// ✅ CATEGORY DROPDOWN — save selected value in dataset so we can read it even when disabled
document.getElementById("addCategory").addEventListener("change", function () {
  this.dataset.selected = this.value;
  if (this.value !== "") {
    document.getElementById("newCategory").value = "";
    document.getElementById("newCategory").disabled = true;
  } else {
    document.getElementById("newCategory").disabled = false;
  }
});

// ✅ NEW CATEGORY INPUT — disable dropdown when typing a new category
document.getElementById("newCategory").addEventListener("input", function () {
  const catDropdown = document.getElementById("addCategory");
  if (this.value.trim() !== "") {
    catDropdown.dataset.selected = "";
    catDropdown.selectedIndex = 0;
    catDropdown.disabled = true;
  } else {
    catDropdown.disabled = false;
  }
});

// ✅ IMAGE PREVIEW
document.getElementById("addImage").addEventListener("change", function () {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function (e) {
      document.getElementById("addImagePreview").src = e.target.result;
    };
    reader.readAsDataURL(file);
  }
});

function saveNewPizza() {
  const name        = document.getElementById("addName").value.trim();
  const newCategory = document.getElementById("newCategory").value.trim();
  const ingredients = document.getElementById("addIngredients").value.trim();

  // Read category safely: use stored dataset value (works even when dropdown is disabled)
  const catDropdown = document.getElementById("addCategory");
  const category    = catDropdown.dataset.selected || catDropdown.value || "";

  const p9q  = document.getElementById("add_p9q").value.trim();
  const p11q = document.getElementById("add_p11q").value.trim();
  const p9m  = document.getElementById("add_p9m").value.trim();
  const p11m = document.getElementById("add_p11m").value.trim();

  // ✅ VALIDATION
  if (name === "") {
    alert("Pizza name is required.");
    return;
  }
  if (category === "" && newCategory === "") {
    alert("Please select or create a category.");
    return;
  }
  if (ingredients === "") {
    alert("Ingredients are required.");
    return;
  }
  if (p9q === "" && p11q === "" && p9m === "" && p11m === "") {
    alert("At least one price is required.");
    return;
  }

  // ✅ BUILD FORM DATA
  const formData = new FormData();
  formData.append("name",            name);
  formData.append("category",        newCategory !== "" ? newCategory : category);
  formData.append("is_new_category", newCategory !== "" ? "1" : "0");
  formData.append("ingredients",     ingredients);
  formData.append("p9q",             p9q  !== "" ? p9q  : "0");
  formData.append("p11q",            p11q !== "" ? p11q : "0");
  formData.append("p9m",             p9m  !== "" ? p9m  : "0");
  formData.append("p11m",            p11m !== "" ? p11m : "0");

  const imageInput = document.getElementById("addImage");
  if (imageInput.files.length > 0) {
    formData.append("image", imageInput.files[0]);
  }

  fetch("add_pizza.php", {
    method: "POST",
    body: formData
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() !== "success") {
      alert("Error saving pizza:\n" + response);
      return;
    }
    sessionStorage.setItem("activeSection", "products");
    location.reload();
  })
  .catch(err => {
    alert("Network error: " + err);
  });
}

function searchPizza() {

  const input = document.getElementById("searchPizza").value.toLowerCase();
  const table = document.getElementById("pizzaTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {

    const nameCell = rows[i].cells[0];
    if (!nameCell) continue;

    const name = nameCell.textContent.toLowerCase();

    if (name.includes(input)) {
      rows[i].style.display = "";
    } else {
      rows[i].style.display = "none";
    }

  }
}

function filterPizza() {

  const filter = document.getElementById("filterCategory").value;
  const table = document.getElementById("pizzaTable");
  const rows = Array.from(table.rows).slice(1);

  // ✅ RESET DISPLAY FIRST
  rows.forEach(row => row.style.display = "");

  if (filter === "all") return;

  // ✅ FILTER BY CATEGORY
  if (filter !== "alphabetical") {

    rows.forEach(row => {
      const category = row.cells[1].textContent;

      if (category !== filter) {
        row.style.display = "none";
      }
    });

  }

  // ✅ SORT ALPHABETICALLY
  if (filter === "alphabetical") {

    rows.sort((a, b) => {
      const nameA = a.cells[0].textContent.toLowerCase();
      const nameB = b.cells[0].textContent.toLowerCase();
      return nameA.localeCompare(nameB);
    });

    const tbody = table.tBodies[0];

    rows.forEach(row => tbody.appendChild(row));
  }
}

function searchUser() {

  const input = document.getElementById("searchUser").value.toLowerCase();
  const table = document.getElementById("userTable");
  const rows = table.getElementsByTagName("tr");

  for (let i = 1; i < rows.length; i++) {

    const nameCell = rows[i].cells[1]; // username column
    if (!nameCell) continue;

    const name = nameCell.textContent.toLowerCase();

    if (name.includes(input)) {
      rows[i].style.display = "";
    } else {
      rows[i].style.display = "none";
    }

  }

}

function filterUser() {

  const filter = document.getElementById("filterUser").value;
  const table = document.getElementById("userTable");
  const rows = Array.from(table.rows).slice(1);

  // ✅ reset first
  rows.forEach(row => row.style.display = "");

  if (filter === "all") return;

  // ✅ FILTER BY ROLE
  if (filter !== "alphabetical") {

    rows.forEach(row => {
      const role = row.cells[3].textContent;

      if (role !== filter) {
        row.style.display = "none";
      }
    });

  }

  // ✅ SORT A-Z by username
  if (filter === "alphabetical") {

    rows.sort((a, b) => {
      const nameA = a.cells[1].textContent.toLowerCase();
      const nameB = b.cells[1].textContent.toLowerCase();
      return nameA.localeCompare(nameB);
    });

    const tbody = table.tBodies[0];
    rows.forEach(row => tbody.appendChild(row));
  }

}

function clearAddUser() {

  document.getElementById("addUserName").value = "";
  document.getElementById("addPassword").value = "";
  document.getElementById("addMobile").value = "";
  document.getElementById("addEmail").value = "";

  // ✅ reset radios
  document.querySelectorAll('input[name="addRole"]').forEach(r => r.checked = false);
  document.querySelectorAll('input[name="addGender"]').forEach(r => r.checked = false);

  // ✅ reset DOB
  document.getElementById("dobMonth").selectedIndex = 0;
  document.getElementById("dobDay").selectedIndex   = 0;
  document.getElementById("dobYear").selectedIndex  = 0;
}


function toggleAddPassword() {

  const input = document.getElementById("addPassword");

  if (input.type === "password") {
    input.type = "text";
  } else {
    input.type = "password";
  }

}



function populateAddUserDOB() {

  const month = document.getElementById("dobMonth");
  const day = document.getElementById("dobDay");
  const year = document.getElementById("dobYear");

  if (!month || !day || !year) return;

  // ✅ MONTHS
  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  month.innerHTML = "<option value='' disabled selected>Month</option>";

  months.forEach((m, i) => {
    let opt = document.createElement("option");
    opt.value = i + 1;
    opt.text = m;
    month.appendChild(opt);
  });

  // ✅ YEARS (current year back 100)
  const currentYear = new Date().getFullYear();

  year.innerHTML = "<option value='' disabled selected>Year</option>";

  for (let y = currentYear; y >= currentYear - 100; y--) {
    let opt = document.createElement("option");
    opt.value = y;
    opt.text = y;
    year.appendChild(opt);
  }

  // ✅ UPDATE DAYS BASED ON MONTH/YEAR
  function updateDays() {
    day.innerHTML = "<option value='' disabled selected>Day</option>";

    const m = parseInt(month.value);
    const y = parseInt(year.value);

    if (!m || !y) return;

    const daysInMonth = new Date(y, m, 0).getDate();

    for (let d = 1; d <= daysInMonth; d++) {
      let opt = document.createElement("option");
      opt.value = d;
      opt.text = d;
      day.appendChild(opt);
    }
  }

  month.addEventListener("change", updateDays);
  year.addEventListener("change", updateDays);
}

function addUser() {

  const username = document.getElementById("addUserName").value.trim();
  const password = document.getElementById("addPassword").value.trim();

  const role = document.querySelector('input[name="addRole"]:checked');
  const gender = document.querySelector('input[name="addGender"]:checked');

  const month = document.getElementById("dobMonth").value;
  const day = document.getElementById("dobDay").value;
  const year = document.getElementById("dobYear").value;

  const mobile = document.getElementById("addMobile").value.trim();
  const email = document.getElementById("addEmail").value.trim();

  // ✅ VALIDATION
  if (!username || !password || !role || !gender || !month || !day || !year || !mobile || !email) {
    alert("Please complete all fields.");
    return;
  }

  // ✅ FORMAT DATE
  const birthdate = year + "-" + month.padStart(2, '0') + "-" + day.padStart(2, '0');

  // ✅ SEND DATA
  fetch("add_user.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body:
      "username=" + encodeURIComponent(username) +
      "&password=" + encodeURIComponent(password) +
      "&role=" + role.value +
      "&birth_date=" + birthdate +
      "&gender=" + gender.value +
      "&mobile=" + encodeURIComponent(mobile) +
      "&email=" + encodeURIComponent(email)
  })
  .then(res => res.text())
  .then(() => {
    sessionStorage.setItem("activeSection", "users");
    location.reload();
  });

}

function restrictAddMobile(input) {

  // ✅ remove non-numeric characters
  input.value = input.value.replace(/[^0-9]/g, '');

  // ✅ limit to 11 digits
  if (input.value.length > 11) {
    input.value = input.value.slice(0, 11);
  }

}

let currentUserId = null;
let originalUser = {};

function openEditUser(id, username, password, role, birth, gender, mobile, email) {
  populateEditUserDOB();
  currentUserId = id;

  // ✅ store original
  originalUser = { username, password, role, birth, gender, mobile, email };

  // ✅ fill fields
  document.getElementById("editUserName").value = username;
  document.getElementById("editPassword").value = password;
  document.getElementById("editMobile").value = mobile;
  document.getElementById("editEmail").value = email;

  // ✅ role
  document.querySelectorAll('input[name="editRole"]').forEach(r => {
  r.checked = (r.value.toLowerCase() === role.toLowerCase());
});

  // ✅ gender
  document.querySelectorAll('input[name="editGender"]').forEach(r => {
    r.checked = (r.value === gender);
  });

  // ✅ split birth_date (YYYY-MM-DD)
if (birth) {

  const parts = birth.split("-");

  setTimeout(() => {

    document.getElementById("editYear").value = parts[0];
    document.getElementById("editMonth").value = parseInt(parts[1]);

    // ✅ trigger day generation first
    document.getElementById("editMonth").dispatchEvent(new Event("change"));
    document.getElementById("editYear").dispatchEvent(new Event("change"));

    setTimeout(() => {
      document.getElementById("editDay").value = parseInt(parts[2]);
    }, 50);

  }, 50);
}
``

  showSection("editUser");
}

function revertUser() {

  openEditUser(
    currentUserId,
    originalUser.username,
    originalUser.password,
    originalUser.role,
    originalUser.birth,
    originalUser.gender,
    originalUser.mobile,
    originalUser.email
  );

}

function toggleEditPassword() {
  const input = document.getElementById("editPassword");
  input.type = (input.type === "password") ? "text" : "password";
}

function saveUserChanges() {

  const formData = new FormData();

  formData.append("user_id", currentUserId);
  formData.append("username", document.getElementById("editUserName").value);
  formData.append("password", document.getElementById("editPassword").value);

  const role = document.querySelector('input[name="editRole"]:checked')?.value;
  formData.append("role", role);

  const y = document.getElementById("editYear").value;
  const m = document.getElementById("editMonth").value;
  const d = document.getElementById("editDay").value;

  formData.append("birth", `${y}-${m}-${d}`);

  const gender = document.querySelector('input[name="editGender"]:checked')?.value;
  formData.append("gender", gender);

  formData.append("mobile", document.getElementById("editMobile").value);
  formData.append("email", document.getElementById("editEmail").value);

  fetch("update_user.php", {
    method: "POST",
    body: formData
  })
  .then(() => {
    sessionStorage.setItem("activeSection", "users");
    location.reload();
  });

}

function populateEditUserDOB() {

  const month = document.getElementById("editMonth");
  const day = document.getElementById("editDay");
  const year = document.getElementById("editYear");

  if (!month || !day || !year) return;

  // ✅ MONTHS
  const months = [
    "January","February","March","April","May","June",
    "July","August","September","October","November","December"
  ];

  month.innerHTML = "<option value='' disabled selected>Month</option>";

  months.forEach((m, i) => {
    let opt = document.createElement("option");
    opt.value = i + 1;
    opt.text = m;
    month.appendChild(opt);
  });

  // ✅ YEARS
  const currentYear = new Date().getFullYear();

  year.innerHTML = "<option value='' disabled selected>Year</option>";

  for (let y = currentYear; y >= currentYear - 100; y--) {
    let opt = document.createElement("option");
    opt.value = y;
    opt.text = y;
    year.appendChild(opt);
  }

  // ✅ DAYS
  function updateDays() {

    day.innerHTML = "<option value='' disabled selected>Day</option>";

    const m = parseInt(month.value);
    const y = parseInt(year.value);

    if (!m || !y) return;

    const daysInMonth = new Date(y, m, 0).getDate();

    for (let d = 1; d <= daysInMonth; d++) {
      let opt = document.createElement("option");
      opt.value = d;
      opt.text = d;
      day.appendChild(opt);
    }
  }

  month.addEventListener("change", updateDays);
  year.addEventListener("change", updateDays);
}

function confirmDeleteUser(id) {

  const confirmAction = confirm("Are you sure you want to delete this user?");

  if (!confirmAction) {
    return; // ✅ cancel
  }

  fetch("delete_user.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "user_id=" + id
  })
  .then(res => res.text())
  .then(() => {
    // ✅ stay in USERS after reload
    sessionStorage.setItem("activeSection", "users");
    location.reload();
  });

}

</script>

<!-- ✅ DASHBOARD CHARTS -->
<script>
(function () {

  // ── Data from PHP ─────────────────────────────────────────────
  const topLabels = <?= json_encode($chartLabels) ?>;
  const topData   = <?= json_encode($chartData) ?>;
  const trendLabels = <?= json_encode($trendLabels) ?>;
  const trendData   = <?= json_encode($trendData) ?>;

  // ── Palette ───────────────────────────────────────────────────
  const barColors = [
    '#f4a700','#f47c00','#e03e00','#c0392b','#e67e22',
    '#d35400','#e74c3c','#f39c12','#ca6f1e','#a93226'
  ];

  // ── TOP SELLING PIZZAS (Horizontal Bar) ──────────────────────
  const topCtx = document.getElementById('topPizzasChart');
  if (topCtx) {
    if (topLabels.length === 0) {
      topCtx.parentElement.innerHTML += '<p style="color:#aaa;font-size:13px;">No sales data yet.</p>';
      topCtx.style.display = 'none';
    } else {
      new Chart(topCtx, {
        type: 'bar',
        data: {
          labels: topLabels,
          datasets: [{
            label: 'Units Sold',
            data: topData,
            backgroundColor: barColors.slice(0, topLabels.length),
            borderRadius: 5,
            borderSkipped: false
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: ctx => ` ${ctx.parsed.x} units sold`
              }
            }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: { stepSize: 1, font: { size: 11 } },
              grid: { color: '#f0f0f0' }
            },
            y: {
              ticks: { font: { size: 11 } },
              grid: { display: false }
            }
          }
        }
      });
    }
  }

  // ── MONTHLY SALES TREND (Line) ────────────────────────────────
  const trendCtx = document.getElementById('salesTrendChart');
  if (trendCtx) {
    if (trendLabels.length === 0) {
      trendCtx.parentElement.innerHTML += '<p style="color:#aaa;font-size:13px;">No trend data yet.</p>';
      trendCtx.style.display = 'none';
    } else {
      new Chart(trendCtx, {
        type: 'line',
        data: {
          labels: trendLabels,
          datasets: [{
            label: 'Sales (₱)',
            data: trendData,
            fill: true,
            tension: 0.4,
            borderColor: '#f47c00',
            backgroundColor: 'rgba(244,124,0,0.12)',
            pointBackgroundColor: '#f47c00',
            pointRadius: 5
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: ctx => ` ₱${ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })}`
              }
            }
          },
          scales: {
            x: {
              ticks: { font: { size: 11 } },
              grid: { color: '#f0f0f0' }
            },
            y: {
              beginAtZero: true,
              ticks: {
                font: { size: 11 },
                callback: v => '₱' + v.toLocaleString()
              },
              grid: { color: '#f0f0f0' }
            }
          }
        }
      });
    }
  }

})();
</script>

</body>
</html>