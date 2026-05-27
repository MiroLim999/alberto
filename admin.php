<?php
session_start();

// ── Role guard: admin only ────────────────
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

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
  FROM v_orders_full
  WHERE status = 'completed'
    AND DATE(created_at) = CURDATE()
")->fetch_assoc()['total'];

$monthlySales = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) AS total
  FROM v_orders_full
  WHERE status = 'completed'
    AND MONTH(created_at) = MONTH(CURDATE())
    AND YEAR(created_at)  = YEAR(CURDATE())
")->fetch_assoc()['total'];

$yearlySales = $conn->query("
  SELECT COALESCE(SUM(total_amount), 0) AS total
  FROM v_orders_full
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
  FROM v_pizzas_full
  WHERE stock < 10
  ORDER BY stock ASC
  LIMIT 10
");

// ── TOP SELLING PIZZAS (for bar chart) ──
$topPizzas = $conn->query("
  SELECT oi.pizza_name, SUM(oi.quantity) AS total_sold
  FROM v_order_items_full oi
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
  FROM v_orders_full
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

// ═══════════════════════════════════════════
// SALES SECTION QUERIES
// ═══════════════════════════════════════════

// ── ORDER STATUS BREAKDOWN ──────────────────
$salesStatusBreakdown = [];
$statusRes = $conn->query("
  SELECT status, COUNT(*) AS cnt, COALESCE(SUM(total_amount),0) AS revenue
  FROM v_orders_full
  GROUP BY status
");
while ($row = $statusRes->fetch_assoc()) {
  $salesStatusBreakdown[$row['status']] = $row;
}

// ── TOTAL REVENUE (completed only) ─────────
$totalRevenue = $conn->query("
  SELECT COALESCE(SUM(total_amount),0) AS total FROM v_orders_full WHERE status='completed'
")->fetch_assoc()['total'];

// ── AVERAGE ORDER VALUE ─────────────────────
$avgOrderVal = $conn->query("
  SELECT COALESCE(AVG(total_amount),0) AS avg FROM v_orders_full WHERE status='completed'
")->fetch_assoc()['avg'];

// ── TOTAL ITEMS SOLD ────────────────────────
$totalItemsSold = $conn->query("
  SELECT COALESCE(SUM(oi.quantity),0) AS total
  FROM order_items oi
  JOIN orders o ON oi.order_id = o.order_id
  WHERE o.status='completed'
")->fetch_assoc()['total'];

// ── PAYMENT METHOD BREAKDOWN ────────────────
$paymentBreakdown = [];
$payRes = $conn->query("
  SELECT payment_method, COUNT(*) AS cnt, COALESCE(SUM(total_amount),0) AS revenue
  FROM v_orders_full
  WHERE status='completed'
  GROUP BY payment_method
");
while ($row = $payRes->fetch_assoc()) {
  $paymentBreakdown[] = $row;
}

// ── ORDER TYPE BREAKDOWN (delivery vs pickup) ──
$orderTypeBreakdown = [];
$typeRes = $conn->query("
  SELECT order_type, COUNT(*) AS cnt, COALESCE(SUM(total_amount),0) AS revenue
  FROM v_orders_full
  WHERE status='completed'
  GROUP BY order_type
");
while ($row = $typeRes->fetch_assoc()) {
  $orderTypeBreakdown[] = $row;
}

// ── BEST SELLING ITEMS (by revenue) ────────
$topByRevenue = $conn->query("
  SELECT oi.pizza_name, SUM(oi.quantity) AS units, SUM(oi.total) AS revenue
  FROM v_order_items_full oi
  JOIN orders o ON oi.order_id = o.order_id
  WHERE o.status='completed'
  GROUP BY oi.pizza_name
  ORDER BY revenue DESC
  LIMIT 10
");

// ── RECENT ORDERS LIST ──────────────────────
$recentOrders = $conn->query("
  SELECT o.order_id, o.customer_name, o.total_amount, o.status,
         o.order_type, o.payment_method, o.created_at,
         (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.order_id) AS item_count
  FROM v_orders_full o
  ORDER BY o.created_at DESC
  LIMIT 15
");

// ── SALES BY SIZE ────────────────────────────
$sizeBreakdown = [];
$sizeRes = $conn->query("
  SELECT oi.size, SUM(oi.quantity) AS units, SUM(oi.total) AS revenue
  FROM v_order_items_full oi
  JOIN orders o ON oi.order_id = o.order_id
  WHERE o.status='completed'
  GROUP BY oi.size
  ORDER BY revenue DESC
");
while ($row = $sizeRes->fetch_assoc()) {
  $sizeBreakdown[] = $row;
}

// ── SALES BY CHEESE ──────────────────────────
$cheeseBreakdown = [];
$cheeseRes = $conn->query("
  SELECT oi.cheese, SUM(oi.quantity) AS units, SUM(oi.total) AS revenue
  FROM v_order_items_full oi
  JOIN orders o ON oi.order_id = o.order_id
  WHERE o.status='completed'
  GROUP BY oi.cheese
  ORDER BY revenue DESC
");
while ($row = $cheeseRes->fetch_assoc()) {
  $cheeseBreakdown[] = $row;
}

// ── WEEKLY SALES (last 7 days) ───────────────
$weeklySalesRes = $conn->query("
  SELECT DATE(created_at) AS sale_date,
         COUNT(*) AS order_count,
         COALESCE(SUM(total_amount),0) AS revenue
  FROM v_orders_full
  WHERE status='completed'
    AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
  GROUP BY sale_date
  ORDER BY sale_date ASC
");
$weeklyLabels = [];
$weeklyData   = [];
while ($row = $weeklySalesRes->fetch_assoc()) {
  $weeklyLabels[] = date('D M j', strtotime($row['sale_date']));
  $weeklyData[]   = (float)$row['revenue'];
}

// ── CHART DATA: Top by Revenue ───────────────
$topRevLabels = [];
$topRevData   = [];
while ($row = $topByRevenue->fetch_assoc()) {
  $topRevLabels[] = $row['pizza_name'];
  $topRevData[]   = (float)$row['revenue'];
}
// re-run for the table below
$topByRevenueTable = $conn->query("
  SELECT oi.pizza_name, SUM(oi.quantity) AS units, SUM(oi.total) AS revenue
  FROM v_order_items_full oi
  JOIN orders o ON oi.order_id = o.order_id
  WHERE o.status='completed'
  GROUP BY oi.pizza_name
  ORDER BY revenue DESC
  LIMIT 10
");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Admin Panel</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<style>

  /* ══════════════════════════════════════
     NAVBAR
  ══════════════════════════════════════ */
  .navbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    height: 64px;
    padding: 0 28px;
    background: #ffff00;
    border-bottom: 1.5px solid #f0e0ce;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .logo-img {
    height: 42px;
    object-fit: contain;
  }

  .nav-links {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .nav-links a {
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 8px;
    transition: background 0.15s, color 0.15s;
  }

  /* logged-in username link */
  .nav-links a.nav-user {
    color: #e06000;
    background: #fff4e8;
    border: 1.5px solid #fde8d0;
    display: flex;
    align-items: center;
    gap: 7px;
  }
  .nav-links a.nav-user:hover {
    background: #fde8d0;
  }

  /* logout / login / signup */
  .nav-links a.nav-logout,
  .nav-links a.nav-auth {
    color: #555;
    background: #f5f5f5;
  }
  .nav-links a.nav-logout:hover,
  .nav-links a.nav-auth:hover {
    background: #ebebeb;
    color: #222;
  }

  /* ══════════════════════════════════════
     LAYOUT
  ══════════════════════════════════════ */
  .admin-container {
    display: flex;
    height: calc(100vh - 64px);
    background: #f7f7f7;
  }

  /* ══════════════════════════════════════
     SIDEBAR
  ══════════════════════════════════════ */
  .sidebar {
    width: 220px;
    background: #fff;
    border-right: 1.5px solid #f0e0ce;
    padding: 24px 14px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex-shrink: 0;
  }

  .sidebar-logo-area {
    padding: 4px 10px 20px;
    border-bottom: 1.5px solid #f0e0ce;
    margin-bottom: 12px;
  }

  .sidebar-logo-area span {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #bbb;
  }

  .sidebar-nav-btn {
    display: flex;
    align-items: center;
    gap: 11px;
    width: 100%;
    padding: 11px 14px;
    border: none;
    background: transparent;
    cursor: pointer;
    text-align: left;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #666;
    transition: background 0.15s, color 0.15s;
  }

  .sidebar-nav-btn i {
    width: 16px;
    text-align: center;
    font-size: 14px;
    color: #ccc;
    transition: color 0.15s;
    flex-shrink: 0;
  }

  .sidebar-nav-btn:hover {
    background: #fff4e8;
    color: #e06000;
  }

  .sidebar-nav-btn:hover i {
    color: #f47c00;
  }

  .sidebar-nav-btn.active-nav {
    background: linear-gradient(135deg, #fff4e8, #fde8d0);
    color: #e06000;
    font-weight: 700;
  }

  .sidebar-nav-btn.active-nav i {
    color: #f47c00;
  }

  /* ══════════════════════════════════════
     MAIN PANEL
  ══════════════════════════════════════ */
  .main-panel {
    flex: 1;
    padding: 28px;
    overflow-y: auto;
    background: #f7f7f7;
  }

  /* ══════════════════════════════════════
     DASH CARDS
  ══════════════════════════════════════ */
  .card-container {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
  }

  .card {
    flex: 1;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
  }

  /* ══════════════════════════════════════
     SECTIONS
  ══════════════════════════════════════ */
  .section { display: none; }
  .active  { display: block; }

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

      <a href="#" class="nav-user"
         onclick="openEditUser(
           <?= $currentUser['user_id'] ?>,
           '<?= addslashes($currentUser['username']) ?>',
           '<?= addslashes($currentUser['password']) ?>',
           '<?= $currentUser['role'] ?>',
           '<?= $currentUser['birth_date'] ?>',
           '<?= $currentUser['gender'] ?>',
           '<?= addslashes($currentUser['mobile_number']) ?>',
           '<?= addslashes($currentUser['email']) ?>'
         )">
        <i class="fa-solid fa-circle-user"></i>
        <?= htmlspecialchars($_SESSION['username']) ?>
      </a>

      <a href="logout.php" class="nav-logout">
        <i class="fa-solid fa-right-from-bracket"></i> Log Out
      </a>

    <?php else: ?>
      <a href="signup.php" class="nav-auth">Sign Up</a>
      <a href="login.php"  class="nav-auth">Log In</a>
    <?php endif; ?>
  </div>
</header>

<!-- ✅ MAIN LAYOUT -->
<div class="admin-container">

<!-- ✅ SIDEBAR -->
<div class="sidebar">

  <div class="sidebar-logo-area">
    <span>Navigation</span>
  </div>

  <button class="sidebar-nav-btn active-nav" onclick="setNav(this, 'home')">
    <i class="fa-solid fa-gauge-high"></i> Dashboard
  </button>

  <button class="sidebar-nav-btn" onclick="setNav(this, 'products')">
    <i class="fa-solid fa-pizza-slice"></i> Products
  </button>

  <button class="sidebar-nav-btn" onclick="setNav(this, 'users')">
    <i class="fa-solid fa-users"></i> Users
  </button>

  <button class="sidebar-nav-btn" onclick="setNav(this, 'sales')">
    <i class="fa-solid fa-chart-line"></i> Sales
  </button>

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

  <style>
    /* ── PRODUCTS SECTION ── */
    .prod-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 22px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .prod-header h2 {
      font-size: 22px;
      font-weight: 800;
      color: #222;
      margin: 0;
    }
    .prod-toolbar {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }
    .prod-search-wrap {
      position: relative;
    }
    .prod-search-wrap i {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      color: #bbb;
      font-size: 13px;
    }
    .prod-search-wrap input {
      padding: 9px 14px 9px 32px;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      font-size: 13px;
      outline: none;
      background: #fafafa;
      transition: border 0.2s, box-shadow 0.2s;
      width: 200px;
    }
    .prod-search-wrap input:focus {
      border-color: #f47c00;
      box-shadow: 0 0 0 3px rgba(244,124,0,0.1);
      background: #fff;
    }
    .prod-filter {
      padding: 9px 12px;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      font-size: 13px;
      background: #fafafa;
      outline: none;
      cursor: pointer;
      transition: border 0.2s;
    }
    .prod-filter:focus { border-color: #f47c00; }
    .btn-add-pizza {
      padding: 9px 18px;
      background: linear-gradient(135deg, #f47c00, #e03e00);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 7px;
      box-shadow: 0 3px 10px rgba(224,62,0,0.25);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-add-pizza:hover {
      transform: translateY(-1px);
      box-shadow: 0 5px 16px rgba(224,62,0,0.35);
    }

    /* ── PIZZA TABLE ── */
    .pizza-table-wrap {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      overflow: hidden;
    }
    .pizza-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .pizza-table thead tr {
      background: #fdf3ea;
      border-bottom: 2px solid #f0e0ce;
    }
    .pizza-table th {
      padding: 13px 16px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #b06020;
    }
    .pizza-table td {
      padding: 13px 16px;
      border-bottom: 1px solid #f5f5f5;
      color: #333;
      vertical-align: middle;
    }
    .pizza-table tbody tr:last-child td { border-bottom: none; }
    .pizza-table tbody tr:hover td { background: #fffbf7; }
    .pizza-name-cell {
      font-weight: 700;
      color: #222;
    }
    .pizza-category-pill {
      display: inline-block;
      background: #fdf3ea;
      color: #e06000;
      font-size: 11px;
      font-weight: 600;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .pizza-ingredients-cell {
      color: #888;
      font-size: 12px;
      max-width: 200px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .stock-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-weight: 700;
      font-size: 13px;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .stock-badge.good  { background: #e8f8ee; color: #2e7d50; }
    .stock-badge.low   { background: #fff4e0; color: #c05000; }
    .stock-badge.out   { background: #fdecea; color: #c62828; }
    .tbl-action-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      border: none;
      cursor: pointer;
      font-size: 13px;
      transition: background 0.15s, transform 0.15s;
    }
    .tbl-action-btn:hover { transform: scale(1.12); }
    .tbl-btn-stock  { background: #e8f8ee; color: #2e7d50; }
    .tbl-btn-stock:hover  { background: #c8edda; }
    .tbl-btn-edit   { background: #e8f0ff; color: #1a56db; }
    .tbl-btn-edit:hover   { background: #c7d9ff; }
    .tbl-btn-delete { background: #fdecea; color: #c62828; }
    .tbl-btn-delete:hover { background: #f9c9c6; }
  </style>

  <div class="prod-header">
    <h2>🍕 Products</h2>
    <div class="prod-toolbar">
      <div class="prod-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchPizza" placeholder="Search pizza..." onkeyup="searchPizza()">
      </div>
      <select class="prod-filter" id="filterCategory" onchange="filterPizza()">
        <option value="all">All Categories</option>
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
      <button class="btn-add-pizza" onclick="showSection('addPizza')">
        <i class="fa-solid fa-plus"></i> Add New Pizza
      </button>
    </div>
  </div>

  <div class="pizza-table-wrap">
    <table class="pizza-table" id="pizzaTable">
      <thead>
        <tr>
          <th>Pizza Name</th>
          <th>Category</th>
          <th>Ingredients</th>
          <th>Stock</th>
          <th style="text-align:center;">Stock In</th>
          <th style="text-align:center;">Edit</th>
          <th style="text-align:center;">Delete</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $result = $conn->query("
            SELECT pizza_id, pizza_name, category, ingredients, stock, image_path
            FROM v_pizzas_full
        ");
        while ($row = $result->fetch_assoc()):
          $stock = (int)$row['stock'];
          if ($stock === 0)       { $badgeClass = 'out';  $badgeIcon = 'fa-circle-xmark'; }
          elseif ($stock < 10)    { $badgeClass = 'low';  $badgeIcon = 'fa-triangle-exclamation'; }
          else                    { $badgeClass = 'good'; $badgeIcon = 'fa-circle-check'; }
        ?>
        <tr>
          <td class="pizza-name-cell"><?= htmlspecialchars($row['pizza_name']) ?></td>
          <td><span class="pizza-category-pill"><?= htmlspecialchars($row['category']) ?></span></td>
          <td class="pizza-ingredients-cell" title="<?= htmlspecialchars($row['ingredients']) ?>">
            <?= htmlspecialchars($row['ingredients']) ?>
          </td>
          <td>
            <span class="stock-badge <?= $badgeClass ?>">
              <i class="fa-solid <?= $badgeIcon ?>" style="font-size:11px;"></i>
              <?= $stock ?>
            </span>
          </td>
          <td style="text-align:center;">
            <button class="tbl-action-btn tbl-btn-stock"
              onclick="openStockModal('<?= htmlspecialchars($row['pizza_name'], ENT_QUOTES) ?>','<?= htmlspecialchars($row['image_path'], ENT_QUOTES) ?>',<?= $stock ?>)"
              title="Add Stock">
              <i class="fa-solid fa-plus"></i>
            </button>
          </td>
          <td style="text-align:center;">
            <button class="tbl-action-btn tbl-btn-edit"
              onclick="openEditPizza(<?= $row['pizza_id'] ?>)"
              title="Edit">
              <i class="fa-solid fa-pen"></i>
            </button>
          </td>
          <td style="text-align:center;">
            <button class="tbl-action-btn tbl-btn-delete"
              onclick="confirmDelete(<?= $row['pizza_id'] ?>)"
              title="Delete">
              <i class="fa-solid fa-trash"></i>
            </button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- ✅ EDIT PIZZA SECTION -->
<div id="editPizza" class="section">

  <style>
    .pform-wrap {
      max-width: 680px;
    }
    .pform-wrap h2 {
      font-size: 22px;
      font-weight: 800;
      color: #222;
      margin-bottom: 24px;
    }
    .pform-card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      padding: 28px 32px;
      margin-bottom: 18px;
    }
    .pform-card-title {
      font-size: 12px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #f47c00;
      margin-bottom: 18px;
      padding-bottom: 10px;
      border-bottom: 1.5px solid #fde8d0;
    }
    .pform-img-row {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 10px;
    }
    .pform-img-preview {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 12px;
      border: 2px solid #f0e0ce;
      background: #fdf3ea;
    }
    .pform-file-label {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 9px 16px;
      background: #fdf3ea;
      color: #e06000;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      border: 1.5px dashed #f4a700;
      transition: background 0.15s;
    }
    .pform-file-label:hover { background: #fce5c0; }
    .pform-field {
      margin-bottom: 16px;
    }
    .pform-field label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: #888;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 6px;
    }
    .pform-field input,
    .pform-field select,
    .pform-field textarea {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      font-size: 13px;
      background: #fafafa;
      outline: none;
      box-sizing: border-box;
      transition: border 0.2s, box-shadow 0.2s;
      font-family: inherit;
    }
    .pform-field input:focus,
    .pform-field select:focus,
    .pform-field textarea:focus {
      border-color: #f47c00;
      box-shadow: 0 0 0 3px rgba(244,124,0,0.1);
      background: #fff;
    }
    .pform-field textarea { resize: vertical; min-height: 80px; }
    .price-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    .pform-actions {
      display: flex;
      gap: 10px;
      margin-top: 4px;
    }
    .btn-pform {
      padding: 11px 22px;
      border: none;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-pform:hover { transform: translateY(-1px); }
    .btn-pform-ghost {
      background: #f0f0f0;
      color: #555;
    }
    .btn-pform-ghost:hover { background: #e4e4e4; }
    .btn-pform-primary {
      background: linear-gradient(135deg, #f47c00, #e03e00);
      color: #fff;
      box-shadow: 0 3px 10px rgba(224,62,0,0.25);
    }
    .btn-pform-primary:hover { box-shadow: 0 5px 16px rgba(224,62,0,0.35); }
  </style>

  <div class="pform-wrap">
    <h2>✏️ Edit Pizza</h2>

    <!-- Image -->
    <div class="pform-card">
      <div class="pform-card-title"><i class="fa-solid fa-image"></i> Pizza Image</div>
      <div class="pform-img-row">
        <img id="editImagePreview" class="pform-img-preview">
        <div>
          <label class="pform-file-label" for="editImage">
            <i class="fa-solid fa-upload"></i> Choose New Image
          </label>
          <input type="file" id="editImage" style="display:none;"
            onchange="document.getElementById('editImagePreview').src=URL.createObjectURL(this.files[0])">
          <div style="font-size:11px; color:#bbb; margin-top:8px;">JPG, PNG, WEBP supported</div>
        </div>
      </div>
    </div>

    <!-- Basic Info -->
    <div class="pform-card">
      <div class="pform-card-title"><i class="fa-solid fa-circle-info"></i> Basic Info</div>
      <div class="pform-field">
        <label>Pizza Name</label>
        <input type="text" id="editName" placeholder="e.g. Pepperoni Supreme">
      </div>
      <div class="pform-field">
        <label>Category</label>
        <select id="editCategory">
          <?php
          $categories->data_seek(0);
          while ($cat = $categories->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($cat['category_name']) . "'>"
                 . htmlspecialchars($cat['category_name']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="pform-field">
        <label>Ingredients</label>
        <textarea id="editIngredients" placeholder="List ingredients..."></textarea>
      </div>
    </div>

    <!-- Prices -->
    <div class="pform-card">
      <div class="pform-card-title"><i class="fa-solid fa-tag"></i> Prices (₱)</div>
      <div class="price-grid">
        <div class="pform-field">
          <label>9" Quickmelt</label>
          <input type="number" id="p9q" placeholder="0.00">
        </div>
        <div class="pform-field">
          <label>11" Quickmelt</label>
          <input type="number" id="p11q" placeholder="0.00">
        </div>
        <div class="pform-field">
          <label>9" Mozzarella</label>
          <input type="number" id="p9m" placeholder="0.00">
        </div>
        <div class="pform-field">
          <label>11" Mozzarella</label>
          <input type="number" id="p11m" placeholder="0.00">
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="pform-actions">
      <button class="btn-pform btn-pform-ghost" onclick="revertEdit()">
        <i class="fa-solid fa-rotate-left"></i> Revert
      </button>
      <button class="btn-pform btn-pform-ghost" onclick="showSection('products')">
        <i class="fa-solid fa-arrow-left"></i> Back
      </button>
      <button class="btn-pform btn-pform-primary" onclick="saveEdit()">
        <i class="fa-solid fa-floppy-disk"></i> Save Changes
      </button>
    </div>
  </div>

</div>

<!-- ✅ ADD NEW PIZZA SECTION -->
<div id="addPizza" class="section">

  <div class="pform-wrap">
    <h2>➕ Add New Pizza</h2>

    <!-- Image -->
    <div class="pform-card">
      <div class="pform-card-title"><i class="fa-solid fa-image"></i> Pizza Image</div>
      <div class="pform-img-row">
        <img id="addImagePreview" src="menu/Other Flavors/Default.png" class="pform-img-preview">
        <div>
          <label class="pform-file-label" for="addImage">
            <i class="fa-solid fa-upload"></i> Choose Image
          </label>
          <input type="file" id="addImage" style="display:none;">
          <div style="font-size:11px; color:#bbb; margin-top:8px;">JPG, PNG, WEBP supported</div>
        </div>
      </div>
    </div>

    <!-- Basic Info -->
    <div class="pform-card">
      <div class="pform-card-title"><i class="fa-solid fa-circle-info"></i> Basic Info</div>
      <div class="pform-field">
        <label>Pizza Name</label>
        <input type="text" id="addName" placeholder="e.g. BBQ Chicken Melt">
      </div>
      <div class="pform-field">
        <label>Category</label>
        <select id="addCategory">
          <option value="" disabled selected>Choose a category...</option>
          <?php
          $categories2 = $conn->query("SELECT category_name FROM categories ORDER BY category_name ASC");
          while ($cat = $categories2->fetch_assoc()) {
            echo "<option value='" . htmlspecialchars($cat['category_name']) . "'>"
                 . htmlspecialchars($cat['category_name']) . "</option>";
          }
          ?>
        </select>
      </div>
      <div class="pform-field">
        <label>Create New Category <span style="color:#bbb;font-size:11px;font-weight:400;">(optional — fills automatically)</span></label>
        <input type="text" id="newCategory" placeholder="Type new category name...">
      </div>
      <div class="pform-field">
        <label>Ingredients</label>
        <textarea id="addIngredients" placeholder="List ingredients..."></textarea>
      </div>
    </div>

    <!-- Prices -->
    <div class="pform-card">
      <div class="pform-card-title"><i class="fa-solid fa-tag"></i> Prices (₱)</div>
      <div class="price-grid">
        <div class="pform-field">
          <label>9" Quickmelt</label>
          <input type="number" id="add_p9q" placeholder="0.00">
        </div>
        <div class="pform-field">
          <label>11" Quickmelt</label>
          <input type="number" id="add_p11q" placeholder="0.00">
        </div>
        <div class="pform-field">
          <label>9" Mozzarella</label>
          <input type="number" id="add_p9m" placeholder="0.00">
        </div>
        <div class="pform-field">
          <label>11" Mozzarella</label>
          <input type="number" id="add_p11m" placeholder="0.00">
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="pform-actions">
      <button class="btn-pform btn-pform-ghost" onclick="resetAddPizza()">
        <i class="fa-solid fa-eraser"></i> Reset
      </button>
      <button class="btn-pform btn-pform-ghost" onclick="showSection('products')">
        <i class="fa-solid fa-arrow-left"></i> Cancel
      </button>
      <button class="btn-pform btn-pform-primary" onclick="saveNewPizza()">
        <i class="fa-solid fa-floppy-disk"></i> Save New Pizza
      </button>
    </div>
  </div>

</div>



<!-- ✅ USERS -->
<div id="users" class="section">

  <style>
    /* ── USERS SECTION ── */
    .users-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 22px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .users-header h2 {
      font-size: 22px;
      font-weight: 800;
      color: #222;
      margin: 0;
    }
    .users-toolbar {
      display: flex;
      gap: 10px;
      align-items: center;
      flex-wrap: wrap;
    }
    .user-search-wrap {
      position: relative;
    }
    .user-search-wrap i {
      position: absolute;
      left: 11px;
      top: 50%;
      transform: translateY(-50%);
      color: #bbb;
      font-size: 13px;
      pointer-events: none;
    }
    .user-search-wrap input {
      padding: 9px 14px 9px 32px;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      font-size: 13px;
      outline: none;
      background: #fafafa;
      transition: border 0.2s, box-shadow 0.2s;
      width: 200px;
    }
    .user-search-wrap input:focus {
      border-color: #f47c00;
      box-shadow: 0 0 0 3px rgba(244,124,0,0.1);
      background: #fff;
    }
    .user-filter {
      padding: 9px 12px;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      font-size: 13px;
      background: #fafafa;
      outline: none;
      cursor: pointer;
      transition: border 0.2s;
    }
    .user-filter:focus { border-color: #f47c00; }
    .btn-add-user {
      padding: 9px 18px;
      background: linear-gradient(135deg, #f47c00, #e03e00);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 7px;
      box-shadow: 0 3px 10px rgba(224,62,0,0.25);
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-add-user:hover {
      transform: translateY(-1px);
      box-shadow: 0 5px 16px rgba(224,62,0,0.35);
    }

    /* ── USER TABLE ── */
    .user-table-wrap {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      overflow: hidden;
    }
    .user-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13px;
    }
    .user-table thead tr {
      background: #fdf3ea;
      border-bottom: 2px solid #f0e0ce;
    }
    .user-table th {
      padding: 13px 16px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      color: #b06020;
    }
    .user-table td {
      padding: 12px 16px;
      border-bottom: 1px solid #f5f5f5;
      color: #333;
      vertical-align: middle;
    }
    .user-table tbody tr:last-child td { border-bottom: none; }
    .user-table tbody tr:hover td { background: #fffbf7; }

    /* avatar */
    .user-avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 13px;
      color: #fff;
      flex-shrink: 0;
    }
    .user-name-cell {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .user-name-text { font-weight: 700; color: #222; }
    .user-id-text   { font-size: 11px; color: #bbb; }

    /* role pill */
    .role-pill {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      padding: 3px 10px;
      border-radius: 20px;
    }
    .role-Admin    { background:#fdecea; color:#c62828; }
    .role-Cashier  { background:#e8f0ff; color:#1a56db; }
    .role-Customer { background:#e8f8ee; color:#2e7d50; }
    .role-Driver   { background:#fff4e0; color:#c05000; }
    .role-default  { background:#f0f0f0; color:#666; }

    /* password mask */
    .pw-mask {
      font-family: monospace;
      letter-spacing: 2px;
      color: #bbb;
      font-size: 13px;
    }
  </style>

  <div class="users-header">
    <h2>👥 Users</h2>
    <div class="users-toolbar">
      <div class="user-search-wrap">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchUser" placeholder="Search user..." onkeyup="searchUser()">
      </div>
      <select class="user-filter" id="filterUser" onchange="filterUser()">
        <option value="all">All Roles</option>
        <option value="alphabetical">Alphabetical (A-Z)</option>
        <?php
        $roles = $conn->query("SELECT DISTINCT role FROM users");
        while ($r = $roles->fetch_assoc()) {
          echo "<option value='" . htmlspecialchars($r['role']) . "'>"
               . htmlspecialchars($r['role']) . "</option>";
        }
        ?>
      </select>
      <button class="btn-add-user" onclick="showSection('addUser')">
        <i class="fa-solid fa-user-plus"></i> Add User
      </button>
    </div>
  </div>

  <div class="user-table-wrap">
    <table class="user-table" id="userTable">
      <thead>
        <tr>
          <th>User</th>
          <th>Password</th>
          <th>Role</th>
          <th>Birth Date</th>
          <th>Gender</th>
          <th>Mobile</th>
          <th>Email</th>
          <th>Created</th>
          <th style="text-align:center;">Edit</th>
          <th style="text-align:center;">Del</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $avatarColors = ['#e03e00','#f47c00','#1a56db','#2e7d50','#7c3aed','#c05000','#c62828','#0369a1'];
        $users = $conn->query("SELECT * FROM users ORDER BY user_id ASC");
        while ($u = $users->fetch_assoc()):
          $initial   = strtoupper(substr($u['username'], 0, 1));
          $avatarBg  = $avatarColors[ord($initial) % count($avatarColors)];
          $roleCls   = 'role-' . (in_array($u['role'], ['Admin','Cashier','Customer','Driver']) ? $u['role'] : 'default');
          $pwMasked  = str_repeat('•', min(strlen($u['password']), 10));
          $createdFmt = date('M j, Y', strtotime($u['created_at']));
        ?>
        <tr>
          <!-- User (avatar + name + id) -->
          <td>
            <div class="user-name-cell">
              <div class="user-avatar" style="background:<?= $avatarBg ?>;"><?= $initial ?></div>
              <div>
                <div class="user-name-text"><?= htmlspecialchars($u['username']) ?></div>
                <div class="user-id-text">#<?= $u['user_id'] ?></div>
              </div>
            </div>
          </td>
          <!-- Password (masked) -->
          <td><span class="pw-mask"><?= $pwMasked ?></span></td>
          <!-- Role -->
          <td><span class="role-pill <?= $roleCls ?>"><?= htmlspecialchars($u['role']) ?></span></td>
          <!-- Birth Date -->
          <td style="color:#666;"><?= htmlspecialchars($u['birth_date']) ?></td>
          <!-- Gender -->
          <td style="color:#666;"><?= htmlspecialchars($u['gender']) ?></td>
          <!-- Mobile -->
          <td style="color:#666;"><?= htmlspecialchars($u['mobile_number']) ?></td>
          <!-- Email -->
          <td style="color:#666;"><?= htmlspecialchars($u['email']) ?></td>
          <!-- Created -->
          <td style="color:#999; font-size:12px;"><?= $createdFmt ?></td>
          <!-- Edit -->
          <td style="text-align:center;">
            <button class="tbl-action-btn tbl-btn-edit"
              onclick="openEditUser(<?= $u['user_id'] ?>,'<?= addslashes($u['username']) ?>','<?= addslashes($u['password']) ?>','<?= $u['role'] ?>','<?= $u['birth_date'] ?>','<?= $u['gender'] ?>','<?= addslashes($u['mobile_number']) ?>','<?= addslashes($u['email']) ?>')"
              title="Edit User">
              <i class="fa-solid fa-pen"></i>
            </button>
          </td>
          <!-- Delete -->
          <td style="text-align:center;">
            <?php if ($u['user_id'] == $_SESSION['user_id']): ?>
              <button class="tbl-action-btn" style="background:#f5f5f5; color:#ccc; cursor:not-allowed;" title="Cannot delete your own account" disabled>
                <i class="fa-solid fa-ban"></i>
              </button>
            <?php else: ?>
              <button class="tbl-action-btn tbl-btn-delete"
                onclick="confirmDeleteUser(<?= $u['user_id'] ?>)"
                title="Delete User">
                <i class="fa-solid fa-trash"></i>
              </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</div>

<!-- ✅ ADD USER SECTION -->
<div id="addUser" class="section">

  <style>
    /* ── USER FORM SHARED ── */
    .uform-wrap { max-width: 640px; }
    .uform-wrap h2 {
      font-size: 22px;
      font-weight: 800;
      color: #222;
      margin-bottom: 24px;
    }
    .uform-card {
      background: #fff;
      border-radius: 14px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.07);
      padding: 26px 30px;
      margin-bottom: 16px;
    }
    .uform-card-title {
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #f47c00;
      margin-bottom: 18px;
      padding-bottom: 10px;
      border-bottom: 1.5px solid #fde8d0;
      display: flex;
      align-items: center;
      gap: 7px;
    }
    .uform-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px;
    }
    .uform-field { margin-bottom: 16px; }
    .uform-field:last-child { margin-bottom: 0; }
    .uform-field label {
      display: block;
      font-size: 11px;
      font-weight: 700;
      color: #999;
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 6px;
    }
    .uform-field input,
    .uform-field select {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid #e8e8e8;
      border-radius: 8px;
      font-size: 13px;
      background: #fafafa;
      outline: none;
      box-sizing: border-box;
      transition: border 0.2s, box-shadow 0.2s;
      font-family: inherit;
    }
    .uform-field input:focus,
    .uform-field select:focus {
      border-color: #f47c00;
      box-shadow: 0 0 0 3px rgba(244,124,0,0.1);
      background: #fff;
    }
    .uform-pw-wrap { position: relative; }
    .uform-pw-wrap input { padding-right: 40px; }
    .uform-pw-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #bbb;
      font-size: 14px;
      transition: color 0.15s;
    }
    .uform-pw-toggle:hover { color: #f47c00; }

    /* radio chips */
    .chip-group {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-top: 2px;
    }
    .chip-group input[type="radio"] { display: none; }
    .chip-group label {
      padding: 7px 16px;
      border: 1.5px solid #e8e8e8;
      border-radius: 20px;
      font-size: 12px !important;
      font-weight: 600 !important;
      color: #666 !important;
      cursor: pointer;
      transition: all 0.15s;
      text-transform: none !important;
      letter-spacing: 0 !important;
      background: #fafafa;
    }
    .chip-group input[type="radio"]:checked + label {
      border-color: #f47c00;
      background: #fff4e8;
      color: #e06000 !important;
    }

    /* dob selects */
    .dob-row {
      display: grid;
      grid-template-columns: 2fr 1fr 1.5fr;
      gap: 10px;
    }

    /* form action buttons */
    .uform-actions {
      display: flex;
      gap: 10px;
      margin-top: 4px;
    }
    .btn-uform {
      padding: 11px 22px;
      border: none;
      border-radius: 9px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-uform:hover { transform: translateY(-1px); }
    .btn-uform-ghost {
      background: #f0f0f0;
      color: #555;
    }
    .btn-uform-ghost:hover { background: #e4e4e4; }
    .btn-uform-primary {
      background: linear-gradient(135deg, #f47c00, #e03e00);
      color: #fff;
      box-shadow: 0 3px 10px rgba(224,62,0,0.25);
    }
    .btn-uform-primary:hover { box-shadow: 0 5px 16px rgba(224,62,0,0.35); }
  </style>

  <div class="uform-wrap">
    <h2>➕ Add User</h2>

    <!-- Account -->
    <div class="uform-card">
      <div class="uform-card-title"><i class="fa-solid fa-lock"></i> Account Credentials</div>
      <div class="uform-row">
        <div class="uform-field">
          <label>Username</label>
          <input type="text" id="addUserName" placeholder="e.g. john_doe">
        </div>
        <div class="uform-field">
          <label>Password</label>
          <div class="uform-pw-wrap">
            <input type="password" id="addPassword" placeholder="Enter password">
            <i class="fa-solid fa-eye uform-pw-toggle" onclick="toggleAddPassword()"></i>
          </div>
        </div>
      </div>

      <div class="uform-field">
        <label>Role</label>
        <div class="chip-group">
          <input type="radio" name="addRole" id="ar-admin"    value="Admin">
          <label for="ar-admin">Admin</label>
          <input type="radio" name="addRole" id="ar-cashier"  value="Cashier">
          <label for="ar-cashier">Cashier</label>
          <input type="radio" name="addRole" id="ar-customer" value="Customer">
          <label for="ar-customer">Customer</label>
          <input type="radio" name="addRole" id="ar-driver"   value="Driver">
          <label for="ar-driver">Driver</label>
        </div>
      </div>
    </div>

    <!-- Personal Info -->
    <div class="uform-card">
      <div class="uform-card-title"><i class="fa-solid fa-user"></i> Personal Info</div>

      <div class="uform-field">
        <label>Birth Date</label>
        <div class="dob-row">
          <select id="dobMonth"></select>
          <select id="dobDay"></select>
          <select id="dobYear"></select>
        </div>
      </div>

      <div class="uform-field">
        <label>Gender</label>
        <div class="chip-group">
          <input type="radio" name="addGender" id="ag-male"   value="Male">
          <label for="ag-male">Male</label>
          <input type="radio" name="addGender" id="ag-female" value="Female">
          <label for="ag-female">Female</label>
          <input type="radio" name="addGender" id="ag-other"  value="Other">
          <label for="ag-other">Other</label>
        </div>
      </div>

      <div class="uform-row">
        <div class="uform-field">
          <label>Mobile Number</label>
          <input type="text" id="addMobile" placeholder="09XXXXXXXXX" oninput="restrictAddMobile(this)">
        </div>
        <div class="uform-field">
          <label>Email</label>
          <input type="text" id="addEmail" placeholder="email@example.com">
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="uform-actions">
      <button class="btn-uform btn-uform-ghost" onclick="clearAddUser()">
        <i class="fa-solid fa-eraser"></i> Clear
      </button>
      <button class="btn-uform btn-uform-ghost" onclick="showSection('users')">
        <i class="fa-solid fa-arrow-left"></i> Back
      </button>
      <button class="btn-uform btn-uform-primary" onclick="addUser()">
        <i class="fa-solid fa-user-plus"></i> Add User
      </button>
    </div>
  </div>

</div>

<!-- ✅ EDIT USER SECTION -->
<div id="editUser" class="section">

  <div class="uform-wrap">
    <h2>✏️ Edit User</h2>

    <!-- Account -->
    <div class="uform-card">
      <div class="uform-card-title"><i class="fa-solid fa-lock"></i> Account Credentials</div>
      <div class="uform-row">
        <div class="uform-field">
          <label>Username</label>
          <input type="text" id="editUserName" placeholder="Username">
        </div>
        <div class="uform-field">
          <label>Password</label>
          <div class="uform-pw-wrap">
            <input type="password" id="editPassword" placeholder="Password">
            <i class="fa-solid fa-eye uform-pw-toggle" onclick="toggleEditPassword()"></i>
          </div>
        </div>
      </div>

      <div class="uform-field">
        <label>Role</label>
        <div class="chip-group">
          <input type="radio" name="editRole" id="er-admin"    value="Admin">
          <label for="er-admin">Admin</label>
          <input type="radio" name="editRole" id="er-cashier"  value="Cashier">
          <label for="er-cashier">Cashier</label>
          <input type="radio" name="editRole" id="er-customer" value="Customer">
          <label for="er-customer">Customer</label>
          <input type="radio" name="editRole" id="er-driver"   value="Driver">
          <label for="er-driver">Driver</label>
        </div>
      </div>
    </div>

    <!-- Personal Info -->
    <div class="uform-card">
      <div class="uform-card-title"><i class="fa-solid fa-user"></i> Personal Info</div>

      <div class="uform-field">
        <label>Birth Date</label>
        <div class="dob-row">
          <select id="editMonth"></select>
          <select id="editDay"></select>
          <select id="editYear"></select>
        </div>
      </div>

      <div class="uform-field">
        <label>Gender</label>
        <div class="chip-group">
          <input type="radio" name="editGender" id="eg-male"   value="Male">
          <label for="eg-male">Male</label>
          <input type="radio" name="editGender" id="eg-female" value="Female">
          <label for="eg-female">Female</label>
          <input type="radio" name="editGender" id="eg-other"  value="Other">
          <label for="eg-other">Other</label>
        </div>
      </div>

      <div class="uform-row">
        <div class="uform-field">
          <label>Mobile Number</label>
          <input type="text" id="editMobile" placeholder="09XXXXXXXXX" oninput="restrictAddMobile(this)">
        </div>
        <div class="uform-field">
          <label>Email</label>
          <input type="text" id="editEmail" placeholder="email@example.com">
        </div>
      </div>
    </div>

    <!-- Buttons -->
    <div class="uform-actions">
      <button class="btn-uform btn-uform-ghost" onclick="revertUser()">
        <i class="fa-solid fa-rotate-left"></i> Revert
      </button>
      <button class="btn-uform btn-uform-ghost" onclick="showSection('users')">
        <i class="fa-solid fa-arrow-left"></i> Back
      </button>
      <button class="btn-uform btn-uform-primary" onclick="saveUserChanges()">
        <i class="fa-solid fa-floppy-disk"></i> Save Changes
      </button>
    </div>
  </div>

</div>

    <!-- ✅ SALES SECTION -->
    <div id="sales" class="section">
      <h2 style="margin-bottom:18px;">💰 Sales Overview</h2>

      <style>
        /* ── SALES SECTION STYLES ── */
        .sales-kpi-grid {
          display: grid;
          grid-template-columns: repeat(4, 1fr);
          gap: 16px;
          margin-bottom: 20px;
        }
        .sales-kpi {
          background: #fff;
          border-radius: 10px;
          padding: 18px 20px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .sales-kpi .kpi-label {
          font-size: 11px;
          color: #999;
          text-transform: uppercase;
          letter-spacing: 1px;
          margin-bottom: 6px;
        }
        .sales-kpi .kpi-value {
          font-size: 24px;
          font-weight: 800;
          color: #222;
          margin-bottom: 4px;
        }
        .sales-kpi .kpi-sub {
          font-size: 12px;
          color: #bbb;
        }
        .sales-row {
          display: flex;
          gap: 18px;
          margin-bottom: 20px;
        }
        .sales-card {
          background: #fff;
          border-radius: 10px;
          padding: 18px 20px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        .sales-card-title {
          font-size: 13px;
          font-weight: 700;
          color: #555;
          margin-bottom: 14px;
        }
        .status-pill {
          display: inline-block;
          font-size: 11px;
          padding: 2px 9px;
          border-radius: 20px;
          font-weight: 600;
          color: #fff;
        }
        .pill-completed  { background: #4CAF50; }
        .pill-pending    { background: #FF9800; }
        .pill-cancelled  { background: #f44336; }
        .pill-other      { background: #9e9e9e; }

        .sales-table {
          width: 100%;
          border-collapse: collapse;
          font-size: 13px;
        }
        .sales-table th {
          background: #f9f9f9;
          padding: 9px 10px;
          text-align: left;
          border-bottom: 2px solid #eee;
          font-weight: 700;
          color: #555;
        }
        .sales-table td {
          padding: 9px 10px;
          border-bottom: 1px solid #f0f0f0;
          color: #333;
        }
        .sales-table tr:hover td { background: #fafafa; }

        .breakdown-bar-wrap {
          display: flex;
          align-items: center;
          gap: 10px;
          margin-bottom: 10px;
        }
        .breakdown-bar-label {
          width: 80px;
          font-size: 12px;
          color: #555;
          flex-shrink: 0;
        }
        .breakdown-bar-bg {
          flex: 1;
          background: #f0f0f0;
          border-radius: 20px;
          height: 10px;
          overflow: hidden;
        }
        .breakdown-bar-fill {
          height: 100%;
          border-radius: 20px;
          transition: width 0.5s ease;
        }
        .breakdown-bar-val {
          font-size: 12px;
          color: #888;
          width: 70px;
          text-align: right;
          flex-shrink: 0;
        }

        /* Revenue share bar in best-selling table */
        .rev-bar {
          width: var(--bar-w, 4%);
          min-width: 4px;
          height: 8px;
          background: linear-gradient(90deg, #f4a700, #e03e00);
          border-radius: 4px;
        }
      </style>

      <!-- ── ROW 1: 4 KPI CARDS ──────────────────────── -->
      <div class="sales-kpi-grid">

        <div class="sales-kpi" style="border-top:4px solid #f4a700;">
          <div class="kpi-label">Total Revenue</div>
          <div class="kpi-value" style="color:#f4a700;">₱<?= number_format($totalRevenue, 2) ?></div>
          <div class="kpi-sub">Completed orders only</div>
        </div>

        <div class="sales-kpi" style="border-top:4px solid #2196F3;">
          <div class="kpi-label">Avg. Order Value</div>
          <div class="kpi-value" style="color:#2196F3;">₱<?= number_format($avgOrderVal, 2) ?></div>
          <div class="kpi-sub">Per completed order</div>
        </div>

        <div class="sales-kpi" style="border-top:4px solid #4CAF50;">
          <div class="kpi-label">Items Sold</div>
          <div class="kpi-value" style="color:#4CAF50;"><?= number_format($totalItemsSold) ?></div>
          <div class="kpi-sub">Total units (completed)</div>
        </div>

        <div class="sales-kpi" style="border-top:4px solid #e03e00;">
          <div class="kpi-label">Today's Sales</div>
          <div class="kpi-value" style="color:#e03e00;">₱<?= number_format($dailySales, 2) ?></div>
          <div class="kpi-sub"><?= date('F j, Y') ?></div>
        </div>

      </div>

      <!-- ── ROW 2: ORDER STATUS + PAYMENT METHOD ──────── -->
      <div class="sales-row">

        <!-- Order Status Breakdown -->
        <div class="sales-card" style="flex:1;">
          <div class="sales-card-title">📋 Order Status Breakdown</div>

          <?php
          $allStatuses = ['completed','pending','cancelled'];
          $totalAllOrders = array_sum(array_column($salesStatusBreakdown, 'cnt')) ?: 1;
          $statusColors   = ['completed'=>'#4CAF50','pending'=>'#FF9800','cancelled'=>'#f44336'];
          foreach ($allStatuses as $st):
            $cnt = isset($salesStatusBreakdown[$st]) ? (int)$salesStatusBreakdown[$st]['cnt'] : 0;
            $rev = isset($salesStatusBreakdown[$st]) ? (float)$salesStatusBreakdown[$st]['revenue'] : 0;
            $pct = round(($cnt / $totalAllOrders) * 100);
            $col = $statusColors[$st] ?? '#9e9e9e';
          ?>
          <div class="breakdown-bar-wrap">
            <div class="breakdown-bar-label" style="text-transform:capitalize;"><?= $st ?></div>
            <div class="breakdown-bar-bg">
              <div class="breakdown-bar-fill" style="width:<?= $pct ?>%; background:<?= $col ?>;"></div>
            </div>
            <div class="breakdown-bar-val"><?= $cnt ?> (<?= $pct ?>%)</div>
          </div>
          <div style="font-size:11px;color:#aaa;margin-bottom:12px;margin-left:90px;">
            <?php if ($rev > 0): ?>Revenue: ₱<?= number_format($rev,2) ?><?php endif; ?>
          </div>
          <?php endforeach; ?>

          <div style="margin-top:10px; padding-top:10px; border-top:1px solid #f0f0f0; font-size:12px; color:#888;">
            Total orders: <strong style="color:#333;"><?= number_format($totalAllOrders) ?></strong>
          </div>
        </div>

        <!-- Payment Method -->
        <div class="sales-card" style="flex:1;">
          <div class="sales-card-title">💳 Payment Method (Completed)</div>

          <?php
          $totalPayRev = array_sum(array_column($paymentBreakdown, 'revenue')) ?: 1;
          $payColors   = ['CASH'=>'#4CAF50','ONLINE'=>'#2196F3','CARD'=>'#9C27B0'];
          foreach ($paymentBreakdown as $p):
            $pct = round(($p['revenue'] / $totalPayRev) * 100);
            $col = $payColors[strtoupper($p['payment_method'])] ?? '#f4a700';
          ?>
          <div class="breakdown-bar-wrap">
            <div class="breakdown-bar-label"><?= htmlspecialchars($p['payment_method']) ?></div>
            <div class="breakdown-bar-bg">
              <div class="breakdown-bar-fill" style="width:<?= $pct ?>%; background:<?= $col ?>;"></div>
            </div>
            <div class="breakdown-bar-val">₱<?= number_format($p['revenue'],0) ?></div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($paymentBreakdown)): ?>
            <p style="color:#bbb; font-size:13px;">No completed orders yet.</p>
          <?php endif; ?>

          <div style="margin-top:14px;">
            <div class="sales-card-title" style="margin-top:4px;">🛵 Order Type (Completed)</div>
            <?php
            $totalTypeRev = array_sum(array_column($orderTypeBreakdown, 'revenue')) ?: 1;
            $typeColors   = ['DELIVERY'=>'#e03e00','PICK-UP'=>'#f4a700'];
            foreach ($orderTypeBreakdown as $t):
              $pct = round(($t['revenue'] / $totalTypeRev) * 100);
              $col = $typeColors[strtoupper($t['order_type'])] ?? '#9e9e9e';
            ?>
            <div class="breakdown-bar-wrap">
              <div class="breakdown-bar-label"><?= htmlspecialchars($t['order_type']) ?></div>
              <div class="breakdown-bar-bg">
                <div class="breakdown-bar-fill" style="width:<?= $pct ?>%; background:<?= $col ?>;"></div>
              </div>
              <div class="breakdown-bar-val"><?= $t['cnt'] ?> orders</div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($orderTypeBreakdown)): ?>
              <p style="color:#bbb; font-size:13px;">No completed orders yet.</p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Size & Cheese Preference -->
        <div class="sales-card" style="flex:1;">
          <div class="sales-card-title">🍕 Size & Cheese Preference</div>

          <div style="font-size:12px; font-weight:600; color:#777; margin-bottom:8px;">By Size</div>
          <?php
          $totalSizeUnits = array_sum(array_column($sizeBreakdown, 'units')) ?: 1;
          $sizeColors = ["9\""=>'#f47c00',"9"=>'#f47c00',"11\""=>'#e03e00',"11"=>'#e03e00'];
          foreach ($sizeBreakdown as $s):
            $pct = round(($s['units'] / $totalSizeUnits) * 100);
            $col = $sizeColors[$s['size']] ?? '#f4a700';
          ?>
          <div class="breakdown-bar-wrap">
            <div class="breakdown-bar-label"><?= htmlspecialchars($s['size']) ?>"</div>
            <div class="breakdown-bar-bg">
              <div class="breakdown-bar-fill" style="width:<?= $pct ?>%; background:<?= $col ?>;"></div>
            </div>
            <div class="breakdown-bar-val"><?= $s['units'] ?> pcs</div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($sizeBreakdown)): ?>
            <p style="color:#bbb;font-size:13px;">No data.</p>
          <?php endif; ?>

          <div style="font-size:12px; font-weight:600; color:#777; margin:12px 0 8px;">By Cheese</div>
          <?php
          $totalCheeseUnits = array_sum(array_column($cheeseBreakdown, 'units')) ?: 1;
          $cheeseColors = ['Quickmelt'=>'#4CAF50','Mozzarella'=>'#2196F3'];
          foreach ($cheeseBreakdown as $c):
            $pct = round(($c['units'] / $totalCheeseUnits) * 100);
            $col = $cheeseColors[$c['cheese']] ?? '#9e9e9e';
          ?>
          <div class="breakdown-bar-wrap">
            <div class="breakdown-bar-label"><?= htmlspecialchars($c['cheese']) ?></div>
            <div class="breakdown-bar-bg">
              <div class="breakdown-bar-fill" style="width:<?= $pct ?>%; background:<?= $col ?>;"></div>
            </div>
            <div class="breakdown-bar-val"><?= $c['units'] ?> pcs</div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($cheeseBreakdown)): ?>
            <p style="color:#bbb;font-size:13px;">No data.</p>
          <?php endif; ?>
        </div>

      </div>

      <!-- ── ROW 3: WEEKLY TREND + TOP BY REVENUE ──────── -->
      <div class="sales-row">

        <div class="sales-card" style="flex:1.3; min-width:0;">
          <div class="sales-card-title">📅 Weekly Revenue (Last 7 Days)</div>
          <canvas id="weeklyRevenueChart" height="180"></canvas>
          <?php if (empty($weeklyLabels)): ?>
            <p style="color:#bbb;font-size:13px;">No completed orders in the last 7 days.</p>
          <?php endif; ?>
        </div>

        <div class="sales-card" style="flex:1; min-width:0;">
          <div class="sales-card-title">🏆 Top 10 Pizzas by Revenue</div>
          <canvas id="topRevenueChart" height="180"></canvas>
          <?php if (empty($topRevLabels)): ?>
            <p style="color:#bbb;font-size:13px;">No sales data yet.</p>
          <?php endif; ?>
        </div>

      </div>

      <!-- ── ROW 4: TOP PIZZAS TABLE ───────────────────── -->
      <div class="sales-card" style="margin-bottom:20px;">
        <div class="sales-card-title">📊 Best-Selling Pizzas — Revenue Breakdown</div>

        <?php if (!$topByRevenueTable || $topByRevenueTable->num_rows === 0): ?>
          <p style="color:#bbb;font-size:13px;">No sales data yet.</p>
        <?php else: ?>
        <table class="sales-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Pizza</th>
              <th>Units Sold</th>
              <th>Revenue</th>
              <th>Revenue Share</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $rank = 1;
            $totalTopRev = array_sum($topRevData) ?: 1;
            while ($row = $topByRevenueTable->fetch_assoc()):
              $share = round(($row['revenue'] / $totalTopRev) * 100);
              $medalColor = $rank === 1 ? '#f4a700' : ($rank === 2 ? '#aaa' : ($rank === 3 ? '#cd7f32' : '#ddd'));
            ?>
            <tr>
              <td>
                <span style="
                  display:inline-block;
                  width:22px; height:22px;
                  background:<?= $medalColor ?>;
                  color:<?= $rank <= 3 ? '#fff' : '#888' ?>;
                  border-radius:50%;
                  text-align:center;
                  line-height:22px;
                  font-size:11px;
                  font-weight:700;
                "><?= $rank ?></span>
              </td>
              <td style="font-weight:600;"><?= htmlspecialchars($row['pizza_name']) ?></td>
              <td><?= number_format($row['units']) ?> pcs</td>
              <td style="font-weight:700; color:#e03e00;">₱<?= number_format($row['revenue'], 2) ?></td>
              <td>
                <div style="display:flex; align-items:center; gap:8px;">
                  <div class="rev-bar" style="--bar-w:<?= max($share, 4) ?>%"></div>
                  <span style="font-size:12px;color:#999;"><?= $share ?>%</span>
                </div>
              </td>
            </tr>
            <?php $rank++; endwhile; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

      <!-- ── ROW 5: RECENT ORDERS TABLE ────────────────── -->
      <div class="sales-card">
        <div class="sales-card-title">🧾 Recent Orders (Last 15)</div>

        <?php if (!$recentOrders || $recentOrders->num_rows === 0): ?>
          <p style="color:#bbb;font-size:13px;">No orders found.</p>
        <?php else: ?>
        <table class="sales-table">
          <thead>
            <tr>
              <th>Order #</th>
              <th>Customer</th>
              <th>Items</th>
              <th>Amount</th>
              <th>Type</th>
              <th>Payment</th>
              <th>Status</th>
              <th>Date</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($o = $recentOrders->fetch_assoc()):
              $statusClass = 'pill-other';
              if ($o['status'] === 'completed') $statusClass = 'pill-completed';
              elseif ($o['status'] === 'pending') $statusClass = 'pill-pending';
              elseif ($o['status'] === 'cancelled') $statusClass = 'pill-cancelled';
            ?>
            <tr>
              <td style="font-weight:700; color:#f47c00;">#<?= $o['order_id'] ?></td>
              <td><?= htmlspecialchars($o['customer_name']) ?></td>
              <td style="text-align:center;"><?= $o['item_count'] ?></td>
              <td style="font-weight:600;">₱<?= number_format($o['total_amount'], 2) ?></td>
              <td><?= htmlspecialchars($o['order_type']) ?></td>
              <td><?= htmlspecialchars($o['payment_method']) ?></td>
              <td>
                <span class="status-pill <?= $statusClass ?>">
                  <?= ucfirst($o['status']) ?>
                </span>
              </td>
              <td style="color:#999; font-size:12px;">
                <?= date('M j, Y g:i A', strtotime($o['created_at'])) ?>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>

    </div><!-- end #sales -->

  </div>

</div>

<!-- ✅ SWITCH SECTIONS -->
<script>
function showSection(section) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
  document.getElementById(section).classList.add('active');
}

function setNav(btn, section) {
  // highlight active sidebar button
  document.querySelectorAll('.sidebar-nav-btn').forEach(b => b.classList.remove('active-nav'));
  btn.classList.add('active-nav');
  showSection(section);
}

window.onload = function () {
  const saved = sessionStorage.getItem("activeSection");
  if (saved) {
    showSection(saved);
    sessionStorage.removeItem("activeSection");
    // highlight matching sidebar button
    document.querySelectorAll('.sidebar-nav-btn').forEach(btn => {
      const section = btn.getAttribute('onclick').match(/'([^']+)'/)?.[1];
      if (section === saved) btn.classList.add('active-nav');
      else btn.classList.remove('active-nav');
    });
  }
  populateAddUserDOB();
};

</script>

<div id="stockModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9999; align-items:center; justify-content:center;">
  <div style="
    background:#fff;
    border-radius:18px;
    padding:32px;
    width:340px;
    box-shadow:0 20px 60px rgba(0,0,0,0.2);
    text-align:center;
    position:relative;
  ">
    <button onclick="closeStockModal()" style="
      position:absolute; top:14px; right:16px;
      background:none; border:none; font-size:18px;
      color:#bbb; cursor:pointer; line-height:1;
    ">&times;</button>

    <img id="modalPizzaImg" style="
      width:110px; height:110px; object-fit:cover;
      border-radius:14px; border:2px solid #f0e0ce;
      margin-bottom:14px;
    ">

    <div style="font-size:17px; font-weight:800; color:#222; margin-bottom:6px;"
         id="modalPizzaName"></div>

    <div style="font-size:13px; color:#888; margin-bottom:20px;">
      Current Stock: <span id="modalStock" style="font-weight:700;"></span>
    </div>

    <div style="text-align:left; margin-bottom:20px;">
      <label style="font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:0.6px; display:block; margin-bottom:7px;">
        Amount to Add
      </label>
      <input type="number" id="stockInput" min="1" placeholder="e.g. 20" style="
        width:100%; padding:10px 14px; border:1.5px solid #e8e8e8;
        border-radius:9px; font-size:14px; font-weight:600;
        outline:none; box-sizing:border-box; background:#fafafa;
        transition: border 0.2s, box-shadow 0.2s;
      " onfocus="this.style.borderColor='#f47c00'; this.style.boxShadow='0 0 0 3px rgba(244,124,0,0.1)'"
         onblur="this.style.borderColor='#e8e8e8'; this.style.boxShadow='none'">
    </div>

    <div style="display:flex; gap:10px;">
      <button onclick="closeStockModal()" style="
        flex:1; padding:11px; border:none; border-radius:9px;
        background:#f0f0f0; color:#555; font-size:13px;
        font-weight:700; cursor:pointer;
      ">Cancel</button>
      <button onclick="addStock()" style="
        flex:1; padding:11px; border:none; border-radius:9px;
        background:linear-gradient(135deg,#f47c00,#e03e00);
        color:#fff; font-size:13px; font-weight:700; cursor:pointer;
        box-shadow:0 3px 10px rgba(224,62,0,0.25);
      ">Add Stock</button>
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
  stockSpan.style.color = (stock < 10) ? "#c62828" : "#2e7d50";

  document.getElementById("stockModal").style.display = "flex"; // ← was "block"
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

  const roleEl   = document.querySelector('input[name="addRole"]:checked');
  const genderEl = document.querySelector('input[name="addGender"]:checked');

  const month = document.getElementById("dobMonth").value;
  const day   = document.getElementById("dobDay").value;
  const year  = document.getElementById("dobYear").value;

  const mobile = document.getElementById("addMobile").value.trim();
  const email  = document.getElementById("addEmail").value.trim();

  // ✅ VALIDATION
  if (!username || !password || !roleEl || !genderEl || !month || !day || !year || !mobile || !email) {
    alert("Please complete all fields.");
    return;
  }

  // ✅ FORMAT DATE
  const birthdate = year + "-" + String(month).padStart(2, '0') + "-" + String(day).padStart(2, '0');

  // ✅ SEND DATA
  fetch("add_user.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body:
      "username="   + encodeURIComponent(username)   +
      "&password="  + encodeURIComponent(password)   +
      "&role="      + encodeURIComponent(roleEl.value) +
      "&birth_date="+ encodeURIComponent(birthdate)  +
      "&gender="    + encodeURIComponent(genderEl.value) +
      "&mobile="    + encodeURIComponent(mobile)     +
      "&email="     + encodeURIComponent(email)
  })
  .then(res => res.text())
  .then(response => {
    if (response.trim() !== "success") {
      alert("Error adding user:\n" + response);
      return;
    }
    sessionStorage.setItem("activeSection", "users");
    location.reload();
  })
  .catch(err => {
    alert("Network error: " + err);
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

  const username = document.getElementById("editUserName").value.trim();
  const password = document.getElementById("editPassword").value.trim();
  const roleEl   = document.querySelector('input[name="editRole"]:checked');
  const genderEl = document.querySelector('input[name="editGender"]:checked');
  const y = document.getElementById("editYear").value;
  const m = document.getElementById("editMonth").value;
  const d = document.getElementById("editDay").value;
  const mobile = document.getElementById("editMobile").value.trim();
  const email  = document.getElementById("editEmail").value.trim();

  // ✅ VALIDATION
  if (!username || !password || !roleEl || !genderEl || !m || !d || !y || !mobile || !email) {
    alert("Please complete all fields.");
    return;
  }

  const formData = new FormData();
  formData.append("user_id",  currentUserId);
  formData.append("username", username);
  formData.append("password", password);
  formData.append("role",     roleEl.value);
  formData.append("birth",    `${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`);
  formData.append("gender",   genderEl.value);
  formData.append("mobile",   mobile);
  formData.append("email",    email);

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

<!-- ✅ DASHBOARD CHARTS (Top Selling + Monthly Trend) -->
<script>
(function () {

  const topLabels   = <?= json_encode($chartLabels) ?>;
  const topData     = <?= json_encode($chartData) ?>;
  const trendLabels = <?= json_encode($trendLabels) ?>;
  const trendData   = <?= json_encode($trendData) ?>;

  const barColors = [
    '#f4a700','#f47c00','#e03e00','#c0392b','#e67e22',
    '#d35400','#e74c3c','#f39c12','#ca6f1e','#a93226'
  ];

  // ── TOP SELLING PIZZAS (Horizontal Bar) ──
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
            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.x} units sold` } }
          },
          scales: {
            x: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 11 } }, grid: { color: '#f0f0f0' } },
            y: { ticks: { font: { size: 11 } }, grid: { display: false } }
          }
        }
      });
    }
  }

  // ── MONTHLY SALES TREND (Line) ──
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
            tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.y.toLocaleString('en-PH', { minimumFractionDigits: 2 })}` } }
          },
          scales: {
            x: { ticks: { font: { size: 11 } }, grid: { color: '#f0f0f0' } },
            y: {
              beginAtZero: true,
              ticks: { font: { size: 11 }, callback: v => '₱' + v.toLocaleString() },
              grid: { color: '#f0f0f0' }
            }
          }
        }
      });
    }
  }

})();
</script>

<!-- ✅ SALES SECTION CHARTS (Weekly Revenue + Top by Revenue) -->
<script>
(function () {

  const weeklyLabels = <?= json_encode($weeklyLabels) ?>;
  const weeklyData   = <?= json_encode($weeklyData) ?>;
  const topRevLabels = <?= json_encode($topRevLabels) ?>;
  const topRevData   = <?= json_encode($topRevData) ?>;

  const orangePalette = [
    '#f4a700','#f47c00','#e03e00','#c0392b','#e67e22',
    '#d35400','#e74c3c','#f39c12','#ca6f1e','#a93226'
  ];

  // ── Weekly Revenue Bar ──
  const weeklyCtx = document.getElementById('weeklyRevenueChart');
  if (weeklyCtx) {
    if (weeklyLabels.length === 0) {
      weeklyCtx.style.display = 'none';
    } else {
      new Chart(weeklyCtx, {
        type: 'bar',
        data: {
          labels: weeklyLabels,
          datasets: [{
            label: 'Revenue (₱)',
            data: weeklyData,
            backgroundColor: 'rgba(244,124,0,0.75)',
            borderColor: '#f47c00',
            borderWidth: 1,
            borderRadius: 5,
            borderSkipped: false
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.y.toLocaleString('en-PH',{minimumFractionDigits:2})}` } }
          },
          scales: {
            x: { ticks: { font: { size: 11 } }, grid: { display: false } },
            y: {
              beginAtZero: true,
              ticks: { font: { size: 11 }, callback: v => '₱' + v.toLocaleString() },
              grid: { color: '#f0f0f0' }
            }
          }
        }
      });
    }
  }

  // ── Top Pizzas by Revenue Horizontal Bar ──
  const topRevCtx = document.getElementById('topRevenueChart');
  if (topRevCtx) {
    if (topRevLabels.length === 0) {
      topRevCtx.style.display = 'none';
    } else {
      new Chart(topRevCtx, {
        type: 'bar',
        data: {
          labels: topRevLabels,
          datasets: [{
            label: 'Revenue (₱)',
            data: topRevData,
            backgroundColor: orangePalette.slice(0, topRevLabels.length),
            borderRadius: 4,
            borderSkipped: false
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => ` ₱${ctx.parsed.x.toLocaleString('en-PH',{minimumFractionDigits:2})}` } }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: { font: { size: 11 }, callback: v => '₱'+v.toLocaleString() },
              grid: { color: '#f0f0f0' }
            },
            y: { ticks: { font: { size: 11 } }, grid: { display: false } }
          }
        }
      });
    }
  }

})();
</script>

</body>
</html>