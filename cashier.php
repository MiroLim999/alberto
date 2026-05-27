<?php
session_start();
include "db_connect.php";

// Fetch pending orders
$ordersQuery = "
    SELECT
        o.order_id, o.branch_id, o.address, o.order_type,
        o.payment_method, o.status, o.created_at,
        COALESCE(u.username,      oc.customer_name) AS customer_name,
        COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
        COALESCE(u.email,         oc.email)         AS email,
        (SELECT SUM(oi.quantity * pv.price)
         FROM order_items oi
         JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
         WHERE oi.order_id = o.order_id)            AS total_amount
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    WHERE o.status = 'pending'
    ORDER BY o.created_at DESC
";
$ordersResult = $conn->query($ordersQuery);

$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");

$user = null;

if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
  $result  = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");
  $user    = $result->fetch_assoc();

  // Only keep user if CUSTOMER
  if ($user && strtolower($user['role']) !== "customer") {
    $user = null;
  }
}

$menuQuery = "
    SELECT
        p.pizza_id, p.pizza_name, c.category_name AS category,
        p.image_path,
        (SELECT GROUP_CONCAT(i.ingredient_name ORDER BY i.ingredient_name SEPARATOR ', ')
         FROM pizza_ingredients pi
         JOIN ingredients i ON pi.ingredient_id = i.ingredient_id
         WHERE pi.pizza_id = p.pizza_id) AS ingredients
    FROM pizzas p
    JOIN categories c ON p.category_id = c.category_id
    ORDER BY c.category_name, p.pizza_name
";
$menuResult = $conn->query($menuQuery);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | Cashier</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* ── CASHIER-PAGE OVERRIDES & EXTRAS ──────────── */

    /* Pending orders strip */
    .pending-orders-wrapper {
      background: var(--white);
      border-bottom: 2px solid var(--border);
      padding: 20px 28px;
      position: relative;
    }

    .pending-orders-wrapper > h3 {
      font-family: var(--font-main);
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 1.2px;
      text-transform: uppercase;
      color: var(--text-mid);
      margin-bottom: 14px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .pending-orders-wrapper > h3::before {
      content: '';
      display: inline-block;
      width: 10px;
      height: 10px;
      border-radius: 50%;
      background: var(--orange);
      animation: pulse 1.4s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(1.3); }
    }

    /* Slider wrapper */
    .pending-slider {
      display: flex;
      align-items: center;
      gap: 6px;
      position: relative;
    }

    .pending-container {
      display: flex;
      gap: 14px;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 6px 2px 10px;
      flex: 1;
      scrollbar-width: none;
    }
    .pending-container::-webkit-scrollbar { display: none; }

    /* Individual pending card */
    .pending-card {
      min-width: 240px;
      max-width: 240px;
      background: #FFFDE7;
      border: 1.5px solid #FFE082;
      border-radius: var(--radius-md);
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex-shrink: 0;
      box-shadow: var(--shadow-sm);
      transition: box-shadow 0.18s, transform 0.18s;
    }

    .pending-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }

    .pending-card p {
      font-size: 12px;
      color: var(--text-mid);
      line-height: 1.5;
    }

    .pending-card p strong {
      color: var(--text-dark);
      font-weight: 700;
    }

    .pending-card hr {
      border: none;
      border-top: 1px solid #FFE082;
      margin: 6px 0;
    }

    /* Order ID badge */
    .order-id-badge {
      display: inline-block;
      background: var(--orange);
      color: #fff;
      font-family: var(--font-main);
      font-weight: 800;
      font-size: 11px;
      letter-spacing: 0.5px;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
      margin-bottom: 6px;
    }

    /* Status badge */
    .status-badge {
      display: inline-block;
      background: #FFF3E0;
      color: var(--amber);
      border: 1px solid #FFE0B2;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      padding: 2px 8px;
      border-radius: var(--radius-pill);
    }

    /* Pending card action buttons */
    .pending-actions {
      display: flex;
      gap: 8px;
      margin-top: 8px;
    }

    .pending-actions button {
      flex: 1;
      padding: 7px 0;
      margin-top: 0;
      border: none;
      border-radius: var(--radius-sm);
      font-family: var(--font-main);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.4px;
      cursor: pointer;
      transition: background 0.15s, transform 0.15s;
    }

    .pending-actions button:first-child {
      background: #FFEBEE;
      color: var(--red);
    }
    .pending-actions button:first-child:hover {
      background: #FFCDD2;
      transform: translateY(-1px);
    }

    .pending-actions button:last-child {
      background: var(--orange);
      color: #fff;
    }
    .pending-actions button:last-child:hover {
      background: var(--orange-light);
      transform: translateY(-1px);
    }

    /* Slider arrows */
    .slider-btn {
      background: var(--yellow);
      border: none;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      font-size: 13px;
      font-weight: 900;
      cursor: pointer;
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: var(--shadow-sm);
      transition: background 0.15s, transform 0.15s;
      margin-top: 0;
    }

    .slider-btn:hover {
      background: var(--yellow-dark);
      transform: scale(1.08);
    }

    /* Empty state */
    .no-pending {
      font-size: 13px;
      color: var(--text-light);
      font-style: italic;
      padding: 8px 0;
    }

    /* ── CASHIER SECTION ─────────────────────────── */
    .cashier-section {
      background: #F9FBE7;
      border: 1.5px solid #DCE775;
      border-radius: var(--radius-md);
      padding: 16px;
      margin-top: 18px;
    }

    .cashier-section h3 {
      font-family: var(--font-main);
      font-size: 13px;
      font-weight: 800;
      letter-spacing: 1px;
      text-transform: uppercase;
      color: #558B2F;
      border-bottom: 2px solid #DCE775;
      padding-bottom: 8px;
      margin-bottom: 14px;
    }

    .cashier-section label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: var(--text-mid);
      margin-top: 12px;
      margin-bottom: 4px;
    }

    .cashier-section label:first-of-type { margin-top: 0; }

    .cashier-section input[type="text"] {
      width: 100%;
      padding: 9px 12px;
      border: 1.5px solid #DCE775;
      border-radius: var(--radius-sm);
      font-family: var(--font-body);
      font-size: 13px;
      background: #fff;
      outline: none;
      transition: border-color 0.18s, box-shadow 0.18s;
    }

    .cashier-section input:focus {
      border-color: #8BC34A;
      box-shadow: 0 0 0 3px rgba(139,195,74,0.2);
    }

    .cashier-section input[readonly] {
      background: #F1F8E9;
      color: var(--text-mid);
      cursor: not-allowed;
    }

    /* Change display */
    #changeAmount {
      font-size: 15px;
      font-weight: 800;
      font-family: var(--font-main);
    }

    /* Calculator grid */
    .calculator {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 8px;
      margin-top: 14px;
    }

    .calculator button {
      padding: 13px 0;
      margin-top: 0;
      background: var(--white);
      border: 1.5px solid var(--border);
      border-radius: var(--radius-sm);
      font-family: var(--font-main);
      font-size: 15px;
      font-weight: 800;
      color: var(--text-dark);
      cursor: pointer;
      transition: background 0.15s, border-color 0.15s, transform 0.12s;
    }

    .calculator button:hover {
      background: var(--yellow);
      border-color: var(--yellow-dark);
      transform: translateY(-1px);
    }

    .calculator button:active { transform: scale(0.96); }

    /* CANCEL ORDER calc button */
    .calculator button[onclick="cancelOrder()"] {
      background: #FFEBEE;
      color: var(--red);
      border-color: #FFCDD2;
      font-size: 11px;
      letter-spacing: 0.3px;
    }
    .calculator button[onclick="cancelOrder()"]:hover {
      background: #FFCDD2;
    }

    /* FINALIZE ORDER calc button */
    .calculator #finalizeBtn {
      background: var(--orange);
      color: #fff;
      border-color: var(--orange);
      font-size: 11px;
      letter-spacing: 0.3px;
      box-shadow: 0 3px 10px rgba(255,107,0,0.3);
    }
    .calculator #finalizeBtn:hover:not(:disabled) {
      background: var(--orange-light);
      transform: translateY(-2px);
      box-shadow: 0 5px 16px rgba(255,107,0,0.4);
    }
    .calculator #finalizeBtn:disabled {
      background: #ccc;
      color: #888;
      border-color: #ccc;
      cursor: not-allowed;
      box-shadow: none;
      transform: none;
    }

    /* Out-of-stock modal close btn */
    #outOfStockModal .modal-content button[onclick="closeOutOfStockModal()"] {
      background: var(--red);
      color: #fff;
    }
    #outOfStockModal .modal-content button[onclick="closeOutOfStockModal()"]:hover {
      background: #c62828;
    }

    /* ── Cashier-page menu-scroll height override ── */
    .cashier-page .menu-scroll { max-height: 1400px; }

    /* ══════════════════════════════════════════════
       TOAST NOTIFICATIONS
    ══════════════════════════════════════════════ */
    #toast-container {
      position: fixed;
      top: 80px;
      right: 20px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      gap: 10px;
      pointer-events: none;
    }

    .toast {
      display: flex;
      align-items: flex-start;
      gap: 12px;
      min-width: 300px;
      max-width: 380px;
      padding: 14px 16px;
      border-radius: 10px;
      box-shadow: 0 6px 24px rgba(0,0,0,0.15);
      font-family: var(--font-body);
      font-size: 13px;
      line-height: 1.5;
      pointer-events: all;
      animation: toastIn 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    @keyframes toastIn {
      from { transform: translateX(120%); opacity: 0; }
      to   { transform: translateX(0);    opacity: 1; }
    }

    @keyframes toastOut {
      from { transform: translateX(0);    opacity: 1; }
      to   { transform: translateX(120%); opacity: 0; }
    }

    .toast.removing { animation: toastOut 0.3s ease forwards; }

    .toast-icon {
      font-size: 18px;
      flex-shrink: 0;
      margin-top: 1px;
    }

    .toast-body { flex: 1; }
    .toast-title {
      font-weight: 700;
      font-size: 13px;
      margin-bottom: 2px;
    }
    .toast-msg { color: inherit; opacity: 0.85; font-size: 12px; }

    .toast-close {
      background: none;
      border: none;
      cursor: pointer;
      font-size: 16px;
      opacity: 0.5;
      padding: 0;
      margin-top: 0;
      line-height: 1;
      flex-shrink: 0;
      transition: opacity 0.15s;
    }
    .toast-close:hover { opacity: 1; }

    /* Progress bar */
    .toast-progress {
      position: absolute;
      bottom: 0; left: 0;
      height: 3px;
      border-radius: 0 0 10px 10px;
      animation: toastProgress linear forwards;
    }
    @keyframes toastProgress {
      from { width: 100%; }
      to   { width: 0%; }
    }

    /* Variants */
    .toast-error {
      background: #fff5f5;
      border: 1.5px solid #FFCDD2;
      color: #c62828;
    }
    .toast-error .toast-progress { background: #c62828; }

    .toast-warning {
      background: #fffbf0;
      border: 1.5px solid #FFE082;
      color: #c05000;
    }
    .toast-warning .toast-progress { background: #f4a700; }

    .toast-success {
      background: #f1f8e9;
      border: 1.5px solid #AED581;
      color: #2e7d32;
    }
    .toast-success .toast-progress { background: #2e7d32; }

    .toast-info {
      background: #e8f0ff;
      border: 1.5px solid #90CAF9;
      color: #1a56db;
    }
    .toast-info .toast-progress { background: #1a56db; }

    /* ══════════════════════════════════════════════
       VALIDATION MODAL (delivery address etc.)
    ══════════════════════════════════════════════ */
    #validationModal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 9998;
      background: rgba(0,0,0,0.45);
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(3px);
    }
    #validationModal.open { display: flex; }

    .vmodal-box {
      background: #fff;
      border-radius: 14px;
      padding: 28px 32px;
      max-width: 420px;
      width: 90%;
      box-shadow: 0 12px 48px rgba(0,0,0,0.18);
      animation: popIn 0.25s ease;
      text-align: center;
    }

    @keyframes popIn {
      from { transform: scale(0.9); opacity: 0; }
      to   { transform: scale(1);   opacity: 1; }
    }

    .vmodal-icon {
      font-size: 44px;
      margin-bottom: 12px;
      display: block;
    }

    .vmodal-title {
      font-family: var(--font-main);
      font-size: 17px;
      font-weight: 900;
      color: var(--text-dark);
      margin-bottom: 8px;
    }

    .vmodal-msg {
      font-size: 13px;
      color: var(--text-mid);
      line-height: 1.6;
      margin-bottom: 22px;
    }

    .vmodal-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .vmodal-btn {
      padding: 10px 22px;
      border: none;
      border-radius: 8px;
      font-family: var(--font-main);
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      transition: transform 0.15s, background 0.15s;
    }
    .vmodal-btn:hover { transform: translateY(-1px); }

    .vmodal-btn-primary {
      background: var(--orange);
      color: #fff;
      box-shadow: 0 3px 10px rgba(255,107,0,0.3);
    }
    .vmodal-btn-primary:hover { background: var(--orange-light); }

    .vmodal-btn-ghost {
      background: #f0f0f0;
      color: #555;
    }
    .vmodal-btn-ghost:hover { background: #e4e4e4; }

    /* Highlight a field with error */
    .field-error {
      border-color: var(--red) !important;
      box-shadow: 0 0 0 3px rgba(198,40,40,0.15) !important;
      animation: shake 0.35s ease;
    }
    @keyframes shake {
      0%,100% { transform: translateX(0); }
      20%,60%  { transform: translateX(-5px); }
      40%,80%  { transform: translateX(5px); }
    }

  </style>
</head>

<body class="cashier-page"
  data-logged="<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
  data-username="<?= isset($user['username']) ? $user['username'] : ''; ?>"
  data-mobile="<?= isset($user['mobile_number']) ? $user['mobile_number'] : ''; ?>"
  data-email="<?= isset($user['email']) ? $user['email'] : ''; ?>"
>

<!-- NAVBAR -->
<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">

  <div class="nav-links">
    <a href="cashier.php">HOME</a>

    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="profile_customer.php"><?= $_SESSION['username']; ?></a>
      <a href="logout.php">LOG OUT</a>
    <?php else: ?>
      <a href="signup.php">SIGN UP</a>
      <a href="login.php">LOG IN</a>
    <?php endif; ?>
  </div>
</header>


<!-- ── PENDING ONLINE ORDERS ───────────────────── -->
<div class="pending-orders-wrapper">

  <h3>Pending Online Orders</h3>

  <div class="pending-slider">

    <button class="slider-btn left" onclick="slideLeft()">&#9664;</button>

    <div class="pending-container" id="pendingContainer">

      <?php
      $hasOrders = false;
      while ($order = $ordersResult->fetch_assoc()):
        $hasOrders = true;

        $branch_id    = $order['branch_id'];
        $branchQuery  = "SELECT * FROM branches WHERE branch_id = '$branch_id'";
        $branchResult = $conn->query($branchQuery);
        $branchData   = $branchResult->fetch_assoc();
      ?>

      <div class="pending-card">

        <span class="order-id-badge"># <?= $order['order_id']; ?></span>

        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
        <p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile_number']); ?></p>
        <?php if ($order['email']): ?>
          <p><strong>Email:</strong> <?= htmlspecialchars($order['email']); ?></p>
        <?php endif; ?>

        <hr>

        <p><strong>Branch:</strong> <?= htmlspecialchars($branchData['branch_name'] . ', ' . $branchData['location']); ?></p>
        <?php if ($order['address']): ?>
          <p><strong>Address:</strong> <?= htmlspecialchars($order['address']); ?></p>
        <?php endif; ?>
        <p><strong>Type:</strong> <?= htmlspecialchars($order['order_type']); ?></p>
        <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>

        <hr>

        <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'], 2); ?></p>
        <p><span class="status-badge"><?= htmlspecialchars($order['status']); ?></span>
           &nbsp;<small style="color:var(--text-light); font-size:11px;"><?= date('M d, g:i A', strtotime($order['created_at'])); ?></small></p>

        <div class="pending-actions">
          <button onclick="cancelPendingOrder(<?= $order['order_id']; ?>)">CANCEL</button>
          <button onclick="processOrder(<?= $order['order_id']; ?>)">PROCESS</button>
        </div>

      </div>

      <?php endwhile; ?>

      <?php if (!$hasOrders): ?>
        <p class="no-pending">No pending online orders at the moment.</p>
      <?php endif; ?>

    </div>

    <button class="slider-btn right" onclick="slideRight()">&#9654;</button>

  </div>

</div>


<!-- ── MAIN CONTENT ─────────────────────────────── -->
<div class="container">

  <!-- LEFT: MENU GROUP -->
  <section class="menu-group">

    <div class="big-image">
      <img id="bigPreview" src="menu/Default.png" alt="Pizza Preview">
    </div>

    <h2 id="pizzaName">Select a Pizza</h2>
    <p id="ingredients">Ingredients will appear here</p>

    <div class="menu-scroll">
      <?php
      $currentCategory = "";
      while ($pizza = $menuResult->fetch_assoc()) {
        if ($pizza['category'] !== $currentCategory) {
          echo "<h3>" . strtoupper(htmlspecialchars($pizza['category'])) . "</h3>";
          $currentCategory = $pizza['category'];
        }
        echo '
          <img
            src="' . htmlspecialchars($pizza['image_path']) . '"
            class="menu-img"
            data-name="' . htmlspecialchars($pizza['pizza_name']) . '"
            data-ingredients="' . htmlspecialchars($pizza['ingredients']) . '"
            onclick="applyPizzaSelection(this)">
        ';
      }
      ?>
    </div>

  </section>

  <!-- RIGHT: ORDER GROUP -->
  <section class="order-group">

    <h3>Pizza Order</h3>

    <label>Pizza:</label>
    <select id="pizzaSelect" onchange="updatePrice()">
      <option value="">Choose a pizza...</option>
      <?php
      $pizzaDropdownQuery  = "SELECT DISTINCT pizza_name FROM pizzas ORDER BY pizza_name";
      $pizzaDropdownResult = $conn->query($pizzaDropdownQuery);
      while ($row = $pizzaDropdownResult->fetch_assoc()) {
        $stockQuery = $conn->query(
          "SELECT stock FROM pizzas WHERE pizza_name='" . $conn->real_escape_string($row['pizza_name']) . "' LIMIT 1"
        );
        $stockRow = $stockQuery->fetch_assoc();
        $stock    = $stockRow ? intval($stockRow['stock']) : 0;
        echo "<option value=\"" . htmlspecialchars($row['pizza_name']) . "\"
              data-stock=\"" . $stock . "\">"
          . htmlspecialchars($row['pizza_name']) .
          "</option>";
      }
      ?>
    </select>

    <div class="inline-group">
      <label>Size:</label>
      <label><input type="radio" name="size" value="9" checked onclick="updatePrice()"> 9"</label>
      <label><input type="radio" name="size" value="11" onclick="updatePrice()"> 11"</label>
    </div>

    <div class="inline-group">
      <label>Cheese:</label>
      <label><input type="radio" name="cheese" value="Quickmelt" checked onclick="updatePrice()"> Quickmelt</label>
      <label><input type="radio" name="cheese" value="Mozzarella" onclick="updatePrice()"> Mozzarella</label>
    </div>

    <label>Stock:</label>
    <input type="text" id="stock" readonly value="0">

    <label>Quantity:</label>
    <input type="number" id="qty" min="1" value="1" onchange="updatePrice()">

    <label>Current Price:</label>
    <input type="text" id="price" readonly value="0">

    <div class="btn-row">
      <button onclick="clearPizzaForm()">CLEAR</button>
      <button id="addBtn" onclick="addToOrder()">ADD</button>
    </div>

    <!-- ORDER TABLE -->
    <h3>Order</h3>
    <table id="orderTable">
      <tr>
        <th>PIZZA</th><th>SIZE</th><th>CHEESE</th>
        <th>BASE PRICE</th><th>QNTY</th><th>OVERALL</th><th>ACTION</th>
      </tr>
    </table>

    <label style="margin-top:15px; font-weight:bold;">TOTAL:</label>
    <input type="text" id="totalAmount" value="0.00" readonly style="width:100%; margin-top:5px;">

    <button class="clear-order" onclick="clearOrder()">CLEAR ORDER</button>

    <!-- CUSTOMER INFO -->
    <h3>Customer Info</h3>

    <label>Username</label>
    <input type="text"
       id="customerName"
       placeholder="e.g. Juan Dela Cruz"
       value="<?= (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'customer') ? $_SESSION['username'] : ''; ?>"
        >

    <label>Mobile Number</label>
    <input type="text"
       id="contact"
       placeholder="e.g. 09123456789"
       value="<?= isset($user['mobile_number']) ? $user['mobile_number'] : ''; ?>"
       oninput="restrictMobileNumber(this)"
       >

    <label>Email (Optional)</label>
    <input type="email"
       id="optionalEmail"
       placeholder="e.g. juan@gmail.com"
       value="<?= isset($user['email']) ? $user['email'] : ''; ?>"
       >

    <!-- ORDER METHODS -->
    <h3>Order Methods</h3>

    <label>Branch</label>
    <select id="branch">
      <option disabled selected hidden>Choose a branch near you...</option>
      <?php while ($row = $branches->fetch_assoc()): ?>
        <option value="<?= $row['branch_id']; ?>">
          <?= htmlspecialchars($row['branch_name'] . ', ' . $row['location']); ?>
        </option>
      <?php endwhile; ?>
    </select>

    <label id="addressLabel">Address <span id="addressOptional" style="font-size:11px; font-weight:400; color:#aaa;">(optional for pick-up)</span><span class="req-star" style="color:var(--red); margin-left:2px; display:none;">*</span></label>
    <input type="text" id="address" placeholder="Enter address (optional for pick-up)">

    <div class="inline-group">
      <label>Order:</label>
      <label><input type="radio" name="orderType" value="PICK-UP" checked> PICK-UP</label>
      <label><input type="radio" name="orderType" value="DELIVERY"> DELIVERY</label>
    </div>

    <div class="inline-group">
      <label>Payment:</label>
      <label><input type="radio" name="payment" value="CASH" checked> CASH</label>
      <label><input type="radio" name="payment" value="ONLINE"> ONLINE PAYMENT</label>
    </div>

    <!-- CASHIER SECTION -->
    <div class="cashier-section">
      <h3>&#x1F4B0; Cashier</h3>

      <label>Total</label>
      <input type="text" id="cashierTotal" readonly placeholder="₱0.00">

      <label>Amount Received</label>
      <input type="text" id="amountReceived" placeholder="Enter amount"
        oninput="sanitizeInput(); calculateChange();">

      <label>Change</label>
      <input type="text" id="changeAmount" readonly placeholder="₱0.00">

      <!-- CALCULATOR KEYPAD -->
      <div class="calculator">
        <button onclick="pressKey('1')">1</button>
        <button onclick="pressKey('2')">2</button>
        <button onclick="pressKey('3')">3</button>

        <button onclick="pressKey('4')">4</button>
        <button onclick="pressKey('5')">5</button>
        <button onclick="pressKey('6')">6</button>

        <button onclick="pressKey('7')">7</button>
        <button onclick="pressKey('8')">8</button>
        <button onclick="pressKey('9')">9</button>

        <button onclick="clearEntry()">C</button>
        <button onclick="pressKey('0')">0</button>
        <button onclick="deleteLast()">&#9003;</button>

        <button onclick="cancelOrder()">CANCEL ORDER</button>
        <button onclick="pressKey('.')">.</button>
        <button id="finalizeBtn" onclick="finalizeOrder()">FINALIZE</button>
      </div>
    </div>

    <!-- Success message -->
    <p id="successMessage" style="display:none; margin-top:12px;">
      ✅ Order submitted successfully!
    </p>

  </section>
</div>


<!-- OUT-OF-STOCK MODAL -->
<div id="outOfStockModal" class="modal">
  <div class="modal-content" style="max-width:620px;">
    <h2 style="color: var(--red);">&#9888; Cannot Process Order</h2>
    <p style="font-size:13px; color:var(--text-mid); margin: 10px 0 16px;">
      One or more items are out of stock. Please restock before processing.
    </p>
    <div id="outOfStockBody"></div>
    <div style="margin-top:16px; text-align:right;">
      <button onclick="closeOutOfStockModal()">CLOSE</button>
    </div>
  </div>
</div>

<!-- RECEIPT MODAL -->
<div id="receiptModal" class="modal">
  <div class="modal-content">
    <h2>Order Receipt</h2>
    <div id="receiptContent"></div>
    <button onclick="printReceipt()">Download / Print</button>
    <button onclick="closeModal()">Close</button>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>

<!-- VALIDATION MODAL -->
<div id="validationModal">
  <div class="vmodal-box">
    <span class="vmodal-icon" id="vmodalIcon">⚠️</span>
    <div class="vmodal-title" id="vmodalTitle">Missing Information</div>
    <div class="vmodal-msg"   id="vmodalMsg">Please fill in all required fields.</div>
    <div class="vmodal-actions" id="vmodalActions">
      <button class="vmodal-btn vmodal-btn-ghost"   onclick="closeValidationModal()">Dismiss</button>
      <button class="vmodal-btn vmodal-btn-primary" id="vmodalActionBtn" onclick="closeValidationModal()">OK</button>
    </div>
  </div>
</div>

<script src="js/home.js"></script>

<script>
// ══════════════════════════════════════════════════════════════
//  TOAST SYSTEM
// ══════════════════════════════════════════════════════════════
function showToast(type, title, msg, duration = 4000) {
  const icons = { error: '❌', warning: '⚠️', success: '✅', info: 'ℹ️' };
  const container = document.getElementById('toast-container');

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <span class="toast-icon">${icons[type] || 'ℹ️'}</span>
    <div class="toast-body">
      <div class="toast-title">${title}</div>
      ${msg ? `<div class="toast-msg">${msg}</div>` : ''}
    </div>
    <button class="toast-close" onclick="dismissToast(this.parentElement)">&times;</button>
    <div class="toast-progress" style="animation-duration:${duration}ms"></div>
  `;

  container.appendChild(toast);

  // Auto-remove
  const timer = setTimeout(() => dismissToast(toast), duration);
  toast._timer = timer;
}

function dismissToast(toast) {
  if (!toast || toast._removing) return;
  toast._removing = true;
  clearTimeout(toast._timer);
  toast.classList.add('removing');
  toast.addEventListener('animationend', () => toast.remove(), { once: true });
}

// ══════════════════════════════════════════════════════════════
//  VALIDATION MODAL
// ══════════════════════════════════════════════════════════════
let _vmodalCallback = null;

function showValidationModal(icon, title, msg, actionLabel, actionCallback, showDismiss = true) {
  document.getElementById('vmodalIcon').textContent  = icon;
  document.getElementById('vmodalTitle').textContent = title;
  document.getElementById('vmodalMsg').textContent   = msg;

  const actionBtn  = document.getElementById('vmodalActionBtn');
  const actionsDiv = document.getElementById('vmodalActions');

  actionBtn.textContent = actionLabel;
  _vmodalCallback = actionCallback;

  // Show/hide dismiss button
  const dismissBtn = actionsDiv.querySelector('.vmodal-btn-ghost');
  dismissBtn.style.display = showDismiss ? '' : 'none';

  document.getElementById('validationModal').classList.add('open');
}

function closeValidationModal() {
  document.getElementById('validationModal').classList.remove('open');
  if (typeof _vmodalCallback === 'function') {
    _vmodalCallback();
    _vmodalCallback = null;
  }
}

// Close on backdrop click
document.getElementById('validationModal').addEventListener('click', function(e) {
  if (e.target === this) closeValidationModal();
});

// ══════════════════════════════════════════════════════════════
//  FIELD HIGHLIGHT HELPER
// ══════════════════════════════════════════════════════════════
function highlightField(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('field-error');
  el.addEventListener('input', () => el.classList.remove('field-error'), { once: true });
  el.addEventListener('change', () => el.classList.remove('field-error'), { once: true });
  // Scroll to field
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  setTimeout(() => el.focus(), 300);
}

// ── Address label: update required/optional based on order type ──
function updateAddressLabel() {
  const orderTypeEl = document.querySelector('input[name="orderType"]:checked');
  const isDelivery  = orderTypeEl && orderTypeEl.value === 'DELIVERY';
  const label       = document.getElementById('addressLabel');
  const addrInput   = document.getElementById('address');
  const optSpan     = document.getElementById('addressOptional');
  const reqStar     = label ? label.querySelector('.req-star') : null;
  if (!label) return;
  if (isDelivery) {
    if (optSpan) optSpan.style.display = 'none';
    if (reqStar) reqStar.style.display = 'inline';
    addrInput.placeholder = 'Enter delivery address (required)';
  } else {
    if (optSpan) optSpan.style.display = '';
    if (reqStar) reqStar.style.display = 'none';
    addrInput.placeholder = 'Enter address (optional for pick-up)';
  }
}

// Hook order type radios
document.querySelectorAll('input[name="orderType"]').forEach(r => {
  r.addEventListener('change', updateAddressLabel);
});
document.addEventListener('DOMContentLoaded', updateAddressLabel);

// ══════════════════════════════════════════════════════════════
//  CASHIER-SPECIFIC finalizeOrder OVERRIDE
//  Replaces the generic home.js finalizeOrder with full
//  validation + toast/modal error handling for cashier.php
// ══════════════════════════════════════════════════════════════
function finalizeOrder() {

  const table        = document.getElementById('orderTable');
  const customerName = document.getElementById('customerName').value.trim();
  const mobile       = document.getElementById('contact').value.trim();
  const branchSelect = document.getElementById('branch');
  const branch       = branchSelect.value;
  const branchText   = branchSelect.options[branchSelect.selectedIndex]?.text ?? '';
  const address      = document.getElementById('address').value.trim();
  const orderTypeEl  = document.querySelector('input[name="orderType"]:checked');
  const paymentEl    = document.querySelector('input[name="payment"]:checked');
  const totalStr     = document.getElementById('totalAmount').value;
  const total        = parseFloat(totalStr) || 0;
  const received     = parseFloat(document.getElementById('amountReceived').value) || 0;
  const email        = document.getElementById('optionalEmail').value.trim();

  // ── 1. Must have at least one item ──────────────────────────
  if (table.rows.length <= 1) {
    showToast('error', 'No Items', 'Add at least one pizza to the order before finalizing.');
    return;
  }

  // ── 2. Customer name ────────────────────────────────────────
  if (customerName === '') {
    showToast('warning', 'Customer Name Required', 'Please enter the customer\'s name.');
    highlightField('customerName');
    return;
  }

  // ── 3. Mobile number ────────────────────────────────────────
  if (mobile === '') {
    showToast('warning', 'Mobile Number Required', 'Please enter the customer\'s mobile number.');
    highlightField('contact');
    return;
  }
  if (mobile.length !== 11 || !mobile.startsWith('09')) {
    showToast('error', 'Invalid Mobile Number', 'Mobile number must be 11 digits and start with 09.');
    highlightField('contact');
    return;
  }

  // ── 4. Branch ────────────────────────────────────────────────
  if (!branch || branch === '0') {
    showToast('warning', 'Branch Required', 'Please select a branch for this order.');
    highlightField('branch');
    return;
  }

  // ── 5. Delivery address ──────────────────────────────────────
  const orderType = orderTypeEl ? orderTypeEl.value : 'PICK-UP';
  if (orderType === 'DELIVERY' && address === '') {
    showValidationModal(
      '🛵',
      'Delivery Address Missing',
      'This order is set to DELIVERY but no address was entered. Please fill in the delivery address before finalizing.',
      'Fill Address',
      () => highlightField('address'),
      true
    );
    return;
  }

  // ── 6. Payment ───────────────────────────────────────────────
  const payment = paymentEl ? paymentEl.value : 'CASH';
  if (payment === 'CASH') {
    if (received === 0) {
      showToast('warning', 'Amount Not Entered', 'Please enter the amount received from the customer.');
      highlightField('amountReceived');
      return;
    }
    if (received < total) {
      showValidationModal(
        '💸',
        'Insufficient Payment',
        `Total is ₱${total.toFixed(2)} but only ₱${received.toFixed(2)} was received. Please collect the correct amount.`,
        'OK',
        null,
        false
      );
      return;
    }
  }

  // ── 7. Collect items ─────────────────────────────────────────
  const items = [];
  for (let i = 1; i < table.rows.length; i++) {
    const row = table.rows[i];
    items.push({
      pizza:    row.cells[0].innerText,
      size:     row.cells[1].innerText,
      cheese:   row.cells[2].innerText,
      price:    parseFloat(row.cells[3].innerText),
      quantity: parseInt(row.cells[4].innerText),
      total:    parseFloat(row.cells[5].innerText),
    });
  }

  // ── 8. Disable button + send ─────────────────────────────────
  const btn = document.getElementById('finalizeBtn');
  btn.disabled    = true;
  btn.textContent = 'Processing...';

  fetch('save_order.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      customer_name: customerName,
      mobile,
      email,
      branch,
      address,
      order_type: orderType,
      payment,
      total,
      items,
      is_online: 0,   // cashier orders are always completed immediately
    }),
  })
  .then(res => {
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  })
  .then(data => {
    if (data.status !== 'success') {
      showToast('error', 'Order Failed', data.message || 'An unknown error occurred. Please try again.');
      btn.disabled    = false;
      btn.textContent = 'FINALIZE';
      return;
    }

    // ── Success ──────────────────────────────────────────────
    showToast('success', 'Order Saved', `Order #${data.order_id} completed successfully.`, 5000);

    showReceipt(
      data.order_id, customerName, mobile, email,
      branchText, address, orderType, payment, total, items,
      false   // not online
    );

    // Mark pending online order as completed if we were processing one
    if (window.currentProcessingOrderId) {
      fetch('complete_order.php', {
        method:  'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body:    `order_id=${window.currentProcessingOrderId}`,
      });
      window.currentProcessingOrderId = null;
    }

    setTimeout(() => cancelOrder(), 800);
    setTimeout(() => {
      document.getElementById('successMessage').style.display = 'none';
    }, 4000);

    btn.disabled    = false;
    btn.textContent = 'FINALIZE';
  })
  .catch(err => {
    showToast('error', 'Network Error', `Could not reach the server. Check your connection and try again. (${err.message})`);
    btn.disabled    = false;
    btn.textContent = 'FINALIZE';
  });
}
</script>