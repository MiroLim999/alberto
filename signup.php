<?php
include "db_connect.php";

$signupErrors  = [];   // field-level errors
$signupSuccess = false;
$old           = [];   // repopulate form on error

if (isset($_POST['signup'])) {

    $username   = trim($_POST['username']   ?? '');
    $password   = trim($_POST['password']   ?? '');
    $dobMonth   = $_POST['dobMonth'] ?? '';
    $dobDay     = $_POST['dobDay']   ?? '';
    $dobYear    = $_POST['dobYear']  ?? '';
    $birth_date = $dobYear . "-"
                . str_pad($dobMonth, 2, '0', STR_PAD_LEFT) . "-"
                . str_pad($dobDay,   2, '0', STR_PAD_LEFT);
    $gender     = $_POST['gender']  ?? '';
    $mobile     = trim($_POST['mobile'] ?? '');
    $email      = trim($_POST['email']  ?? '');
    $role       = "customer";

    // Keep old values so the form repopulates on error
    $old = compact('username','dobMonth','dobDay','dobYear','gender','mobile','email');

    // ── Validation ────────────────────────────────────────────
    if ($username === '') {
        $signupErrors['username'] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $signupErrors['username'] = 'Username must be at least 3 characters.';
    }

    if ($password === '') {
        $signupErrors['password'] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $signupErrors['password'] = 'Password must be at least 6 characters.';
    }

    if ($mobile === '') {
        $signupErrors['mobile'] = 'Mobile number is required.';
    } elseif (!preg_match('/^09\d{9}$/', $mobile)) {
        $signupErrors['mobile'] = 'Must be 11 digits starting with 09.';
    }

    if ($email === '') {
        $signupErrors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signupErrors['email'] = 'Please enter a valid email address.';
    }

    if ($gender === '') {
        $signupErrors['gender'] = 'Please select a gender.';
    }

    if (!$dobYear || !$dobMonth || !$dobDay) {
        $signupErrors['dob'] = 'Please select your complete date of birth.';
    }

    // ── Duplicate checks (only if basic validation passed) ────
    if (empty($signupErrors['username'])) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $signupErrors['username'] = 'This username is already taken. Please choose another.';
        }
        $stmt->close();
    }

    if (empty($signupErrors['email'])) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $signupErrors['email'] = 'An account with this email already exists.';
        }
        $stmt->close();
    }

    // ── Insert if no errors ───────────────────────────────────
    if (empty($signupErrors)) {
        $stmt = $conn->prepare("
            INSERT INTO users (username, password, role, birth_date, gender, mobile_number, email)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssssss", $username, $password, $role, $birth_date, $gender, $mobile, $email);

        if ($stmt->execute()) {
            $new_user_id = $conn->insert_id;
            $stmt->close();

            session_start();
            $_SESSION['user_id']  = $new_user_id;
            $_SESSION['username'] = $username;
            $_SESSION['role']     = $role;

            header("Location: index.php");
            exit;
        } else {
            $signupErrors['general'] = 'Error creating account. Please try again.';
            $stmt->close();
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

    /* ── Inline field errors ── */
    .input-error {
      border-color: var(--red) !important;
      box-shadow: 0 0 0 3px rgba(198,40,40,0.12) !important;
      background: #fff8f8 !important;
    }
    .field-error-msg {
      color: #c62828;
      font-size: 12px;
      font-weight: 600;
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .field-error-msg i { font-size: 11px; }

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

      <?php if (!empty($signupErrors['general'])): ?>
        <div style="background:#fff5f5;border:1.5px solid #FFCDD2;border-radius:8px;padding:10px 14px;color:#c62828;font-size:13px;font-weight:600;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
          <i class="fa-solid fa-circle-exclamation"></i>
          <?= htmlspecialchars($signupErrors['general']) ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <!-- USERNAME -->
        <label>Username *</label>
        <input type="text" name="username"
               placeholder="Choose a username"
               value="<?= htmlspecialchars($old['username'] ?? '') ?>"
               class="<?= isset($signupErrors['username']) ? 'input-error' : '' ?>"
               required>
        <?php if (isset($signupErrors['username'])): ?>
          <div class="field-error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($signupErrors['username']) ?></div>
        <?php endif; ?>

        <!-- PASSWORD -->
        <label>Password *</label>
        <div class="pw-wrap">
          <input type="password" name="password" id="signupPassword"
                 placeholder="Create a password (min. 6 characters)"
                 class="<?= isset($signupErrors['password']) ? 'input-error' : '' ?>"
                 required>
          <i class="fa-solid fa-eye eye-icon" onclick="toggleSignupPassword()"></i>
        </div>
        <?php if (isset($signupErrors['password'])): ?>
          <div class="field-error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($signupErrors['password']) ?></div>
        <?php endif; ?>

        <!-- DATE OF BIRTH -->
        <label>Date of Birth *</label>
        <div class="dob">
          <select name="dobMonth" id="dobMonth"
                  class="<?= isset($signupErrors['dob']) ? 'input-error' : '' ?>"
                  required></select>
          <select name="dobDay" id="dobDay"
                  class="<?= isset($signupErrors['dob']) ? 'input-error' : '' ?>"
                  required></select>
          <select name="dobYear" id="dobYear"
                  class="<?= isset($signupErrors['dob']) ? 'input-error' : '' ?>"
                  required></select>
        </div>
        <?php if (isset($signupErrors['dob'])): ?>
          <div class="field-error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($signupErrors['dob']) ?></div>
        <?php endif; ?>

        <!-- GENDER -->
        <label class="gender-label">Gender *</label>
        <div class="gender-inline">
          <?php foreach (['Male','Female','Other'] as $g): ?>
            <label>
              <input type="radio" name="gender" value="<?= $g ?>"
                     <?= (($old['gender'] ?? '') === $g) ? 'checked' : '' ?>
                     required> <?= $g ?>
            </label>
          <?php endforeach; ?>
        </div>
        <?php if (isset($signupErrors['gender'])): ?>
          <div class="field-error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($signupErrors['gender']) ?></div>
        <?php endif; ?>

        <!-- MOBILE -->
        <label>Mobile Number *</label>
        <input type="text" name="mobile"
               placeholder="09XXXXXXXXX"
               value="<?= htmlspecialchars($old['mobile'] ?? '') ?>"
               class="<?= isset($signupErrors['mobile']) ? 'input-error' : '' ?>"
               maxlength="11" required>
        <?php if (isset($signupErrors['mobile'])): ?>
          <div class="field-error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($signupErrors['mobile']) ?></div>
        <?php endif; ?>

        <!-- EMAIL -->
        <label>Email *</label>
        <input type="email" name="email"
               placeholder="Enter your email"
               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
               class="<?= isset($signupErrors['email']) ? 'input-error' : '' ?>"
               required>
        <?php if (isset($signupErrors['email'])): ?>
          <div class="field-error-msg"><i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($signupErrors['email']) ?></div>
        <?php endif; ?>

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

function termsAlert()  { document.getElementById("termsModal").style.display   = "block"; }
function privacyAlert(){ document.getElementById("privacyModal").style.display = "block"; }
function closeTerms()  { document.getElementById("termsModal").style.display   = "none";  }
function closePrivacy(){ document.getElementById("privacyModal").style.display = "none";  }

// ── Restore DOB dropdowns after server-side validation error ──
document.addEventListener("DOMContentLoaded", function () {
  // home.js populateDOB() fills the dropdowns — then we set the saved values
  const savedMonth = <?= json_encode($old['dobMonth'] ?? '') ?>;
  const savedDay   = <?= json_encode($old['dobDay']   ?? '') ?>;
  const savedYear  = <?= json_encode($old['dobYear']  ?? '') ?>;

  if (!savedMonth && !savedDay && !savedYear) return;

  // Wait for populateDOB to finish (it runs synchronously in DOMContentLoaded too)
  setTimeout(() => {
    const m = document.getElementById("dobMonth");
    const d = document.getElementById("dobDay");
    const y = document.getElementById("dobYear");
    if (m && savedMonth) m.value = savedMonth;
    if (y && savedYear)  y.value = savedYear;
    // Trigger change so days populate, then set day
    if (m) m.dispatchEvent(new Event("change"));
    if (y) y.dispatchEvent(new Event("change"));
    setTimeout(() => { if (d && savedDay) d.value = savedDay; }, 50);
  }, 10);
});
</script>

</body>
</html>