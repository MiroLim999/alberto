<?php
session_start();
include "db_connect.php";

$branches = $conn->query("SELECT * FROM branches ORDER BY branch_name ASC");

$user = null;

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $result = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'");
    $user = $result->fetch_assoc();
}

$menuQuery = "
  SELECT pizza_id, pizza_name, category, ingredients, image_path
  FROM v_pizzas_full
  ORDER BY category, pizza_name
";
$menuResult = $conn->query($menuQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | Home</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="index-page"
  data-logged="<?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>"
  data-username="<?= isset($user['username']) ? $user['username'] : ''; ?>"
  data-mobile="<?= isset($user['mobile_number']) ? $user['mobile_number'] : ''; ?>"
  data-email="<?= isset($user['email']) ? $user['email'] : ''; ?>"
>

<!-- NAVBAR -->
<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">

  <div class="nav-links">
    <a href="index.php">HOME</a>

    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="my_orders.php"><i class="fa-solid fa-receipt" style="margin-right:4px;"></i>My Orders</a>
      <a href="profile_customer.php"><?= $_SESSION['username']; ?></a>
      <a href="logout.php">LOG OUT</a>
    <?php else: ?>
      <a href="signup.php">SIGN UP</a>
      <a href="login.php">LOG IN</a>
    <?php endif; ?>
  </div>
</header>

<!-- MAIN CONTENT -->
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
      $pizzaDropdownQuery = "SELECT DISTINCT pizza_name FROM pizzas ORDER BY pizza_name";
      $pizzaDropdownResult = $conn->query($pizzaDropdownQuery);
      while ($row = $pizzaDropdownResult->fetch_assoc()) {
        $stockRow = $conn->query(
          "SELECT stock FROM pizzas WHERE pizza_name='" . $conn->real_escape_string($row['pizza_name']) . "' LIMIT 1"
        )->fetch_assoc();
        $stock = $stockRow ? intval($stockRow['stock']) : 0;
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

    <label>Stock Status:</label>
    <input type="text" id="stockStatus" readonly value="Available" style="color: var(--green-light);">

    <label>Quantity:</label>
    <input type="number" id="qty" min="1" value="1" onchange="updatePrice()">

    <label>Current Price:</label>
    <input type="text" id="price" readonly value="0">

    <div class="btn-row">
      <button onclick="clearPizzaForm()">CLEAR</button>
      <button onclick="addToOrder()">ADD</button>
    </div>

    <!-- ORDER TABLE -->
    <h3>Order</h3>
    <table id="orderTable">
      <tr>
        <th>PIZZA</th><th>SIZE</th><th>CHEESE</th>
        <th>BASE PRICE</th><th>QNTY</th><th>OVERALL</th><th>ACTION</th>
      </tr>
    </table>

    <label style="margin-top:15px; font-weight: bold;">TOTAL:</label>
    <input type="text" id="totalAmount" value="0.00" readonly style="width:100%; margin-top:5px;">

    <button class="clear-order" onclick="clearOrder()">CLEAR ORDER</button>

    <!-- CUSTOMER INFO -->
    <h3>Customer Info</h3>

    <label>Username</label>
    <input type="text"
       id="customerName"
       placeholder="e.g. Juan Dela Cruz"
       value="<?= isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"
       <?= isset($_SESSION['user_id']) ? 'readonly' : '' ?>>

    <label>Mobile Number</label>
    <input
      type="text"
      id="contact"
      placeholder="e.g. 09123456789"
      value="<?= isset($user['mobile_number']) ? $user['mobile_number'] : ''; ?>"
      oninput="restrictMobileNumber(this)"
      <?= isset($_SESSION['user_id']) ? 'readonly' : '' ?>
    >

    <label>Email (Optional)</label>
    <input type="email"
       id="optionalEmail"
       placeholder="e.g. juan@gmail.com"
       value="<?= isset($user['email']) ? $user['email'] : ''; ?>"
       <?= isset($_SESSION['user_id']) ? 'readonly' : '' ?>>

    <!-- ORDER METHODS -->
    <h3>Order Methods</h3>

    <label>Branch</label>
    <select id="branch">
      <option disabled selected hidden>Choose a branch near you...</option>
      <?php while ($row = $branches->fetch_assoc()): ?>
        <option value="<?= $row['branch_id']; ?>">
          <?= $row['branch_name'] . ", " . $row['location']; ?>
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

    <div class="btn-row">
      <p id="successMessage" style="display: none; width: 100%; margin-bottom: 0;">
        ✅ Order submitted successfully!
      </p>
      <button onclick="cancelOrder()">CANCEL ORDER</button>
      <button id="finalizeBtn" onclick="finalizeOrder()">FINALIZE ORDER</button>
    </div>

  </section>
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
<div id="validationModal" style="display:none; position:fixed; inset:0; z-index:9998; background:rgba(0,0,0,0.45); align-items:center; justify-content:center; backdrop-filter:blur(3px);">
  <div style="background:#fff; border-radius:14px; padding:28px 32px; max-width:420px; width:90%; box-shadow:0 12px 48px rgba(0,0,0,0.18); text-align:center; animation:popIn 0.25s ease;">
    <span id="vmodalIcon"  style="font-size:44px; display:block; margin-bottom:12px;"></span>
    <div id="vmodalTitle"  style="font-family:var(--font-main); font-size:17px; font-weight:900; color:var(--text-dark); margin-bottom:8px;"></div>
    <div id="vmodalMsg"    style="font-size:13px; color:var(--text-mid); line-height:1.6; margin-bottom:22px;"></div>
    <div id="vmodalActions" style="display:flex; gap:10px; justify-content:center;">
      <button id="vmodalDismiss" onclick="closeValidationModal()"
        style="padding:10px 22px; border:none; border-radius:8px; background:#f0f0f0; color:#555; font-family:var(--font-main); font-size:13px; font-weight:800; cursor:pointer;">
        Dismiss
      </button>
      <button id="vmodalActionBtn" onclick="closeValidationModal()"
        style="padding:10px 22px; border:none; border-radius:8px; background:var(--orange); color:#fff; font-family:var(--font-main); font-size:13px; font-weight:800; cursor:pointer; box-shadow:0 3px 10px rgba(255,107,0,0.3);">
        OK
      </button>
    </div>
  </div>
</div>

<style>
/* Toast system */
#toast-container {
  position: fixed; top: 80px; right: 20px;
  z-index: 99999; display: flex; flex-direction: column;
  gap: 10px; pointer-events: none;
}
.toast {
  display: flex; align-items: flex-start; gap: 12px;
  min-width: 300px; max-width: 380px;
  padding: 14px 16px; border-radius: 10px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.15);
  font-family: var(--font-body); font-size: 13px; line-height: 1.5;
  pointer-events: all; position: relative; overflow: hidden;
  animation: toastIn 0.3s ease;
}
@keyframes toastIn  { from { transform:translateX(120%); opacity:0; } to { transform:translateX(0); opacity:1; } }
@keyframes toastOut { from { transform:translateX(0); opacity:1; } to { transform:translateX(120%); opacity:0; } }
.toast.removing { animation: toastOut 0.3s ease forwards; }
.toast-icon  { font-size:18px; flex-shrink:0; margin-top:1px; }
.toast-body  { flex:1; }
.toast-title { font-weight:700; font-size:13px; margin-bottom:2px; }
.toast-msg   { opacity:.85; font-size:12px; }
.toast-close { background:none; border:none; cursor:pointer; font-size:16px; opacity:.5; padding:0; margin-top:0; line-height:1; flex-shrink:0; transition:opacity .15s; }
.toast-close:hover { opacity:1; }
.toast-progress { position:absolute; bottom:0; left:0; height:3px; border-radius:0 0 10px 10px; animation:toastProgress linear forwards; }
@keyframes toastProgress { from{width:100%;} to{width:0%;} }
.toast-error   { background:#fff5f5; border:1.5px solid #FFCDD2; color:#c62828; }
.toast-error   .toast-progress { background:#c62828; }
.toast-warning { background:#fffbf0; border:1.5px solid #FFE082; color:#c05000; }
.toast-warning .toast-progress { background:#f4a700; }
.toast-success { background:#f1f8e9; border:1.5px solid #AED581; color:#2e7d32; }
.toast-success .toast-progress { background:#2e7d32; }

/* Address field required indicator */
#addressLabel .req-star { color: var(--red); margin-left:2px; display:none; }
#addressLabel.required .req-star { display:inline; }
#addressLabel.required #addressOptional { display:none; }

/* Field error shake */
.field-error {
  border-color: var(--red) !important;
  box-shadow: 0 0 0 3px rgba(198,40,40,0.15) !important;
  animation: shake 0.35s ease;
}
@keyframes shake {
  0%,100% { transform:translateX(0); }
  20%,60%  { transform:translateX(-5px); }
  40%,80%  { transform:translateX(5px); }
}
@keyframes popIn {
  from { transform:scale(0.9); opacity:0; }
  to   { transform:scale(1);   opacity:1; }
}
</style>

<script src="js/home.js"></script>

<script>
// ── Toast system ─────────────────────────────────────────────
function showToast(type, title, msg, duration = 4500) {
  const icons = { error:'❌', warning:'⚠️', success:'✅', info:'ℹ️' };
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <span class="toast-icon">${icons[type]||'ℹ️'}</span>
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

// ── Validation modal ─────────────────────────────────────────
let _vmodalCb = null;
function showValidationModal(icon, title, msg, actionLabel, actionCb, showDismiss = true) {
  document.getElementById('vmodalIcon').textContent  = icon;
  document.getElementById('vmodalTitle').textContent = title;
  document.getElementById('vmodalMsg').textContent   = msg;
  document.getElementById('vmodalActionBtn').textContent = actionLabel;
  document.getElementById('vmodalDismiss').style.display = showDismiss ? '' : 'none';
  _vmodalCb = actionCb;
  const m = document.getElementById('validationModal');
  m.style.display = 'flex';
}
function closeValidationModal() {
  document.getElementById('validationModal').style.display = 'none';
  if (typeof _vmodalCb === 'function') { _vmodalCb(); _vmodalCb = null; }
}
document.getElementById('validationModal').addEventListener('click', function(e) {
  if (e.target === this) closeValidationModal();
});

// ── Field highlight helper ────────────────────────────────────
function highlightField(id) {
  const el = document.getElementById(id);
  if (!el) return;
  el.classList.add('field-error');
  el.addEventListener('input',  () => el.classList.remove('field-error'), { once: true });
  el.addEventListener('change', () => el.classList.remove('field-error'), { once: true });
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  setTimeout(() => el.focus(), 300);
}

// ── Address label: update required/optional based on order type ──
function updateAddressLabel() {
  const orderTypeEl = document.querySelector('input[name="orderType"]:checked');
  const isDelivery  = orderTypeEl && orderTypeEl.value === 'DELIVERY';
  const label       = document.getElementById('addressLabel');
  const addrInput   = document.getElementById('address');
  if (!label) return;
  if (isDelivery) {
    label.classList.add('required');
    addrInput.placeholder = 'Enter delivery address (required)';
  } else {
    label.classList.remove('required');
    addrInput.placeholder = 'Enter address (optional for pick-up)';
  }
}

// Hook order type radios to update label
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('input[name="orderType"]').forEach(r => {
    r.addEventListener('change', updateAddressLabel);
  });
  updateAddressLabel(); // run once on load
});

// ── Override finalizeOrder for index.php (customer page) ─────
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
  const total        = document.getElementById('totalAmount').value;
  const email        = document.getElementById('optionalEmail').value.trim();
  const orderType    = orderTypeEl ? orderTypeEl.value : 'PICK-UP';
  const payment      = paymentEl  ? paymentEl.value  : 'CASH';

  // 1. Items
  if (table.rows.length <= 1) {
    showToast('error', 'No Items', 'Add at least one pizza to your order first.');
    return;
  }

  // 2. Customer name
  if (customerName === '') {
    showToast('warning', 'Name Required', 'Please enter your name before placing the order.');
    highlightField('customerName');
    return;
  }

  // 3. Mobile
  if (mobile === '') {
    showToast('warning', 'Mobile Required', 'Please enter your mobile number.');
    highlightField('contact');
    return;
  }
  if (mobile.length !== 11 || !mobile.startsWith('09')) {
    showToast('error', 'Invalid Mobile', 'Mobile number must be 11 digits and start with 09.');
    highlightField('contact');
    return;
  }

  // 4. Branch
  if (!branch || branch === '0') {
    showToast('warning', 'Branch Required', 'Please select a branch near you.');
    highlightField('branch');
    return;
  }

  // 5. Delivery address — modal with "Fill Address" action
  if (orderType === 'DELIVERY' && address === '') {
    showValidationModal(
      '🛵',
      'Delivery Address Required',
      'You selected DELIVERY but haven\'t entered an address. Please fill in your delivery address to continue.',
      'Fill Address',
      () => highlightField('address'),
      true
    );
    return;
  }

  // 6. Collect items
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

  // 7. Send
  const btn = document.getElementById('finalizeBtn');
  btn.disabled    = true;
  btn.textContent = 'Processing...';

  fetch('save_order.php', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      customer_name: customerName,
      mobile, email, branch, address,
      order_type: orderType, payment,
      total, items,
      is_online: 1,
    }),
  })
  .then(res => { if (!res.ok) throw new Error(`HTTP ${res.status}`); return res.json(); })
  .then(data => {
    if (data.status !== 'success') {
      showToast('error', 'Order Failed', data.message || 'Something went wrong. Please try again.');
      btn.disabled    = false;
      btn.textContent = 'FINALIZE ORDER';
      return;
    }

    showToast('success', 'Order Placed', `Order #${data.order_id} submitted. Awaiting cashier confirmation.`, 6000);

    showReceipt(
      data.order_id, customerName, mobile, email,
      branchText, address, orderType, payment, total, items,
      true
    );

    const successMsg = document.getElementById('successMessage');
    if (successMsg) successMsg.style.display = 'block';
    setTimeout(() => cancelOrder(), 1000);
    setTimeout(() => { if (successMsg) successMsg.style.display = 'none'; }, 4000);

    btn.disabled    = false;
    btn.textContent = 'FINALIZE ORDER';
  })
  .catch(err => {
    showToast('error', 'Network Error', `Could not reach the server. Check your connection. (${err.message})`);
    btn.disabled    = false;
    btn.textContent = 'FINALIZE ORDER';
  });
}
</script>