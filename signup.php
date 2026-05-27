<?php
include "db_connect.php";

if (isset($_POST['signup'])) {

  $username = $_POST['username'];
  $password = $_POST['password'];
  $birth_date = $_POST['dobYear'] . "-" . $_POST['dobMonth'] . "-" . $_POST['dobDay'];
  $gender = $_POST['gender'];
  $mobile = $_POST['mobile'];
  $email = $_POST['email'];

  $role = "customer";

  $check = "SELECT * FROM users WHERE username='$username'";
  $result = $conn->query($check);

  if ($result->num_rows > 0) {
    echo "<script>alert('Username already exists');</script>";
  } else {
    $sql = "INSERT INTO users
      (username, password, role, birth_date, gender, mobile_number, email)
      VALUES
      ('$username', '$password', '$role', '$birth_date', '$gender', '$mobile', '$email')";

    if ($conn->query($sql)) {
      echo "<script>
        alert('Account created successfully!');
        window.location.href='index.php';
      </script>";
    } else {
      echo "<script>alert('Error creating account');</script>";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | Sign Up</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    /* ── Page background ── */
    body {
      background: var(--bg);
    }

    /* ── Wrapper centers the card vertically ── */
    .signup-wrapper {
      min-height: calc(100vh - 72px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }

    /* ── Card ── */
    .signup-card {
      display: flex;
      width: 100%;
      max-width: 880px;
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }

    /* ── Left panel ── */
    .signup-info {
      width: 38%;
      background: linear-gradient(160deg, #FFF9C4 0%, #FFF3CD 60%, #FFE082 100%);
      padding: 48px 36px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-right: 3px solid var(--yellow-dark);
    }

    .signup-info .brand-badge {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--orange);
      color: #fff;
      font-family: var(--font-main);
      font-weight: 900;
      font-size: 11px;
      letter-spacing: 1.5px;
      text-transform: uppercase;
      padding: 5px 14px;
      border-radius: var(--radius-pill);
      margin-bottom: 20px;
    }

    .signup-info h2 {
      font-family: var(--font-main);
      font-size: 30px;
      font-weight: 900;
      color: var(--text-dark);
      margin-bottom: 12px;
      line-height: 1.2;
    }

    .signup-info h2 span {
      color: var(--orange);
    }

    .signup-info p {
      font-size: 14px;
      color: var(--text-mid);
      line-height: 1.7;
      margin-bottom: 16px;
    }

    .terms-text {
      font-size: 12px !important;
      color: var(--text-light) !important;
      line-height: 1.6 !important;
    }

    .terms-text a {
      color: var(--orange) !important;
      text-decoration: none;
      font-weight: 700;
      cursor: pointer;
    }

    .terms-text a:hover {
      text-decoration: underline;
    }

    .login-link {
      font-size: 13px !important;
      color: var(--text-mid) !important;
      margin-top: 20px !important;
      padding-top: 20px;
      border-top: 1px solid rgba(0,0,0,0.1);
    }

    .login-link a {
      color: var(--orange) !important;
      font-weight: 700;
      text-decoration: none;
    }

    .login-link a:hover {
      text-decoration: underline;
    }

    /* ── Right panel (form) ── */
    .signup-form {
      width: 62%;
      padding: 44px 40px;
      overflow-y: auto;
      max-height: calc(100vh - 120px);
    }

    .signup-form .form-title {
      font-family: var(--font-main);
      font-size: 20px;
      font-weight: 900;
      color: var(--text-dark);
      margin-bottom: 24px;
      padding-bottom: 14px;
      border-bottom: 2px solid var(--yellow);
    }

    .signup-form label {
      font-family: var(--font-main);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.4px;
      color: var(--text-mid);
      text-transform: uppercase;
      display: block;
      margin-top: 18px;
      margin-bottom: 6px;
    }

    .signup-form input[type="text"],
    .signup-form input[type="email"],
    .signup-form input[type="password"],
    .signup-form select {
      width: 100%;
      padding: 10px 14px;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-md);
      font-family: var(--font-body);
      font-size: 14px;
      color: var(--text-dark);
      background: #FAFAFA;
      transition: border-color 0.18s, box-shadow 0.18s;
      outline: none;
      height: auto;
    }

    .signup-form input:focus,
    .signup-form select:focus {
      border-color: var(--yellow-dark);
      box-shadow: 0 0 0 3px rgba(255,209,0,0.2);
      background: #fff;
    }

    /* DOB row */
    .dob {
      display: flex;
      gap: 8px;
    }

    .dob select {
      flex: 1;
    }

    /* Gender row */
    .gender-label {
      font-family: var(--font-main);
      font-size: 12px;
      font-weight: 800;
      letter-spacing: 0.4px;
      color: var(--text-mid);
      text-transform: uppercase;
      display: block;
      margin-top: 18px;
      margin-bottom: 6px;
    }

    .gender-inline {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
    }

    .gender-inline label {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-family: var(--font-body);
      font-size: 13px;
      font-weight: 600;
      text-transform: none;
      letter-spacing: 0;
      color: var(--text-dark);
      margin: 0;
      padding: 7px 14px;
      border-radius: var(--radius-pill);
      border: 1.5px solid var(--border);
      cursor: pointer;
      transition: border-color 0.15s, background 0.15s;
    }

    .gender-inline label:hover {
      border-color: var(--orange);
      background: #FFF3E0;
    }

    .gender-inline input[type="radio"] {
      accent-color: var(--orange);
      margin: 0;
    }

    /* Password toggle */
    .pw-wrap {
      position: relative;
    }

    .pw-wrap input {
      padding-right: 40px !important;
    }

    .pw-wrap .eye-icon {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: var(--text-light);
      font-size: 15px;
      transition: color 0.15s;
    }

    .pw-wrap .eye-icon:hover {
      color: var(--orange);
    }

    /* Submit button */
    .create-account-btn {
      width: 100%;
      margin-top: 28px;
      padding: 13px;
      background: var(--orange);
      color: #fff;
      border: none;
      border-radius: var(--radius-md);
      font-family: var(--font-main);
      font-size: 15px;
      font-weight: 900;
      letter-spacing: 0.5px;
      cursor: pointer;
      transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
      box-shadow: 0 3px 12px rgba(255,107,0,0.30);
    }

    .create-account-btn:hover {
      background: var(--orange-light);
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255,107,0,0.40);
    }

    /* Modal styling */
    .modal {
      display: none;
      position: fixed;
      inset: 0;
      z-index: 999;
      background: rgba(0,0,0,0.45);
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(3px);
    }

    .modal[style*="block"] {
      display: flex !important;
    }

    .modal-content {
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      padding: 32px;
      max-width: 480px;
      width: 90%;
      animation: popIn 0.25s ease;
    }

    @keyframes popIn {
      from { transform: scale(0.92); opacity: 0; }
      to   { transform: scale(1);    opacity: 1; }
    }

    .modal-content h3 {
      font-family: var(--font-main);
      font-size: 20px;
      font-weight: 900;
      color: var(--text-dark);
      margin-bottom: 14px;
      padding-bottom: 12px;
      border-bottom: 2px solid var(--yellow);
    }

    .modal-content p {
      font-size: 14px;
      color: var(--text-mid);
      line-height: 1.7;
    }

    .modal-content button {
      margin-top: 20px;
      padding: 10px 24px;
      background: var(--orange);
      color: #fff;
      border: none;
      border-radius: var(--radius-md);
      font-family: var(--font-main);
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      transition: background 0.18s, transform 0.15s;
    }

    .modal-content button:hover {
      background: var(--orange-light);
      transform: translateY(-1px);
    }

    @media (max-width: 720px) {
      .signup-card { flex-direction: column; }
      .signup-info, .signup-form { width: 100%; }
      .signup-info { padding: 32px 24px; }
      .signup-form { padding: 32px 24px; max-height: none; }
    }
  </style>
</head>

<body>

<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="index.php">HOME</a>
    <a href="signup.php" class="nav-btn">SIGN UP</a>
    <a href="login.php">LOG IN</a>
  </div>
</header>

<div class="signup-wrapper">
  <div class="signup-card">

    <!-- LEFT INFO PANEL -->
    <div class="signup-info">
      <div class="brand-badge">
        <i class="fa-solid fa-pizza-slice"></i> Alberto's Pizza
      </div>
      <h2>Create your<br><span>Account</span></h2>
      <p>
        Welcome! Sign up to enjoy a smoother ordering experience, track your orders, and more.
      </p>
      <p class="terms-text">
        By continuing, you agree to our
        <a href="#" onclick="termsAlert(); return false;">Terms &amp; Conditions</a>
        and
        <a href="#" onclick="privacyAlert(); return false;">Privacy Notice</a>.
      </p>
      <p class="login-link">
        Already have an account?
        <a href="login.php">Log in here</a>
      </p>
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="signup-form">
      <div class="form-title">Registration Details</div>

      <form method="POST">

        <label>Username *</label>
        <input type="text" name="username" placeholder="Choose a username" required>

        <label>Password *</label>
        <div class="pw-wrap">
          <input type="password" name="password" id="signupPassword" placeholder="Create a password" required>
          <i class="fa-solid fa-eye eye-icon" onclick="toggleSignupPassword()"></i>
        </div>

        <label>Date of Birth</label>
        <div class="dob">
          <select name="dobMonth" id="dobMonth" required></select>
          <select name="dobDay" id="dobDay" required></select>
          <select name="dobYear" id="dobYear" required></select>
        </div>

        <label class="gender-label">Gender *</label>
        <div class="gender-inline">
          <label><input type="radio" name="gender" value="Male" required> Male</label>
          <label><input type="radio" name="gender" value="Female"> Female</label>
          <label><input type="radio" name="gender" value="Other"> Other</label>
        </div>

        <label>Mobile Number *</label>
        <input type="text" name="mobile" placeholder="09XXXXXXXXX" required maxlength="11">

        <label>Email *</label>
        <input type="email" name="email" placeholder="Enter your email" required>

        <button class="create-account-btn" type="submit" name="signup">
          Create Account
        </button>

      </form>
    </div>

  </div>
</div>

<!-- TERMS MODAL -->
<div id="termsModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Terms &amp; Conditions</h3>
    <p>
      By creating an account, you agree to use Alberto's Pizza services responsibly.
      You agree not to misuse the ordering system, provide false information, or disrupt operations.
      Prices and availability of items are subject to change without prior notice.
    </p>
    <button onclick="closeTerms()">Close</button>
  </div>
</div>

<!-- PRIVACY MODAL -->
<div id="privacyModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Privacy Notice</h3>
    <p>
      Your personal information such as username, email, and mobile number
      is collected solely for account creation and order processing.
      Alberto's Pizza does not share your data with third parties without consent.
    </p>
    <button onclick="closePrivacy()">Close</button>
  </div>
</div>

<script src="js/home.js"></script>
<script>
function toggleSignupPassword() {
  const input = document.getElementById("signupPassword");
  const icon = input.nextElementSibling;
  if (input.type === "password") {
    input.type = "text";
    icon.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.replace("fa-eye-slash", "fa-eye");
  }
}

function termsAlert() {
  document.getElementById("termsModal").style.display = "block";
}
function privacyAlert() {
  document.getElementById("privacyModal").style.display = "block";
}
function closeTerms() {
  document.getElementById("termsModal").style.display = "none";
}
function closePrivacy() {
  document.getElementById("privacyModal").style.display = "none";
}
</script>

</body>
</html>