<?php
session_start();
include "db_connect.php";

// ── FETCH: Orders ready for delivery (completed by cashier, not yet accepted) ──
$availableQuery = "
  SELECT o.*, b.branch_name, b.location AS branch_location
  FROM v_orders_full o
  LEFT JOIN branches b ON o.branch_id = b.branch_id
  WHERE o.order_type = 'DELIVERY'
    AND o.status = 'completed'
  ORDER BY o.created_at ASC
";
$availableResult = $conn->query($availableQuery);

// ── FETCH: Orders this driver is currently delivering ──
$driver_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

$activeQuery = "
  SELECT o.*, b.branch_name, b.location AS branch_location
  FROM v_orders_full o
  LEFT JOIN branches b ON o.branch_id = b.branch_id
  WHERE o.order_type = 'DELIVERY'
    AND o.status = 'out_for_delivery'
    AND o.driver_id = '$driver_id'
  ORDER BY o.created_at ASC
";
$activeResult = $conn->query($activeQuery);

// ── FETCH: Recently delivered by this driver (last 20) ──
$doneQuery = "
  SELECT o.*, b.branch_name, b.location AS branch_location
  FROM v_orders_full o
  LEFT JOIN branches b ON o.branch_id = b.branch_id
  WHERE o.order_type = 'DELIVERY'
    AND o.status = 'delivered'
    AND o.driver_id = '$driver_id'
  ORDER BY o.updated_at DESC
  LIMIT 20
";
$doneResult = $conn->query($doneQuery);
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

    /* ── Navbar badge ── */
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

    /* ── Page header ── */
    .driver-header {
      background: linear-gradient(135deg, #1565C0 0%, #0D47A1 100%);
      color: #fff;
      padding: 28px 32px 24px;
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 3px solid #0A3880;
    }

    .driver-header .header-icon {
      font-size: 36px;
      line-height: 1;
    }

    .driver-header h1 {
      font-family: var(--font-main);
      font-size: 22px;
      font-weight: 900;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      margin: 0;
    }

    .driver-header p {
      font-size: 13px;
      opacity: 0.8;
      margin: 4px 0 0;
    }

    /* ── Section wrappers ── */
    .driver-section {
      padding: 24px 32px;
      border-bottom: 1.5px solid var(--border);
    }

    .driver-section:last-child {
      border-bottom: none;
    }

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
      width: 10px;
      height: 10px;
      border-radius: 50%;
      flex-shrink: 0;
    }

    .dot-blue   { background: #1565C0; }
    .dot-orange { background: var(--orange); animation: pulse 1.4s infinite; }
    .dot-green  { background: #2E7D32; }

    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(1.3); }
    }

    /* ── Card grid ── */
    .card-grid {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
    }

    /* ── Delivery card base ── */
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

    /* Available = blue tint */
    .card-available {
      background: #E3F2FD;
      border: 1.5px solid #90CAF9;
    }

    /* Active = orange tint */
    .card-active {
      background: #FFF8E1;
      border: 1.5px solid #FFD54F;
    }

    /* Done = green tint */
    .card-done {
      background: #F1F8E9;
      border: 1.5px solid #AED581;
      opacity: 0.85;
    }

    .delivery-card p {
      font-size: 12px;
      color: var(--text-mid);
      line-height: 1.5;
      margin: 0;
    }

    .delivery-card p strong {
      color: var(--text-dark);
    }

    .delivery-card hr {
      border: none;
      border-top: 1px solid rgba(0,0,0,0.08);
      margin: 6px 0;
    }

    /* Order ID badge */
    .order-badge {
      display: inline-block;
      font-family: var(--font-main);
      font-weight: 800;
      font-size: 11px;
      letter-spacing: 0.4px;
      padding: 3px 10px;
      border-radius: var(--radius-pill);
      margin-bottom: 6px;
    }

    .badge-blue   { background: #1565C0; color: #fff; }
    .badge-orange { background: var(--orange); color: #fff; }
    .badge-green  { background: #2E7D32; color: #fff; }

    /* Status pill */
    .status-pill {
      display: inline-block;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      padding: 2px 9px;
      border-radius: var(--radius-pill);
    }

    .pill-blue   { background: #BBDEFB; color: #0D47A1; }
    .pill-orange { background: #FFE0B2; color: #E65100; }
    .pill-green  { background: #DCEDC8; color: #33691E; }

    /* Card action buttons */
    .card-actions {
      display: flex;
      gap: 8px;
      margin-top: 10px;
    }

    .card-actions button {
      flex: 1;
      padding: 8px 0;
      margin-top: 0;
      border: none;
      border-radius: var(--radius-sm);
      font-family: var(--font-main);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.3px;
      cursor: pointer;
      transition: background 0.15s, transform 0.12s;
    }

    .btn-accept {
      background: #1565C0;
      color: #fff;
    }
    .btn-accept:hover {
      background: #0D47A1;
      transform: translateY(-1px);
    }

    .btn-deliver {
      background: #2E7D32;
      color: #fff;
    }
    .btn-deliver:hover {
      background: #1B5E20;
      transform: translateY(-1px);
    }

    /* Empty state */
    .empty-state {
      font-size: 13px;
      color: var(--text-light);
      font-style: italic;
      padding: 6px 0;
    }

    /* ── Items expandable ── */
    .items-toggle {
      background: none;
      border: 1px solid rgba(0,0,0,0.15);
      border-radius: var(--radius-sm);
      font-size: 11px;
      font-weight: 700;
      color: var(--text-mid);
      padding: 4px 10px;
      cursor: pointer;
      margin-top: 4px;
      width: 100%;
      text-align: left;
      transition: background 0.12s;
    }
    .items-toggle:hover { background: rgba(0,0,0,0.05); }

    .items-list {
      display: none;
      margin-top: 6px;
      font-size: 11px;
      color: var(--text-mid);
      line-height: 1.7;
      background: rgba(255,255,255,0.6);
      border-radius: var(--radius-sm);
      padding: 8px 10px;
    }
    .items-list.open { display: block; }

    /* ── Confirm modal ── */
    #confirmModal {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }
    #confirmModal.open { display: flex; }

    .confirm-box {
      background: #fff;
      border-radius: var(--radius-md);
      padding: 28px 32px;
      max-width: 380px;
      width: 90%;
      text-align: center;
      box-shadow: 0 8px 40px rgba(0,0,0,0.2);
    }

    .confirm-box .confirm-icon { font-size: 40px; margin-bottom: 10px; }

    .confirm-box h3 {
      font-family: var(--font-main);
      font-size: 16px;
      font-weight: 900;
      letter-spacing: 0.5px;
      margin-bottom: 8px;
    }

    .confirm-box p {
      font-size: 13px;
      color: var(--text-mid);
      margin-bottom: 20px;
    }

    .confirm-actions {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .confirm-actions button {
      padding: 10px 24px;
      border: none;
      border-radius: var(--radius-sm);
      font-family: var(--font-main);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.4px;
      cursor: pointer;
      transition: background 0.15s, transform 0.12s;
    }

    .confirm-actions button:hover { transform: translateY(-1px); }

    .btn-cancel-confirm {
      background: #ECEFF1;
      color: var(--text-mid);
    }
    .btn-cancel-confirm:hover { background: #CFD8DC; }

    .btn-confirm-yes-blue  { background: #1565C0; color: #fff; }
    .btn-confirm-yes-blue:hover  { background: #0D47A1; }

    .btn-confirm-yes-green { background: #2E7D32; color: #fff; }
    .btn-confirm-yes-green:hover { background: #1B5E20; }

    /* Scrollable done section */
    .done-scroll {
      max-height: 320px;
      overflow-y: auto;
      display: flex;
      flex-wrap: wrap;
      gap: 14px;
      padding-bottom: 4px;
    }

    .done-scroll::-webkit-scrollbar { width: 5px; }
    .done-scroll::-webkit-scrollbar-thumb { background: #AED581; border-radius: 10px; }

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


<!-- ══════════════════════════════════════════════════
     SECTION 1 — AVAILABLE DELIVERIES
══════════════════════════════════════════════════ -->
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

      // Fetch items for this order
      $oid   = intval($order['order_id']);
      $items = $conn->query("SELECT * FROM v_order_items_full WHERE order_id = $oid");
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

      <!-- Items toggle -->
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


<!-- ══════════════════════════════════════════════════
     SECTION 2 — MY ACTIVE DELIVERIES
══════════════════════════════════════════════════ -->
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

      $oid   = intval($order['order_id']);
      $items = $conn->query("SELECT * FROM v_order_items_full WHERE order_id = $oid");
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


<!-- ══════════════════════════════════════════════════
     SECTION 3 — DELIVERED (HISTORY)
══════════════════════════════════════════════════ -->
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

      $oid   = intval($order['order_id']);
      $items = $conn->query("SELECT * FROM v_order_items_full WHERE order_id = $oid");
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
          <?= isset($order['updated_at']) ? date('M d, g:i A', strtotime($order['updated_at'])) : date('M d, g:i A', strtotime($order['created_at'])); ?>
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
<div id="confirmModal">
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


<script>
// ── Toggle items list ──────────────────────────────
function toggleItems(btn) {
  const list = btn.nextElementSibling;
  const open = list.classList.toggle('open');
  btn.textContent = open ? '▾ Hide Items' : '▸ View Items';
}

// ── Confirm modal helpers ──────────────────────────
let pendingAction = null;

function openConfirm(icon, title, message, btnClass, btnLabel, action) {
  document.getElementById('confirmIcon').textContent    = icon;
  document.getElementById('confirmTitle').textContent   = title;
  document.getElementById('confirmMessage').textContent = message;

  const yesBtn = document.getElementById('confirmYesBtn');
  yesBtn.className = btnClass;
  yesBtn.textContent = btnLabel;
  yesBtn.onclick = action;

  document.getElementById('confirmModal').classList.add('open');
}

function closeConfirm() {
  document.getElementById('confirmModal').classList.remove('open');
  pendingAction = null;
}

// ── Accept a delivery ──────────────────────────────
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
    if (data === 'success') {
      location.reload();
    } else {
      alert('Error accepting order: ' + data);
    }
  })
  .catch(err => alert('Network error: ' + err));
}

// ── Mark as delivered ──────────────────────────────
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
    if (data === 'success') {
      location.reload();
    } else {
      alert('Error updating order: ' + data);
    }
  })
  .catch(err => alert('Network error: ' + err));
}

// ── Close confirm on backdrop click ───────────────
document.getElementById('confirmModal').addEventListener('click', function(e) {
  if (e.target === this) closeConfirm();
});
</script>

</body>
</html>