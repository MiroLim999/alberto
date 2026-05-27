function processPendingOrder() {
  // Simulated data for prototype
  const table = document.getElementById("orderTable");

  table.innerHTML += `
    <tr>
      <td>Pizza Supreme</td>
      <td>9"</td>
      <td>Quickmelt</td>
      <td>145</td>
      <td>2</td>
      <td>290</td>
      <td><button onclick="this.parentElement.parentElement.remove()">DEL</button></td>
    </tr>
  `;

  document.getElementById("customerName").value = "user123";
  document.getElementById("contactNumber").value = "09123456789";
  document.getElementById("optionalEmail").value = "user@email.com";
  document.getElementById("customerAddress").value = "Sogod, Southern Leyte";

  alert("Pending order loaded for processing.");
}

function finalizeCashierOrder() {
  const cash = parseFloat(document.getElementById("cashReceived").value);
  const total = getOrderTotal();

  if (isNaN(cash) || cash < total) {
    alert("Insufficient payment.");
    return;
  }

  const change = cash - total;

  // ✅ SHOW CHANGE IN TEXTBOX
  document.getElementById("change").value = change.toFixed(2);

  alert(
    "Payment successful!\n\n" +
    "Total: ₱" + total.toFixed(2) +
    "\nPaid: ₱" + cash.toFixed(2) +
    "\nChange: ₱" + change.toFixed(2)
  );

  // ✅ Clear only AFTER displaying change
  setTimeout(() => {
    calcClear();
    clearOrder();
  }, 300);
}

let cashierInput = "";

function calcInput(num) {
  cashierInput += num;
  document.getElementById("cashReceived").value = cashierInput;
}

function calcDel() {
  cashierInput = cashierInput.slice(0, -1);
  document.getElementById("cashReceived").value = cashierInput || "0";
}

function calcClear() {
  cashierInput = "";
  document.getElementById("cashReceived").value = "0";
  document.getElementById("change").value = "";
}

function getOrderTotal() {
  let total = 0;
  const rows = document.querySelectorAll("#orderTable tr");

  rows.forEach((row, index) => {
    if (index === 0) return; // skip header
    const amount = parseFloat(row.children[5].innerText);
    if (!isNaN(amount)) total += amount;
  });

  return total;
}
