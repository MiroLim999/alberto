// CAN BE REMOVED
function loginAlert() {
  alert("LOG IN page does not exist yet. Please be patient. Thank you!");
}

function signupAlert() {
  alert("SIGN UP page does not exist yet. Please be patient. Thank you!");
}

function applyPizzaSelection(img) {
  if (!img || !img.dataset) return;

  const allImages = document.querySelectorAll(".menu-img");

  // ✅ Remove active from all pizzas
  allImages.forEach(i => i.classList.remove("active"));

  // ✅ Add active to selected pizza
  img.classList.add("active");

  const preview = document.getElementById("bigPreview");
  const nameField = document.getElementById("pizzaName");
  const ingredientsField = document.getElementById("ingredients");
  const dropdown = document.getElementById("pizzaSelect");

  preview.src = img.src;
  nameField.textContent = img.dataset.name;
  ingredientsField.textContent = img.dataset.ingredients;

  
  if (dropdown) {
    dropdown.value = img.dataset.name;
  }

  // ✅ Force correct price calculation AFTER everything updates
  updatePrice();

}


function updatePrice() {

  const select = document.getElementById("pizzaSelect");
  const selectedOption = select.options[select.selectedIndex];
  const pizza = select.value;

  const stockValue = parseInt(selectedOption.getAttribute("data-stock")) || 0;

  // ✅ Update hidden stock field (cashier.php uses this)
  const stockField = document.getElementById("stock");
  if (stockField) stockField.value = stockValue;

  // ✅ Update Stock Status display (index.php uses this)
  const stockStatus = document.getElementById("stockStatus");
  const qty = parseInt(document.getElementById("qty").value) || 1;

  const sizeEl = document.querySelector('input[name="size"]:checked');
  const cheeseEl = document.querySelector('input[name="cheese"]:checked');

  if (!pizza || !sizeEl || !cheeseEl) {
    document.getElementById("price").value = 0;
    if (stockStatus) {
      stockStatus.value = "—";
      stockStatus.style.color = "gray";
    }
    checkFinalizeBtn();
    return;
  }

  // ✅ Stock Status: compare qty vs stock
  if (stockStatus) {
    if (qty > stockValue) {
      stockStatus.value = "Out of Stock";
      stockStatus.style.color = "red";
    } else {
      stockStatus.value = "Available";
      stockStatus.style.color = "green";
    }
  }

  // ✅ ADD button: disable if out of stock
  // Supports both cashier.php (id="addBtn") and index.php (onclick="addToOrder()")
  const addButton =
    document.getElementById("addBtn") ||
    document.querySelector('button[onclick="addToOrder()"]');
  if (addButton) {
    addButton.disabled = (qty > stockValue || stockValue === 0);
  }

  const size = sizeEl.value;
  const cheese = cheeseEl.value;

  fetch("get_price.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `pizza=${encodeURIComponent(pizza)}&size=${size}&cheese=${cheese}`
  })
  .then(res => res.text())
  .then(price => {
    document.getElementById("price").value = (price * qty).toFixed(2);
  })
  .catch(err => console.error(err));

  checkFinalizeBtn();
}


function addToOrder() {
  const pizza = document.getElementById("pizzaSelect").value;

  if (!pizza) {
    alert("Please select a pizza first.");
    return;
  }

  // ✅ Guard: block if stock status shows Out of Stock
  const stockStatus = document.getElementById("stockStatus");
  if (stockStatus && stockStatus.value === "Out of Stock") {
    alert("This pizza is out of stock. Please choose a different pizza or quantity.");
    return;
  }

  const sizeEl = document.querySelector('input[name="size"]:checked');
  const cheeseEl = document.querySelector('input[name="cheese"]:checked');

  const size = sizeEl ? sizeEl.value : "";
  const cheese = cheeseEl ? cheeseEl.value : "";

  const qty = parseInt(document.getElementById("qty").value);
  const totalPrice = parseFloat(document.getElementById("price").value);

  if (!size || !cheese) {
    alert("Please select size and cheese.");
    return;
  }

  const basePrice = totalPrice / qty;

  const table = document.getElementById("orderTable");

  const row = table.insertRow();

  row.innerHTML = `
    <td>${pizza}</td>
    <td>${size}"</td>
    <td>${cheese}</td>
    <td>${basePrice.toFixed(2)}</td>
    <td>${qty}</td>
    <td>${totalPrice.toFixed(2)}</td>
    <td><button onclick="removeRow(this)">DEL</button></td>
  `;

  updateTotal();
}

function removeRow(button) {
  const row = button.parentElement.parentElement;
  row.remove();

  updateTotal();   // ✅ update after deleting
}

function clearOrder() {
  document.getElementById("orderTable").innerHTML =
    "<tr><th>PIZZA</th><th>SIZE</th><th>CHEESE</th><th>BASE PRICE</th><th>QNTY</th><th>OVERALL</th><th>ACTION</th></tr>";
  
  document.getElementById("totalAmount").value = "0.00";
}

function cancelAll() {
  clearOrder();
  alert("Order cancelled.");
}

function termsAlert() {
  alert("Terms & Conditions page does not exist yet.\nPlease check again later.");
}

function privacyAlert() {
  alert("Privacy Notice page does not exist yet.\nPlease check again later.");
}

function createAccount() {
  const username = document.getElementById("username").value.trim();
  const password = document.getElementById("password").value.trim();
  const mobile = document.getElementById("mobileNumber").value.trim();
  const email = document.getElementById("emailInput").value.trim();

  const dobMonth = document.getElementById("dobMonth").value;
  const dobDay = document.getElementById("dobDay").value;
  const dobYear = document.getElementById("dobYear").value;

  const genderInput = document.querySelector('input[name="gender"]:checked');

  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  // ✅ Check required fields
  if (
    username === "" ||
    password === "" ||
    mobile === "" ||
    email === "" ||
    dobMonth === "" ||
    dobDay === "" ||
    dobYear === "" ||
    !genderInput
  ) {
    alert("Please complete all required fields before creating an account.");
    return;
  }

  // ✅ Password basic rule (minimum length)
  if (password.length < 6) {
    alert("Password must be at least 6 characters long.");
    return;
  }

  // ✅ Mobile Number validation
  if (mobile.length !== 11 || !mobile.startsWith("09")) {
    alert("Please enter a valid Philippine mobile number (11 digits starting with 09).");
    return;
  }

  // ✅ Email validation
  if (!emailPattern.test(email)) {
    alert("Please enter a valid Email Address.");
    return;
  }

  // ✅ SUCCESS (Prototype behavior)
  alert(
    "Account created successfully!\n\n" +
    "Username: " + username + "\n" +
    "Gender: " + genderInput.value + "\n" +
    "Date of Birth: " + dobMonth + "/" + dobDay + "/" + dobYear
  );
}

/* ===== DATE OF BIRTH DROPDOWNS ===== */
function populateDOB() {
  const monthSelect = document.getElementById("dobMonth");
  const daySelect = document.getElementById("dobDay");
  const yearSelect = document.getElementById("dobYear");

  if (!monthSelect || !daySelect || !yearSelect) return;

  // ----- Months -----
  const months = [
    "January", "February", "March", "April", "May", "June",
    "July", "August", "September", "October", "November", "December"
  ];

  months.forEach((month, index) => {
    const option = document.createElement("option");
    option.value = index + 1;
    option.text = month;
    monthSelect.appendChild(option);
  });

  // ----- Years (Current year back to 100 years) -----
  const currentYear = new Date().getFullYear();

  for (let year = currentYear; year >= currentYear - 100; year--) {
    const option = document.createElement("option");
    option.value = year;
    option.text = year;
    yearSelect.appendChild(option);
  }

  // ----- Update days when month or year changes -----
  monthSelect.addEventListener("change", updateDays);
  yearSelect.addEventListener("change", updateDays);

  function updateDays() {
    daySelect.innerHTML = '<option value="" disabled selected hidden>Day</option>';

    const month = parseInt(monthSelect.value);
    const year = parseInt(yearSelect.value);

    if (!month || !year) return;

    const daysInMonth = new Date(year, month, 0).getDate();

    for (let day = 1; day <= daysInMonth; day++) {
      const option = document.createElement("option");
      option.value = day;
      option.text = day;
      daySelect.appendChild(option);
    }
  }
}

function restrictMobileNumber(input) {
  // Remove any non-numeric characters
  input.value = input.value.replace(/\D/g, '');

  // Limit to 11 digits
  if (input.value.length > 11) {
    input.value = input.value.slice(0, 11);
  }
  if (input.value.length === 11 && !input.value.startsWith("09")) {
    alert("Mobile number must start with 09.");
    input.value = "";
  }
}

  // ----- Email Validation -----

function validateEmail(input) {
  const emailError = document.getElementById("emailError");

  // Simple but reliable email regex
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  if (input.value === "") {
    input.classList.remove("input-error");
    emailError.style.display = "none";
    return;
  }

  if (!emailPattern.test(input.value)) {
    input.classList.add("input-error");
    emailError.style.display = "block";
  } else {
    input.classList.remove("input-error");
    emailError.style.display = "none";
  }
}

  // ----- LOGIN Alerts -----

function loginUser() {
  const username = document.getElementById("loginUsername").value.trim();
  const password = document.getElementById("loginPassword").value.trim();

  if (username === "" || password === "") {
    alert("Please enter both username and password to log in.");
    return;
  }

  // Prototype behavior (no database yet)
  alert(
    "Login successful!\n\n" +
    "Username: " + username
  );
}

  // ----- LOGOUT Alerts -----
  
function logoutCustomer() {
  alert("You have been logged out successfully.");
  window.location.href = "index.html";
}

// ✅ CHECK FINALIZE BUTTON — re-evaluates all 4 conditions every time anything changes
function checkFinalizeBtn() {
  const btn = document.getElementById("finalizeBtn");
  if (!btn) return;  // not on this page

  const table       = document.getElementById("orderTable");
  const username    = document.getElementById("customerName")?.value.trim() ?? "";
  const mobile      = document.getElementById("contact")?.value.trim() ?? "";
  const branch      = document.getElementById("branch")?.value ?? "";
  const address     = document.getElementById("address")?.value.trim() ?? "";
  const stockStatus = document.getElementById("stockStatus");

  // Condition 1: at least 1 pizza in the order table
  const hasItems = table && table.rows.length > 1;

  // Condition 2: username and mobile are filled
  const hasCustomer = username !== "" && mobile !== "";

  // Condition 3: branch is selected (not the placeholder)
  const hasBranch = branch !== "" && branch !== "0";

  // Condition 4: if Delivery is selected, address must be filled
  // NOTE: address validation is intentionally NOT checked here —
  // it's handled in finalizeOrder() with a proper modal popup.
  // Disabling the button here would prevent the modal from showing.
  const needsAddress = false; // always allow click; modal handles it
  const hasAddress   = true;

  // Bonus: stock status must not be Out of Stock (only on index.php)
  const stockOk = !stockStatus || stockStatus.value !== "Out of Stock";

  const allGood = hasItems && hasCustomer && hasBranch && hasAddress && stockOk;

  btn.disabled = !allGood;

  // Visual hint: show a subtle tooltip-style title so user knows why it's disabled
  if (!allGood) {
    const reasons = [];
    if (!hasItems)    reasons.push("Add at least 1 pizza");
    if (!hasCustomer) reasons.push("Fill in Username and Mobile Number");
    if (!hasBranch)   reasons.push("Select a Branch");
    if (!stockOk)     reasons.push("Selected pizza is Out of Stock");
    btn.title = reasons.join(" • ");
  } else {
    btn.title = "";
  }

  // ✅ Also keep ADD button in sync with stock status
  const addButton =
    document.getElementById("addBtn") ||
    document.querySelector('button[onclick="addToOrder()"]');
  if (addButton && stockStatus) {
    addButton.disabled = (stockStatus.value === "Out of Stock");
  }
}

document.addEventListener("DOMContentLoaded", function () {

  const pizzaImages = document.querySelectorAll(".menu-img");
  const pizzaSelect = document.getElementById("pizzaSelect");

  // ✅ IMAGE CLICK → MENU UPDATE
  pizzaImages.forEach(img => {
    img.addEventListener("click", function () {
      applyPizzaSelection(img);
    });
  });

  // ✅ DROPDOWN → MENU UPDATE
  if (pizzaSelect) {
    pizzaSelect.addEventListener("change", function () {
      const selectedName = pizzaSelect.value;
      if (!selectedName) return;

      const match = Array.from(pizzaImages).find(
        img => img.dataset.name === selectedName
      );

      if (match) {
        applyPizzaSelection(match);
      }
    });
  }

  populateDOB();

  // ✅ HOOK: re-check finalize button whenever these fields change
  const watchIds = ["customerName", "contact", "branch", "address", "qty"];
  watchIds.forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener("input", checkFinalizeBtn);
    if (el) el.addEventListener("change", checkFinalizeBtn);
  });

  // ✅ HOOK: order type radio buttons (PICK-UP / DELIVERY)
  document.querySelectorAll('input[name="orderType"]').forEach(r => {
    r.addEventListener("change", checkFinalizeBtn);
  });

  // ✅ Run once on load so button starts in correct state
  checkFinalizeBtn();

});

function clearPizzaForm() {
  // ✅ Reset pizza dropdown
  const pizzaSelect = document.getElementById("pizzaSelect");
  if (pizzaSelect) {
    pizzaSelect.value = "";
  }

  // ✅ Reset size (default = 9")
  const sizeOptions = document.querySelectorAll('input[name="size"]');
  sizeOptions.forEach(radio => {
    radio.checked = radio.value === "9";
  });

  // ✅ Reset cheese (default = Quickmelt)
  const cheeseOptions = document.querySelectorAll('input[name="cheese"]');
  cheeseOptions.forEach(radio => {
    radio.checked = radio.value === "Quickmelt";
  });
  const stockField = document.getElementById("stock");
  if (stockField) stockField.value = "0";

  const stockStatus = document.getElementById("stockStatus");
  if (stockStatus) {
    stockStatus.value = "Available";
    stockStatus.style.color = "green";
  }

  // ✅ Reset quantity
  document.getElementById("qty").value = 1;

  // ✅ Reset price
  document.getElementById("price").value = 0;

  // ✅ Reset preview section
  document.getElementById("bigPreview").src = "menu/Default.png";
  document.getElementById("pizzaName").textContent = "Select a Pizza";
  document.getElementById("ingredients").textContent = "Ingredients will appear here";

  // ✅ Remove active highlight from menu images
  const images = document.querySelectorAll(".menu-img");
  images.forEach(img => img.classList.remove("active"));

  // Re-evaluate finalize button after clearing
  checkFinalizeBtn();
}

// CALCULATE TOTAL
function updateTotal() {
  const table = document.getElementById("orderTable");
  let total = 0;

  // start at 1 to skip table header row
  for (let i = 1; i < table.rows.length; i++) {
    const overallCell = table.rows[i].cells[5]; // OVERALL column
    const value = parseFloat(overallCell.innerText);

    if (!isNaN(value)) {
      total += value;
    }
  }

  document.getElementById("totalAmount").value = total.toFixed(2);

  const cashierTotal = document.getElementById("cashierTotal");
  if (cashierTotal) cashierTotal.value = "₱" + total.toFixed(2);

  calculateChange();
  checkFinalizeBtn();
}

// VISIBILITY TOGGLE PROFILE_CUSTOMER
function toggleProfilePassword() {
  const input = document.getElementById("profilePassword");

  if (input.type === "password") {
    input.type = "text";
  } else {
    input.type = "password";
  }
}

function cancelOrder() {

  // ✅ 1. RESET PIZZA ORDER

  document.getElementById("pizzaSelect").value = "";

  // Size → default 9"
  document.querySelectorAll('input[name="size"]').forEach(r => {
    r.checked = (r.value === "9");
  });

  // Cheese → default Quickmelt
  document.querySelectorAll('input[name="cheese"]').forEach(r => {
    r.checked = (r.value === "Quickmelt");
  });

  document.getElementById("qty").value = 1;
  document.getElementById("price").value = 0;


  // ✅ 2. RESET ORDER METHODS

  document.getElementById("branch").selectedIndex = 0;
  document.getElementById("address").value = "";

  // Order → default PICK-UP
  document.querySelectorAll('input[name="orderType"]').forEach(r => {
    r.checked = (r.nextSibling.textContent.trim() === "PICK-UP");
  });

  // Payment → default CASH
  document.querySelectorAll('input[name="payment"]').forEach(r => {
    r.checked = (r.nextSibling.textContent.trim() === "CASH");
  });


  // ✅ 3. RESET CUSTOMER INFO (IMPORTANT)

  // These values come from PHP (we pass them into JS)
  const isLoggedIn = document.body.dataset.logged === "true";

  if (isLoggedIn) {
    document.getElementById("customerName").value = document.body.dataset.username;
    document.getElementById("contact").value = document.body.dataset.mobile;
    document.getElementById("optionalEmail").value = document.body.dataset.email;
  } else {
    document.getElementById("customerName").value = "";
    document.getElementById("contact").value = "";
    document.getElementById("optionalEmail").value = "";
  }

  // ✅ 4. CLEAR ORDER TABLE

  const table = document.getElementById("orderTable");

  // keep header row (index 0), delete the rest
  while (table.rows.length > 1) {
    table.deleteRow(1);
  }

  // ✅ reset total amount
  document.getElementById("totalAmount").value = "0.00";

  // ✅ RESET CASHIER FIELDS (cashier.php only)
  const cashierTotal  = document.getElementById("cashierTotal");
  const amtReceived   = document.getElementById("amountReceived");
  const changeAmount  = document.getElementById("changeAmount");
  if (cashierTotal)  cashierTotal.value  = "₱0.00";
  if (amtReceived)   amtReceived.value   = "";
  if (changeAmount)  changeAmount.value  = "₱0.00";

  // ✅ RESET STOCK STATUS (index.php only)
  const stockStatus = document.getElementById("stockStatus");
  if (stockStatus) {
    stockStatus.value = "Available";
    stockStatus.style.color = "green";
  }

  // Re-evaluate finalize button
  checkFinalizeBtn();
}

function finalizeOrder() {

  // ✅ Detect which page we're on
  const isOnlinePage = document.body.classList.contains("index-page");

  // ✅ COLLECT COMMON VALUES
  let customer_name = document.getElementById("customerName").value.trim();
  const mobile      = document.getElementById("contact").value.trim();
  const email       = document.getElementById("optionalEmail").value.trim();

  const branchSelect = document.getElementById("branch");
  const branch       = branchSelect.value;
  const branchText   = branchSelect.selectedIndex >= 0 ? branchSelect.options[branchSelect.selectedIndex].text : '';

  const address      = document.getElementById("address").value.trim();
  const order_type   = document.querySelector('input[name="orderType"]:checked').value;
  const payment      = document.querySelector('input[name="payment"]:checked').value;
  const total        = document.getElementById("totalAmount").value;
  const table        = document.getElementById("orderTable");

  // ✅ SHARED VALIDATION (both pages)
  if (table.rows.length <= 1) {
    alert("Please add at least one pizza to your order.");
    return;
  }
  if (customer_name === "" || mobile === "") {
    alert("Please fill in your Username and Mobile Number.");
    return;
  }
  if (!branch) {
    alert("Please select a branch.");
    return;
  }
  if (order_type === "DELIVERY" && address === "") {
    // Use modal if available (index.php / cashier.php inject it),
    // otherwise fall back to alert
    if (typeof showValidationModal === 'function') {
      showValidationModal(
        '🛵',
        'Delivery Address Required',
        'You selected DELIVERY but haven\'t entered an address. Please fill in your delivery address to continue.',
        'Fill Address',
        () => {
          const el = document.getElementById('address');
          if (el) { el.scrollIntoView({ behavior:'smooth', block:'center' }); setTimeout(() => el.focus(), 300); }
        },
        true
      );
    } else {
      alert("Please enter your delivery address.");
    }
    return;
  }

  // ✅ CASHIER-ONLY VALIDATION (cashier.php)
  if (!isOnlinePage) {
    const received   = parseFloat(document.getElementById("amountReceived").value) || 0;
    const totalValue = parseFloat(total) || 0;

    if (received < totalValue) {
      alert("Amount received is insufficient.");
      return;
    }

    // Cashier walk-in: name optional
    if (order_type === "walkin" && customer_name === "") {
      customer_name = "Guest";
    }
  }

  // ✅ COLLECT ITEMS FROM TABLE
  let items = [];
  for (let i = 1; i < table.rows.length; i++) {
    const row = table.rows[i];
    items.push({
      pizza:    row.cells[0].innerText,
      size:     row.cells[1].innerText,
      cheese:   row.cells[2].innerText,
      price:    parseFloat(row.cells[3].innerText),
      quantity: parseInt(row.cells[4].innerText),
      total:    parseFloat(row.cells[5].innerText)
    });
  }

  const btn = document.getElementById("finalizeBtn");
  btn.disabled    = true;
  btn.textContent = "Processing...";

  // ✅ SEND TO save_order.php
  // Online orders (index.php) are saved with status="pending"
  // Cashier orders are saved with status="completed"
  fetch("save_order.php", {
    method:  "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      customer_name,
      mobile,
      email,
      branch,
      address,
      order_type,
      payment,
      total,
      items,
      is_online: isOnlinePage ? 1 : 0   // ✅ tells PHP which status to use
    })
  })
  .then(res => res.json())
  .then(data => {

    if (data.status !== "success") {
      alert("Error submitting order: " + (data.message || "Unknown error"));
      btn.disabled    = false;
      btn.textContent = "FINALIZE ORDER";
      return;
    }

    // ✅ SHOW SUCCESS MESSAGE
    const successMsg = document.getElementById("successMessage");
    if (successMsg) successMsg.style.display = "block";

    // ✅ SHOW RECEIPT MODAL
    showReceipt(
      data.order_id, customer_name, mobile, email,
      branchText, address, order_type, payment, total, items,
      isOnlinePage   // ✅ pass flag so receipt shows "PENDING" note for online
    );

    // ✅ CLEAR FORM after short delay
    setTimeout(() => cancelOrder(), 1000);

    // ✅ HIDE SUCCESS MESSAGE after a few seconds
    setTimeout(() => {
      if (successMsg) successMsg.style.display = "none";
    }, 4000);

    btn.disabled    = false;
    btn.textContent = "FINALIZE ORDER";

    // ✅ CASHIER: if processing a pending online order → mark it completed
    if (!isOnlinePage && window.currentProcessingOrderId) {
      fetch("complete_order.php", {
        method:  "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body:    `order_id=${window.currentProcessingOrderId}`
      });
      window.currentProcessingOrderId = null;
    }

  })
  .catch(err => {
    alert("Network error: " + err);
    btn.disabled    = false;
    btn.textContent = "FINALIZE ORDER";
  });

}

function showReceipt(order_id, name, mobile, email, branch, address, order_type, payment, total, items, isOnline = false) {

  // ✅ Pending badge shown only for online orders
  const pendingBadge = isOnline ? `
    <div style="
      background: #fff3cd;
      border: 1px solid #ffc107;
      border-radius: 6px;
      padding: 10px 14px;
      margin-bottom: 14px;
      color: #856404;
      font-size: 13px;
    ">
      ⏳ <strong>Order Status: PENDING</strong><br>
      Your order has been received and is waiting to be processed by the cashier.
      You will be notified once it is confirmed.
    </div>` : "";

  let content = `
  <div style="text-align:center;">
    <img src="logo/Alberto's Pizza.png" style="width:120px; margin-bottom:10px;">
    <h3>Order Receipt</h3>
  </div>

  ${pendingBadge}

  <p><strong>Order ID:</strong> ${order_id}</p>
  <p><strong>Name:</strong> ${name}</p>
  <p><strong>Mobile:</strong> ${mobile}</p>
  <p><strong>Email:</strong> ${email || "—"}</p>
  <p><strong>Branch:</strong> ${branch}</p>
  <p><strong>Address:</strong> ${address || "—"}</p>
  <p><strong>Order Type:</strong> ${order_type}</p>
  <p><strong>Payment:</strong> ${payment}</p>

  <hr>
  <h4>Items:</h4>
`;

  items.forEach(item => {
    content += `
      <p>${item.pizza} (${item.size}", ${item.cheese}) x${item.quantity} — ₱${parseFloat(item.total).toFixed(2)}</p>
    `;
  });

  content += `
  <hr>
  <p style="font-size:15px;"><strong>Total: ₱${parseFloat(total).toFixed(2)}</strong></p>
`;

  // ✅ GCASH QR CODE — shown only when payment method is GCash
  if (payment === "ONLINE") {
    content += `
  <hr>
  <div style="text-align:center; margin-top:14px;">
    <p style="font-weight:bold; font-size:14px; margin-bottom:8px;">📱 Scan to Pay via Online</p>
    <img
      src="menu/PAYMENT-SUCCESS-1024.jpeg"
      alt="GCash QR Code"
      style="width:220px; border:2px solid #007bff; border-radius:10px; padding:6px;"
    >
    <p style="font-size:12px; color:#555; margin-top:8px;">
      Please screenshot this QR and show it to the cashier upon payment.
    </p>
  </div>
`;
  }

  document.getElementById("receiptContent").innerHTML = content;
  document.getElementById("receiptModal").style.display = "block";
}

function closeModal() {
  document.getElementById("receiptModal").style.display = "none";

  // ✅ refresh page to update pending orders
  location.reload();
}

function printReceipt() {
  const content = document.getElementById("receiptContent").innerHTML;

  const win = window.open("", "", "width=600,height=600");
  win.document.write(`
  <html>
    <head>
      <title>Receipt</title>
      <style>
        body {
          font-family: Arial;
          text-align: left;
          padding: 20px;
        }
        .logo {
          text-align: center;
        }
        .logo img {
          width: 120px;
          margin-bottom: 10px;
        }
      </style>
    </head>
    <body>
      ${content}
    </body>
  </html>
`);
  
  win.document.close();
  win.print();
}

function slideLeft() {
  const container = document.getElementById("pendingContainer");
  container.scrollBy({ left: -300, behavior: 'smooth' });
}

function slideRight() {
  const container = document.getElementById("pendingContainer");
  container.scrollBy({ left: 300, behavior: 'smooth' });
}

document.addEventListener("DOMContentLoaded", function() {
  updateTotal();
});


function processOrder(orderId) {

  // ── STEP 1: Check stock before doing anything ────────────────
  fetch(`check_stock.php?order_id=${orderId}`)
    .then(res => res.json())
    .then(data => {

      // ── OUT OF STOCK: show modal and stop ──────────────────────
      if (data.status === "out_of_stock") {
        showOutOfStockModal(data.order, data.items, data.out_of_stock);
        return;
      }

      // ── STOCK OK: load order into the cashier form ─────────────
      window.currentProcessingOrderId = orderId;

      const order = data.order;
      const items = data.items;

      // ✅ 1. CUSTOMER INFO
      document.getElementById("customerName").value = order.customer_name;
      document.getElementById("contact").value = order.mobile_number;
      document.getElementById("optionalEmail").value = order.email;

      // ✅ 2. ORDER METHODS
      const branchSelect = document.getElementById("branch");
      branchSelect.value = order.branch_id;

      document.getElementById("address").value = order.address;

      // Order type
      document.querySelectorAll('input[name="orderType"]').forEach(r => {
        r.checked = r.nextSibling.textContent.trim() === order.order_type;
      });

      // Payment
      document.querySelectorAll('input[name="payment"]').forEach(r => {
        r.checked = r.nextSibling.textContent.trim() === order.payment_method;
      });

      // ✅ 3. CLEAR EXISTING TABLE
      const table = document.getElementById("orderTable");
      while (table.rows.length > 1) {
        table.deleteRow(1);
      }

      // ✅ 4. ADD ITEMS TO TABLE
      let total = 0;

      items.forEach(item => {
        const row = table.insertRow();
        row.innerHTML = `
          <td>${item.pizza_name}</td>
          <td>${item.size}</td>
          <td>${item.cheese}</td>
          <td>${parseFloat(item.price).toFixed(2)}</td>
          <td>${item.quantity}</td>
          <td>${parseFloat(item.total).toFixed(2)}</td>
          <td><button onclick="removeRow(this)">DEL</button></td>
        `;
        total += parseFloat(item.total);
      });

      // ✅ 5. UPDATE TOTAL
      document.getElementById("totalAmount").value = total.toFixed(2);
      updateTotal();

      // ✅ SCROLL DOWN TO ORDER SECTION
      window.scrollTo({
        top: document.getElementById("pizzaName").offsetTop,
        behavior: "smooth"
      });

    })
    .catch(err => {
      alert("Error checking stock: " + err);
    });
}

// ── OUT-OF-STOCK MODAL ────────────────────────────────────────────
function showOutOfStockModal(order, items, outOfStock) {

  // ── ORDER DETAILS ──
  let detailsHtml = `
    <p><strong>Order ID:</strong> ${order.order_id}</p>
    <p><strong>Customer:</strong> ${order.customer_name}</p>
    <p><strong>Mobile:</strong> ${order.mobile_number}</p>
    <p><strong>Order Type:</strong> ${order.order_type}</p>
    <p><strong>Payment:</strong> ${order.payment_method}</p>
    <p><strong>Total:</strong> ₱${parseFloat(order.total_amount).toFixed(2)}</p>
    <hr>
    <h4 style="margin-bottom:8px;">All Items:</h4>
    <table style="width:100%; border-collapse:collapse; font-size:13px;">
      <tr style="background:#f5f5f5;">
        <th style="padding:6px; text-align:left; border:1px solid #ddd;">Pizza</th>
        <th style="padding:6px; border:1px solid #ddd;">Size</th>
        <th style="padding:6px; border:1px solid #ddd;">Cheese</th>
        <th style="padding:6px; border:1px solid #ddd;">Qty</th>
        <th style="padding:6px; border:1px solid #ddd;">Stock</th>
        <th style="padding:6px; border:1px solid #ddd;">Status</th>
      </tr>
  `;

  // Build a quick lookup of out-of-stock pizza names
  const oosNames = outOfStock.map(o => o.pizza_name);

  items.forEach(item => {
    const isOOS    = oosNames.includes(item.pizza_name);
    const rowStyle = isOOS ? "background:#fff0f0;" : "";
    const status   = isOOS
      ? `<span style="color:red; font-weight:bold;">OUT OF STOCK (${item.current_stock} left)</span>`
      : `<span style="color:green;">✔ In Stock</span>`;

    detailsHtml += `
      <tr style="${rowStyle}">
        <td style="padding:6px; border:1px solid #ddd;">${item.pizza_name}</td>
        <td style="padding:6px; text-align:center; border:1px solid #ddd;">${item.size}"</td>
        <td style="padding:6px; text-align:center; border:1px solid #ddd;">${item.cheese}</td>
        <td style="padding:6px; text-align:center; border:1px solid #ddd;">${item.quantity}</td>
        <td style="padding:6px; text-align:center; border:1px solid #ddd;">${item.current_stock}</td>
        <td style="padding:6px; text-align:center; border:1px solid #ddd;">${status}</td>
      </tr>
    `;
  });

  detailsHtml += `</table>`;

  // ── SUMMARY OF OUT-OF-STOCK ──
  detailsHtml += `
    <hr>
    <p style="color:red; font-weight:bold; margin-top:8px;">
      ⚠ ${outOfStock.length} item(s) cannot be fulfilled due to insufficient stock.
      Please restock before processing this order.
    </p>
  `;

  document.getElementById("outOfStockBody").innerHTML = detailsHtml;
  document.getElementById("outOfStockModal").style.display = "block";
}

function closeOutOfStockModal() {
  document.getElementById("outOfStockModal").style.display = "none";
}

// CANCEL ORDER
function cancelPendingOrder(orderId) {

  if (!confirm("Are you sure you want to cancel this order?")) return;

  fetch("cancel_order.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: `order_id=${orderId}`
  })
  .then(res => res.text())
  .then(data => {
    if (data === "success") {
      alert("Order cancelled");
      location.reload();  // ✅ refresh list
    } else {
      alert("Error cancelling order");
    }
  });

}

/*if (change < 0) {
  document.getElementById("changeAmount").value = "Insufficient";

  document.getElementById("changeAmount").style.color = "white";
  document.getElementById("changeAmount").style.backgroundColor = "red";

} else {
  document.getElementById("changeAmount").value = "₱" + change.toFixed(2);

  document.getElementById("changeAmount").style.color = "black";
  document.getElementById("changeAmount").style.backgroundColor = "#e9ecef"; // original
}*/

// ✅ CALCULATOR BUTTON INPUT
function pressKey(value) {
  const input = document.getElementById("amountReceived");

  // ✅ Prevent multiple decimals
  if (value === "." && input.value.includes(".")) {
    return;
  }

  input.value += value;
  calculateChange();
}

// ✅ CLEAR BUTTON (C)
function clearEntry() {
  document.getElementById("amountReceived").value = "";
  calculateChange();
}

// ✅ DELETE BUTTON (DEL)
function deleteLast() {
  const input = document.getElementById("amountReceived");

  input.value = input.value.slice(0, -1);
  calculateChange();
}

function sanitizeInput() {
  const input = document.getElementById("amountReceived");

  // ✅ Remove non-numeric + non-dot
  input.value = input.value.replace(/[^0-9.]/g, '');

  // ✅ Allow only ONE dot
  const parts = input.value.split('.');
  if (parts.length > 2) {
    input.value = parts[0] + '.' + parts.slice(1).join('').replace(/\./g, '');
  }
}

function calculateChange() {

  const total = parseFloat(document.getElementById("totalAmount").value) || 0;
  const received = parseFloat(document.getElementById("amountReceived").value) || 0;

  const cashierTotalEl = document.getElementById("cashierTotal");
  if (cashierTotalEl) cashierTotalEl.value = "₱" + total.toFixed(2);

  const change = received - total;
  const changeEl = document.getElementById("changeAmount");

  if (change < 0 || received === 0) {
    if (changeEl) {
      changeEl.value = received === 0 ? "" : "Insufficient";
      changeEl.style.color = "red";
    }
  } else {
    if (changeEl) {
      changeEl.value = "₱" + change.toFixed(2);
      changeEl.style.color = "green";
    }
  }

  // ✅ Don't disable the finalize button here — let checkFinalizeBtn()
  // handle button state, and let finalizeOrder() do the actual validation
  // with proper toast/modal error messages.
}