<?php
session_start();
include "db_connect.php";

// Only logged-in customers can view this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Fetch this customer's orders with computed total (no view)
$ordersResult = $conn->query("
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at,
           (SELECT COALESCE(SUM(oi.quantity * pv.price),0)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id) AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN branches b ON o.branch_id = b.branch_id
    WHERE o.user_id = $user_id
    ORDER BY o.created_at DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | My Orders</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background: var(--bg); }

    .orders-wrapper {
      max-width: 860px;
      margin: 36px auto;
      padding: 0 16px 60px;
    }

    /* ── Page header ── */
    .orders-page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 12px;
    }

    .orders-page-header h1 {
      font-family: var(--font-main);
      font-size: 22px;
      font-weight: 900;
      color: var(--text-dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .orders-page-header a {
      font-size: 13px;
      font-weight: 700;
      color: var(--orange);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: gap 0.15s;
    }
    .orders-page-header a:hover { gap: 9px; }

    /* ── Order card ── */
    .order-card {
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      border: 1.5px solid var(--border);
      margin-bottom: 16px;
      overflow: hidden;
      transition: box-shadow 0.18s, transform 0.18s;
    }
    .order-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }

    /* Card header row */
    .order-card-head {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 14px 20px;
      background: #FAFAFA;
      border-bottom: 1px solid var(--border);
      flex-wrap: wrap;
      gap: 8px;
    }

    .order-id {
      font-family: var(--font-main);
      font-weight: 900;
      font-size: 14px;
      color: var(--orange);
    }

    .order-date {
      font-size: 12px;
      color: var(--text-light);
    }

    /* Status pill */
    .status-pill {
      display: inline-block;
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.6px;
      text-transform: uppercase;
      padding: 3px 12px;
      border-radius: var(--radius-pill);
    }
    .pill-pending       { background: #FFF3E0; color: #E65100; border: 1px solid #FFE0B2; }
    .pill-completed     { background: #E8F5E9; color: #2E7D32; border: 1px solid #A5D6A7; }
    .pill-cancelled     { background: #FFEBEE; color: #C62828; border: 1px solid #FFCDD2; }
    .pill-delivered     { background: #E3F2FD; color: #1565C0; border: 1px solid #90CAF9; }
    .pill-out_for_delivery { background: #F3E5F5; color: #6A1B9A; border: 1px solid #CE93D8; }
    .pill-other         { background: #F5F5F5; color: #666;    border: 1px solid #E0E0E0; }

    /* Card body */
    .order-card-body {
      padding: 16px 20px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px 24px;
      font-size: 13px;
    }

    @media (max-width: 560px) {
      .order-card-body { grid-template-columns: 1fr; }
    }

    .order-meta-item {
      display: flex;
      flex-direction: column;
      gap: 2px;
    }
    .order-meta-label {
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      color: var(--text-light);
    }
    .order-meta-value {
      font-size: 13px;
      color: var(--text-dark);
      font-weight: 500;
    }
    .order-total-value {
      font-size: 16px;
      font-weight: 900;
      color: var(--orange);
      font-family: var(--font-main);
    }

    /* Items accordion */
    .order-items-toggle {
      width: 100%;
      background: none;
      border: none;
      border-top: 1px solid var(--border);
      padding: 10px 20px;
      text-align: left;
      font-family: var(--font-main);
      font-size: 12px;
      font-weight: 700;
      color: var(--text-mid);
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: background 0.15s, color 0.15s;
      margin-top: 0;
    }
    .order-items-toggle:hover {
      background: #FFFDE7;
      color: var(--orange);
    }
    .order-items-toggle i {
      transition: transform 0.2s;
      font-size: 11px;
    }
    .order-items-toggle.open i { transform: rotate(180deg); }

    .order-items-body {
      display: none;
      padding: 0 20px 14px;
      border-top: 1px dashed var(--border);
    }
    .order-items-body.open { display: block; }

    .order-item-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px solid #F5F5F5;
      font-size: 13px;
      gap: 12px;
    }
    .order-item-row:last-child { border-bottom: none; }

    .order-item-name {
      font-weight: 600;
      color: var(--text-dark);
      flex: 1;
    }
    .order-item-meta {
      font-size: 11px;
      color: var(--text-light);
    }
    .order-item-price {
      font-weight: 700;
      color: var(--text-dark);
      white-space: nowrap;
    }

    /* Empty state */
    .orders-empty {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-light);
    }
    .orders-empty i {
      font-size: 48px;
      color: #E0E0E0;
      margin-bottom: 16px;
      display: block;
    }
    .orders-empty p {
      font-size: 15px;
      margin-bottom: 20px;
    }
    .orders-empty a {
      display: inline-block;
      padding: 11px 28px;
      background: var(--orange);
      color: #fff;
      border-radius: var(--radius-md);
      font-family: var(--font-main);
      font-weight: 800;
      font-size: 13px;
      text-decoration: none;
      box-shadow: 0 3px 10px rgba(255,107,0,0.3);
      transition: background 0.18s, transform 0.15s;
    }
    .orders-empty a:hover {
      background: var(--orange-light);
      transform: translateY(-2px);
    }
  </style>
</head>

<body>

<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="index.php">HOME</a>
    <a href="my_orders.php" style="background:rgba(0,0,0,0.08); border-radius:var(--radius-pill); padding:8px 16px;">
      <i class="fa-solid fa-receipt" style="margin-right:5px;"></i>My Orders
    </a>
    <a href="profile_customer.php"><?= htmlspecialchars($_SESSION['username']); ?></a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
  </div>
</header>

<div class="orders-wrapper">

  <div class="orders-page-header">
    <h1><i class="fa-solid fa-receipt" style="color:var(--orange);"></i> My Orders</h1>
    <a href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to Menu</a>
  </div>

  <?php if ($ordersResult->num_rows === 0): ?>

    <div class="orders-empty">
      <i class="fa-solid fa-box-open"></i>
      <p>You haven't placed any orders yet.</p>
      <a href="index.php">Order Now</a>
    </div>

  <?php else: ?>

    <?php while ($order = $ordersResult->fetch_assoc()):

      // Status pill class
      $statusClass = match($order['status']) {
        'pending'           => 'pill-pending',
        'completed'         => 'pill-completed',
        'cancelled'         => 'pill-cancelled',
        'delivered'         => 'pill-delivered',
        'out_for_delivery'  => 'pill-out_for_delivery',
        default             => 'pill-other',
      };

      $statusLabel = match($order['status']) {
        'pending'           => 'Pending',
        'completed'         => 'Completed',
        'cancelled'         => 'Cancelled',
        'delivered'         => 'Delivered',
        'out_for_delivery'  => 'Out for Delivery',
        default             => ucfirst($order['status']),
      };

      // Fetch items for this order
      $itemsResult = $conn->query("
          SELECT p.pizza_name, pv.size, pv.cheese, pv.price,
                 oi.quantity, (oi.quantity * pv.price) AS total
          FROM order_items oi
          JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
          JOIN pizzas         p  ON pv.pizza_id   = p.pizza_id
          WHERE oi.order_id = {$order['order_id']}
      ");

      $branchDisplay = $order['branch_name']
          ? htmlspecialchars($order['branch_name'] . ', ' . $order['branch_location'])
          : '—';

      $addressDisplay = ($order['address'] && $order['address'] !== '0')
          ? htmlspecialchars($order['address'])
          : '—';
    ?>

    <div class="order-card">

      <!-- Card Header -->
      <div class="order-card-head">
        <div>
          <span class="order-id"># <?= $order['order_id'] ?></span>
          <span class="order-date" style="margin-left:10px;">
            <?= date('M j, Y · g:i A', strtotime($order['created_at'])) ?>
          </span>
        </div>
        <span class="status-pill <?= $statusClass ?>"><?= $statusLabel ?></span>
      </div>

      <!-- Card Body -->
      <div class="order-card-body">

        <div class="order-meta-item">
          <span class="order-meta-label">Branch</span>
          <span class="order-meta-value"><?= $branchDisplay ?></span>
        </div>

        <div class="order-meta-item">
          <span class="order-meta-label">Order Type</span>
          <span class="order-meta-value"><?= htmlspecialchars($order['order_type']) ?></span>
        </div>

        <?php if ($addressDisplay !== '—'): ?>
        <div class="order-meta-item">
          <span class="order-meta-label">Address</span>
          <span class="order-meta-value"><?= $addressDisplay ?></span>
        </div>
        <?php endif; ?>

        <div class="order-meta-item">
          <span class="order-meta-label">Payment</span>
          <span class="order-meta-value"><?= htmlspecialchars($order['payment_method']) ?></span>
        </div>

        <div class="order-meta-item">
          <span class="order-meta-label">Total</span>
          <span class="order-total-value">₱<?= number_format($order['total_amount'], 2) ?></span>
        </div>

      </div>

      <!-- Items accordion -->
      <button class="order-items-toggle" onclick="toggleItems(this)">
        <i class="fa-solid fa-chevron-down"></i>
        View Items (<?= $itemsResult->num_rows ?>)
      </button>

      <div class="order-items-body">
        <?php while ($item = $itemsResult->fetch_assoc()): ?>
        <div class="order-item-row">
          <div>
            <div class="order-item-name"><?= htmlspecialchars($item['pizza_name']) ?></div>
            <div class="order-item-meta">
              <?= $item['size'] ?>" · <?= htmlspecialchars($item['cheese']) ?> · qty <?= $item['quantity'] ?>
            </div>
          </div>
          <div class="order-item-price">₱<?= number_format($item['total'], 2) ?></div>
        </div>
        <?php endwhile; ?>
      </div>

    </div>

    <?php endwhile; ?>

  <?php endif; ?>

</div>

<script>
function toggleItems(btn) {
  const body = btn.nextElementSibling;
  const isOpen = body.classList.toggle('open');
  btn.classList.toggle('open', isOpen);
  btn.querySelector('span') && (btn.querySelector('span').textContent =
    isOpen ? 'Hide Items' : `View Items (${body.querySelectorAll('.order-item-row').length})`);
}
</script>

</body>
</html>
