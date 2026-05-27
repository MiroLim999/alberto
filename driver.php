<?php
session_start();
include "db_connect.php";

// ── Role guard: driver only ──────────────────────
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'driver') {
    header("Location: login.php");
    exit;
}

$driver_id = intval($_SESSION['user_id']);

// ── FETCH: Orders ready for delivery (completed by cashier, delivery type, not yet accepted) ──
$availableQuery = "
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at, o.driver_id,
           COALESCE(u.username,      oc.customer_name) AS customer_name,
           COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
           COALESCE(u.email,         oc.email)         AS email,
           (SELECT SUM(oi.quantity * pv.price)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id)            AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_type = 'DELIVERY' AND o.status = 'completed' AND o.driver_id IS NULL
    ORDER BY o.created_at ASC
";
$availableResult = $conn->query($availableQuery);

// ── FETCH: Orders this driver is currently delivering ──
$activeStmt = $conn->prepare("
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at, o.driver_id,
           COALESCE(u.username,      oc.customer_name) AS customer_name,
           COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
           COALESCE(u.email,         oc.email)         AS email,
           (SELECT SUM(oi.quantity * pv.price)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id)            AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_type = 'DELIVERY'
      AND o.status = 'out_for_delivery'
      AND o.driver_id = ?
    ORDER BY o.created_at ASC
");
$activeStmt->bind_param("i", $driver_id);
$activeStmt->execute();
$activeResult = $activeStmt->get_result();

// ── FETCH: Recently delivered by this driver (last 20) ──
$doneStmt = $conn->prepare("
    SELECT o.order_id, o.branch_id, o.address, o.order_type,
           o.payment_method, o.status, o.created_at, o.updated_at, o.driver_id,
           COALESCE(u.username,      oc.customer_name) AS customer_name,
           COALESCE(u.mobile_number, oc.mobile_number) AS mobile_number,
           (SELECT SUM(oi.quantity * pv.price)
            FROM order_items oi
            JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
            WHERE oi.order_id = o.order_id)            AS total_amount,
           b.branch_name, b.location AS branch_location
    FROM orders o
    LEFT JOIN users          u  ON o.user_id  = u.user_id
    LEFT JOIN order_contacts oc ON o.order_id = oc.order_id
    LEFT JOIN branches       b  ON o.branch_id = b.branch_id
    WHERE o.order_type = 'DELIVERY'
      AND o.status = 'delivered'
      AND o.driver_id = ?
    ORDER BY o.updated_at DESC
    LIMIT 20
");
$doneStmt->bind_param("i", $driver_id);
$doneStmt->execute();
$doneResult = $doneStmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | Driver</title>
  <link rel="stylesheet" href="css/style.css">
  <style>

    /* ══════════════════════════════════════════════
       DRIVER PAGE — STYLES
    ══════════════════════════════════════════════ */

    .driver-page {
      background: #F8F9FA;
      min-height: 100vh;
    }

    .driver-badge {
      display: inline-block;
      background: #1565C0;
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.8px;
      padding: 2px 8px;
      border-radius: var(--radius-pill);
      vertical-align: middle;
      margin-left: 4px;
    }

    .driver-header {
      background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
      color: #fff;
      padding: 28px 32px 24px;
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px solid #0A3880;
    }

    .driver-header .header-icon { font-size: 36px; line-height: 1; }

    .driver-header h1 {
      font-family: var(--font-main);
      font-size: 22px;
      font-weight: 900;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin: 0;
    }

    .driver-header p { font-size: 13px; opacity: 0.8; margin: 4px 0 0; }

    .driver-section {
      padding: 24px 32px;
      border-bottom: 1.5px solid var(--border);
    }
    .driver-section:last-child { border-bottom: none; }

    .section-title {
      font-family: var(--font-main);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 1.4px;
      text-transform: uppercase;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .section-title .dot {
      width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0;
    }
    .dot-blue   { background: #1565C0; }
    .dot-orange { background: var(--orange); animation: pulse 1.4s infinite; }
    .dot-green  { background: #2E7D32; }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(1.3); }
    }

    .card-grid { display: flex; gap: 14px; flex-wrap: wrap; }

    .delivery-card {
      width: 270px;
      border-radius: var(--radius-md);
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 5px;
      box-shadow: var(--shadow-sm);
      transition: box-shadow 0.18s, transform 0.18s;
      flex-shrink: 0;
      position: relative;
    }
    .delivery-card:hover {
      box-shadow: var(--shadow-md);
      transform: translateY(-2px);
    }

    .card-available { background: #E3F2FD; border: 1.5px solid #90CAF9; }
    .card-active    { background: #FFF8E1; border: 1.5px solid #FFD54F; }
    .card-done      { background: #F1F8E9; border: 1.5px solid #AED581; opacity: 0.85; }

    .delivery-card p { font-size: 12px; color: var(--text-mid); line-height: 1.5; margin: 0; }
    .delivery-card p strong { color: var(--text-dark); }
    .delivery-card hr { border: none; border-top: 1px solid rgba(0,0,0,0.08); margin: 6px 0; }

    .order-badge {
      display: inline-block;
      font-family: var(--font-main);
      font-weight: 800; font-size: 11px;
      letter-spacing: 0.4px;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
      margin-bottom: 6px;
    }
    .badge-blue   { background: #1565C0; color: #fff; }
    .badge-orange { background: var(--orange); color: #fff; }
    .badge-green  { background: #2E7D32; color: #fff; }

    .status-pill {
      display: inline-block; font-size: 10px; font-weight: 800;
      letter-spacing: 0.8px; text-transform: uppercase;
      padding: 2px 9px; border-radius: var(--radius-pill);
    }
    .pill-blue   { background: #BBDEFB; color: #0D47A1; }
    .pill-orange { background: #FFE0B2; color: #E65100; }
    .pill-green  { background: #DCEDC8; color: #33691E; }

    .card-actions { display: flex; gap: 8px; margin-top: 10px; }
    .card-actions button {
      flex: 1; padding: 8px 0; margin-top: 0; border: none;
      border-radius: var(--radius-sm);
      font-family: var(--font-main); font-size: 11px; font-weight: 800;
      letter-spacing: 0.3px; cursor: pointer;
      transition: background 0.15s, transform 0.12s;
    }
    .btn-accept { background: #1565C0; color: #fff; }
    .btn-accept:hover { background: #0D47A1; transform: translateY(-1px); }
    .btn-deliver { background: #2E7D32; color: #fff; }
    .btn-deliver:hover { background: #1B5E20; transform: translateY(-1px); }

    .empty-state { font-size: 13px; color: var(--text-light); font-style: italic; padding: 6px 0; }

    .items-toggle {
      background: none; border: 1px solid rgba(0,0,0,0.15);
      border-radius: var(--radius-sm); font-size: 11px; font-weight: 700;
      color: var(--text-mid); padding: 4px 10px; cursor: pointer;
      margin-top: 4px; width: 100%; text-align: left;
      transition: background 0.12s;
    }
    .items-toggle:hover { background: rgba(0,0,0,0.05); }

    .items-list {
      display: none; margin-top: 6px; font-size: 11px;
      color: var(--text-mid); line-height: 1.7;
      background: rgba(255,255,255,0.6);
      border-radius: var(--radius-sm); padding: 8px 10px;
    }
    .items-list.open { display: block; }

    /* ── Confirm modal ── */
    #driverConfirmModal {
      display: none; position: fixed; inset: 0;
      background: rgba(0,0,0,0.5); z-index: 1000;
      align-items: center; justify-content: center;
      backdrop-filter: blur(3px);
    }
    #driverConfirmModal.open { display: flex; }

    .confirm-box {
      background: #fff; border-radius: var(--radius-md);
      padding: 28px 32px; max-width: 380px; width: 90%;
      text-align: center; box-shadow: 0 8px 40px rgba(0,0,0,0.2);
      animation: popIn 0.25s ease;
    }
    @keyframes popIn {
      from { transform: scale(0.9); opacity: 0; }
      to   { transform: scale(1);   opacity: 1; }
    }

    .confirm-box .confirm-icon { font-size: 40px; margin-bottom: 10px; }
    .confirm-box h3 {
      font-family: var(--font-main); font-size: 16px;
      font-weight: 900; letter-spacing: 0.5px; margin-bottom: 8px;
    }
    .confirm-box p { font-size: 13px; color: var(--text-mid); margin-bottom: 20px; }

    .confirm-actions { display: flex; gap: 10px; justify-content: center; }
    .confirm-actions button {
      padding: 10px 24px; border: none; border-radius: var(--radius-sm);
      font-family: var(--font-main); font-size: 12px; font-weight: 800;
      letter-spacing: 0.4px; cursor: pointer;
      transition: background 0.15s, transform 0.12s;
    }
    .confirm-actions button:hover { transform: translateY(-1px); }

    .btn-cancel-confirm { background: #ECEFF1; color: var(--text-mid); }
    .btn-cancel-confirm:hover { background: #CFD8DC; }
    .btn-confirm-yes-blue  { background: #1565C0; color: #fff; }
    .btn-confirm-yes-blue:hover  { background: #0D47A1; }
    .btn-confirm-yes-green { background: #2E7D32; color: #fff; }
    .btn-confirm-yes-green:hover { background: #1B5E20; }

    .done-scroll {
      max-height: 320px; overflow-y: auto;
      display: flex; flex-wrap: wrap; gap: 14px; padding-bottom: 4px;
    }
    .done-scroll::-webkit-scrollbar { width: 5px; }
    .done-scroll::-webkit-scrollbar-thumb { background: #AED581; border-radius: 10px; }

    /* ── Toast notifications ── */
    #toast-container {
      position: fixed; top: 80px; right: 20px; z-index: 99999;
      display: flex; flex-direction: column; gap: 10px; pointer-events: none;
    }
    .toast {
      display: flex; align-items: flex-start; gap: 12px;
      min-width: 300px; max-width: 380px; padding: 14px 16px;
      border-radius: 10px; box-shadow: 0 6px 24px rgba(0,0,0,0.15);
      font-family: var(--font-body); font-size: 13px; line-height: 1.5;
      pointer-events: all; animation: toastIn 0.3s ease;
      position: relative; overflow: hidden;
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
    .toast-icon { font-size: 18px; flex-shrink: 0; margin-top: 1px; }
    .toast-body { flex: 1; }
    .toast-title { font-weight: 700; font-size: 13px; margin-bottom: 2px; }
    .toast-msg { color: inherit; opacity: 0.85; font-size: 12px; }
    .toast-close {
      background: none; border: none; cursor: pointer;
      font-size: 16px; opacity: 0.5; padding: 0; margin-top: 0;
      line-height: 1; flex-shrink: 0; transition: opacity 0.15s;
    }
    .toast-close:hover { opacity: 1; }
    .toast-progress {
      position: absolute; bottom: 0; left: 0; height: 3px;
      border-radius: 0 0 10px 10px; animation: toastProgress linear forwards;
    }
    @keyframes toastProgress { from { width: 100%; } to { width: 0%; } }

    .toast-error   { background: #fff5f5; border: 1.5px solid #FFCDD2; color: #c62828; }
    .toast-error .toast-progress   { background: #c62828; }
    .toast-warning { background: #fffbf0; border: 1.5px solid #FFE082; color: #c05000; }
    .toast-warning .toast-progress { background: #f4a700; }
    .toast-success { background: #f1f8e9; border: 1.5px solid #AED581; color: #2e7d32; }
    .toast-success .toast-progress { background: #2e7d32; }
    .toast-info    { background: #e8f0ff; border: 1.5px solid #90CAF9; color: #1a56db; }
    .toast-info .toast-progress    { background: #1a56db; }

  </style>
</head>

<body class="driver-page">

<!-- ══ NAVBAR ══════════════════════════════════════ -->
<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="driver.php">HOME <span class="driver-badge">DRIVER</span></a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="profile_customer.php"><?= htmlspecialchars($_SESSION['username']); ?></a>
      <a href="logout.php">LOG OUT</a>
    <?php else: ?>
      <a href="login.php">LOG IN</a>
    <?php endif; ?>
  </div>
</header>

<!-- ══ PAGE HEADER ═════════════════════════════════ -->
<div class="driver-header">
  <div class="header-icon">🛵</div>
  <div>
    <h1>Driver Dashboard</h1>
    <p>Manage your delivery queue — accept, track, and complete orders.</p>
  </div>
</div>

<!-- ══ SECTION 1 — AVAILABLE DELIVERIES ═══════════ -->
<div class="driver-section">
  <div class="section-title">
    <span class="dot dot-blue"></span>
    Available Deliveries
  </div>
  <div class="card-grid" id="availableGrid">
    <?php
    $hasAvailable = false;
    while ($order = $availableResult->fetch_assoc()):
      $hasAvailable = true;
      $oid = intval($order['order_id']);
      $items = $conn->query("
        SELECT oi.quantity, p.pizza_name, pv.size, pv.cheese, pv.price,
               (oi.quantity * pv.price) AS total
        FROM order_items oi
        JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
        JOIN pizzas p ON pv.pizza_id = p.pizza_id
        WHERE oi.order_id = $oid
      ");
    ?>
    <div class="delivery-card card-available">
      <span class="order-badge badge-blue"># <?= $order['order_id']; ?></span>
      <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
      <p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile_number']); ?></p>
      <hr>
      <p><strong>Branch:</strong> <?= htmlspecialchars($order['branch_name'] . ', ' . $order['branch_location']); ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['address'] ?: '—'); ?></p>
      <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>
      <hr>
      <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'], 2); ?></p>
      <p>
        <span class="status-pill pill-blue">READY FOR PICKUP</span>
        &nbsp;<small style="color:var(--text-light); font-size:10px;"><?= date('M d, g:i A', strtotime($order['created_at'])); ?></small>
      </p>
      <button class="items-toggle" onclick="toggleItems(this)">▸ View Items</button>
      <div class="items-list">
        <?php while ($item = $items->fetch_assoc()): ?>
          <div>• <?= htmlspecialchars($item['pizza_name']); ?> (<?= $item['size']; ?>", <?= htmlspecialchars($item['cheese']); ?>) × <?= $item['quantity']; ?> — ₱<?= number_format($item['total'], 2); ?></div>
        <?php endwhile; ?>
      </div>
      <div class="card-actions">
        <button class="btn-accept" onclick="confirmAccept(<?= $order['order_id']; ?>, '<?= htmlspecialchars(addslashes($order['customer_name'])); ?>')">
          🚀 ACCEPT
        </button>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if (!$hasAvailable): ?>
      <p class="empty-state">No deliveries available right now. Check back soon!</p>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SECTION 2 — MY ACTIVE DELIVERIES ═══════════ -->
<div class="driver-section">
  <div class="section-title">
    <span class="dot dot-orange"></span>
    My Active Deliveries
  </div>
  <div class="card-grid" id="activeGrid">
    <?php
    $hasActive = false;
    while ($order = $activeResult->fetch_assoc()):
      $hasActive = true;
      $oid = intval($order['order_id']);
      $items = $conn->query("
        SELECT oi.quantity, p.pizza_name, pv.size, pv.cheese, pv.price,
               (oi.quantity * pv.price) AS total
        FROM order_items oi
        JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
        JOIN pizzas p ON pv.pizza_id = p.pizza_id
        WHERE oi.order_id = $oid
      ");
    ?>
    <div class="delivery-card card-active">
      <span class="order-badge badge-orange"># <?= $order['order_id']; ?></span>
      <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
      <p><strong>Mobile:</strong> <?= htmlspecialchars($order['mobile_number']); ?></p>
      <hr>
      <p><strong>Branch:</strong> <?= htmlspecialchars($order['branch_name'] . ', ' . $order['branch_location']); ?></p>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['address'] ?: '—'); ?></p>
      <p><strong>Payment:</strong> <?= htmlspecialchars($order['payment_method']); ?></p>
      <hr>
      <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'], 2); ?></p>
      <p>
        <span class="status-pill pill-orange">OUT FOR DELIVERY</span>
        &nbsp;<small style="color:var(--text-light); font-size:10px;"><?= date('M d, g:i A', strtotime($order['created_at'])); ?></small>
      </p>
      <button class="items-toggle" onclick="toggleItems(this)">▸ View Items</button>
      <div class="items-list">
        <?php while ($item = $items->fetch_assoc()): ?>
          <div>• <?= htmlspecialchars($item['pizza_name']); ?> (<?= $item['size']; ?>", <?= htmlspecialchars($item['cheese']); ?>) × <?= $item['quantity']; ?> — ₱<?= number_format($item['total'], 2); ?></div>
        <?php endwhile; ?>
      </div>
      <div class="card-actions">
        <button class="btn-deliver" onclick="confirmDeliver(<?= $order['order_id']; ?>, '<?= htmlspecialchars(addslashes($order['customer_name'])); ?>')">
          ✅ MARK DELIVERED
        </button>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if (!$hasActive): ?>
      <p class="empty-state">You have no active deliveries. Accept one above!</p>
    <?php endif; ?>
  </div>
</div>

<!-- ══ SECTION 3 — DELIVERED (HISTORY) ════════════ -->
<div class="driver-section">
  <div class="section-title">
    <span class="dot dot-green"></span>
    Recently Delivered
  </div>
  <div class="done-scroll">
    <?php
    $hasDone = false;
    while ($order = $doneResult->fetch_assoc()):
      $hasDone = true;
      $oid = intval($order['order_id']);
      $items = $conn->query("
        SELECT oi.quantity, p.pizza_name, pv.size, pv.cheese
        FROM order_items oi
        JOIN pizza_variants pv ON oi.variant_id = pv.variant_id
        JOIN pizzas p ON pv.pizza_id = p.pizza_id
        WHERE oi.order_id = $oid
      ");
    ?>
    <div class="delivery-card card-done">
      <span class="order-badge badge-green"># <?= $order['order_id']; ?></span>
      <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']); ?></p>
      <hr>
      <p><strong>Address:</strong> <?= htmlspecialchars($order['address'] ?: '—'); ?></p>
      <p><strong>Total:</strong> ₱<?= number_format($order['total_amount'], 2); ?></p>
      <p>
        <span class="status-pill pill-green">DELIVERED ✓</span>
        &nbsp;<small style="color:var(--text-light); font-size:10px;">
          <?= $order['updated_at'] ? date('M d, g:i A', strtotime($order['updated_at'])) : date('M d, g:i A', strtotime($order['created_at'])); ?>
        </small>
      </p>
      <button class="items-toggle" onclick="toggleItems(this)">▸ View Items</button>
      <div class="items-list">
        <?php while ($item = $items->fetch_assoc()): ?>
          <div>• <?= htmlspecialchars($item['pizza_name']); ?> (<?= $item['size']; ?>", <?= htmlspecialchars($item['cheese']); ?>) × <?= $item['quantity']; ?></div>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endwhile; ?>
    <?php if (!$hasDone): ?>
      <p class="empty-state">No completed deliveries yet. Your history will appear here.</p>
    <?php endif; ?>
  </div>
</div>

<!-- ══ CONFIRM MODAL ════════════════════════════════ -->
<div id="driverConfirmModal">
  <div class="confirm-box">
    <div class="confirm-icon" id="confirmIcon">📦</div>
    <h3 id="confirmTitle">Confirm Action</h3>
    <p id="confirmMessage">Are you sure?</p>
    <div class="confirm-actions">
      <button class="btn-cancel-confirm" onclick="closeConfirm()">CANCEL</button>
      <button id="confirmYesBtn" onclick="">CONFIRM</button>
    </div>
  </div>
</div>

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>

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
//  TOGGLE ITEMS
// ══════════════════════════════════════════════════════════════
function toggleItems(btn) {
  const list = btn.nextElementSibling;
  const open = list.classList.toggle('open');
  btn.textContent = open ? '▾ Hide Items' : '▸ View Items';
}

// ══════════════════════════════════════════════════════════════
//  CONFIRM MODAL
// ══════════════════════════════════════════════════════════════
function openConfirm(icon, title, message, btnClass, btnLabel, action) {
  document.getElementById('confirmIcon').textContent    = icon;
  document.getElementById('confirmTitle').textContent   = title;
  document.getElementById('confirmMessage').textContent = message;

  const yesBtn = document.getElementById('confirmYesBtn');
  yesBtn.className = btnClass;
  yesBtn.textContent = btnLabel;
  yesBtn.onclick = action;

  document.getElementById('driverConfirmModal').classList.add('open');
}

function closeConfirm() {
  document.getElementById('driverConfirmModal').classList.remove('open');
}

// Close on backdrop click
document.getElementById('driverConfirmModal').addEventListener('click', function(e) {
  if (e.target === this) closeConfirm();
});

// ══════════════════════════════════════════════════════════════
//  ACCEPT DELIVERY
// ══════════════════════════════════════════════════════════════
function confirmAccept(orderId, customerName) {
  openConfirm(
    '🚀',
    'Accept Delivery?',
    `Accept order #${orderId} for ${customerName}? This will move it to your active deliveries.`,
    'btn-confirm-yes-blue',
    'YES, ACCEPT',
    () => doAccept(orderId)
  );
}

function doAccept(orderId) {
  closeConfirm();

  fetch('accept_delivery.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `order_id=${orderId}`
  })
  .then(res => res.text())
  .then(data => {
    const response = data.trim();
    if (response === 'success') {
      showToast('success', 'Delivery Accepted', `Order #${orderId} is now in your active deliveries.`);
      setTimeout(() => location.reload(), 1500);
    } else if (response === 'already_taken') {
      showToast('warning', 'Already Taken', 'This delivery was already accepted by another driver. Refreshing...');
      setTimeout(() => location.reload(), 2000);
    } else {
      showToast('error', 'Accept Failed', `Could not accept order. Server response: ${response}`);
    }
  })
  .catch(err => showToast('error', 'Network Error', 'Could not reach the server. Check your connection.'));
}

// ══════════════════════════════════════════════════════════════
//  MARK AS DELIVERED
// ══════════════════════════════════════════════════════════════
function confirmDeliver(orderId, customerName) {
  openConfirm(
    '✅',
    'Mark as Delivered?',
    `Confirm that order #${orderId} for ${customerName} has been delivered successfully?`,
    'btn-confirm-yes-green',
    'YES, DELIVERED',
    () => doDeliver(orderId)
  );
}

function doDeliver(orderId) {
  closeConfirm();

  fetch('complete_delivery.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `order_id=${orderId}`
  })
  .then(res => res.text())
  .then(data => {
    const response = data.trim();
    if (response === 'success') {
      showToast('success', 'Delivered!', `Order #${orderId} marked as delivered successfully.`);
      setTimeout(() => location.reload(), 1500);
    } else {
      showToast('error', 'Update Failed', `Could not mark order as delivered. Server response: ${response}`);
    }
  })
  .catch(err => showToast('error', 'Network Error', 'Could not reach the server. Check your connection.'));
}
</script>

</body>
</html>
