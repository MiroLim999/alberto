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
  SELECT p.pizza_id, p.pizza_name, c.category_name AS category,
         p.ingredients, p.image_path
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
  <title>Alberto's Pizza | Home</title>
  <link rel="stylesheet" href="css/style.css">
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

    <label>Address</label>
    <input type="text" id="address" placeholder="Enter exact address">

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

<script src="js/home.js"></script>
</body>
</html>