<?php
session_start();
include "db_connect.php";

// Already logged in — redirect to their home page
if (isset($_SESSION['user_id'])) {
    $role = strtolower($_SESSION['role'] ?? '');
    if ($role === 'admin')    { header("Location: admin.php");   exit; }
    if ($role === 'cashier')  { header("Location: cashier.php"); exit; }
    if ($role === 'driver')   { header("Location: driver.php");  exit; }
    header("Location: index.php"); exit;
}

$loginError = '';

if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $loginError = 'Please enter both username and password.';
    } else {
        // ── Prepared statement — no SQL injection ──────────────
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Plain-text password comparison (matches current DB storage)
        if ($user && $user['password'] === $password) {
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = strtolower($user['role']); // normalise to lowercase

            $role = $_SESSION['role'];
            if ($role === 'admin')   { header("Location: admin.php");   exit; }
            if ($role === 'cashier') { header("Location: cashier.php"); exit; }
            if ($role === 'driver')  { header("Location: driver.php");  exit; }
            header("Location: index.php"); exit;
        } else {
            $loginError = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | Log In</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body { background: var(--bg); }
    .signup-wrapper {
      min-height: calc(100vh - 72px);
      display: flex; align-items: center; justify-content: center;
      padding: 40px 16px;
    }
    .signup-card {
      display: flex; width: 100%; max-width: 820px;
      background: var(--white); border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg); overflow: hidden;
    }
    .signup-info {
      width: 40%;
      background: linear-gradient(160deg, #FFF9C4 0%, #FFF3CD 60%, #FFE082 100%);
      padding: 56px 36px; display: flex; flex-direction: column;
      justify-content: center; border-right: 3px solid var(--yellow-dark);
    }
    .signup-info .brand-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--orange); color: #fff;
      font-family: var(--font-main); font-weight: 900; font-size: 11px;
      letter-spacing: 1.5px; text-transform: uppercase;
      padding: 5px 14px; border-radius: var(--radius-pill);
      margin-bottom: 20px; width: fit-content;
    }
    .signup-info h2 {
      font-family: var(--font-main); font-size: 30px; font-weight: 900;
      color: var(--text-dark); margin-bottom: 12px; line-height: 1.2;
    }
    .signup-info h2 span { color: var(--orange); }
    .signup-info p { font-size: 14px; color: var(--text-mid); line-height: 1.7; margin-bottom: 16px; }
    .terms-text { font-size: 12px !important; color: var(--text-light) !important; line-height: 1.6 !important; }
    .terms-text a { color: var(--orange) !important; text-decoration: none; font-weight: 700; cursor: pointer; }
    .terms-text a:hover { text-decoration: underline; }
    .login-link { font-size: 13px !important; color: var(--text-mid) !important; margin-top: 20px !important; padding-top: 20px; border-top: 1px solid rgba(0,0,0,0.1); }
    .login-link a { color: var(--orange) !important; font-weight: 700; text-decoration: none; }
    .login-link a:hover { text-decoration: underline; }
    .signup-form {
      width: 60%; padding: 56px 44px;
      display: flex; flex-direction: column; justify-content: center;
    }
    .signup-form .form-title {
      font-family: var(--font-main); font-size: 20px; font-weight: 900;
      color: var(--text-dark); margin-bottom: 28px; padding-bottom: 14px;
      border-bottom: 2px solid var(--yellow);
    }
    .signup-form label {
      font-family: var(--font-main); font-size: 12px; font-weight: 800;
      letter-spacing: 0.4px; color: var(--text-mid); text-transform: uppercase;
      display: block; margin-top: 18px; margin-bottom: 6px;
    }
    .signup-form label:first-of-type { margin-top: 0; }
    .signup-form input {
      width: 100%; padding: 11px 14px;
      border: 1.5px solid var(--border); border-radius: var(--radius-md);
      font-family: var(--font-body); font-size: 14px; color: var(--text-dark);
      background: #FAFAFA; transition: border-color 0.18s, box-shadow 0.18s;
      outline: none; height: auto;
    }
    .signup-form input:focus {
      border-color: var(--yellow-dark);
      box-shadow: 0 0 0 3px rgba(255,209,0,0.2); background: #fff;
    }
    .signup-form input.input-error { border-color: var(--red) !important; }
    .password-wrapper { position: relative; width: 100%; }
    .password-wrapper input { padding-right: 44px !important; }
    .toggle-password {
      position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
      cursor: pointer; font-size: 17px; color: var(--text-light);
      user-select: none; transition: color 0.15s;
    }
    .toggle-password:hover { color: var(--orange); }
    .create-account-btn {
      width: 100%; margin-top: 32px; padding: 13px;
      background: var(--orange); color: #fff; border: none;
      border-radius: var(--radius-md); font-family: var(--font-main);
      font-size: 15px; font-weight: 900; letter-spacing: 0.5px; cursor: pointer;
      transition: background 0.18s, transform 0.15s, box-shadow 0.18s;
      box-shadow: 0 3px 12px rgba(255,107,0,0.30);
    }
    .create-account-btn:hover {
      background: var(--orange-light); transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255,107,0,0.40);
    }
    .error-banner {
      background: #fff5f5; border: 1.5px solid #FFCDD2; border-radius: 8px;
      padding: 10px 14px; color: #c62828; font-size: 13px; font-weight: 600;
      margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }
    .modal {
      display: none; position: fixed; inset: 0; z-index: 999;
      background: rgba(0,0,0,0.45); align-items: center; justify-content: center;
      backdrop-filter: blur(3px);
    }
    .modal[style*="block"] { display: flex !important; }
    .modal-content {
      background: var(--white); border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg); padding: 32px; max-width: 480px; width: 90%;
    }
    .modal-content h3 {
      font-family: var(--font-main); font-size: 20px; font-weight: 900;
      color: var(--text-dark); margin-bottom: 14px; padding-bottom: 12px;
      border-bottom: 2px solid var(--yellow);
    }
    .modal-content p { font-size: 14px; color: var(--text-mid); line-height: 1.7; }
    .modal-content button {
      margin-top: 20px; padding: 10px 24px; background: var(--orange); color: #fff;
      border: none; border-radius: var(--radius-md); font-family: var(--font-main);
      font-size: 13px; font-weight: 800; cursor: pointer;
    }
    @media (max-width: 680px) {
      .signup-card { flex-direction: column; }
      .signup-info, .signup-form { width: 100%; }
      .signup-info { padding: 32px 24px; }
      .signup-form { padding: 32px 24px; }
    }
  </style>
</head>
<body>

<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="index.php">HOME</a>
    <a href="signup.php">SIGN UP</a>
    <a href="login.php" class="nav-btn">LOG IN</a>
  </div>
</header>

<div class="signup-wrapper">
  <div class="signup-card">

    <div class="signup-info">
      <div class="brand-badge"><i class="fa-solid fa-pizza-slice"></i> Alberto's Pizza</div>
      <h2>Welcome<br><span>Back!</span></h2>
      <p>Log in to your Alberto's Pizza account to continue ordering and track your purchases.</p>
      <p class="terms-text">
        By continuing, you agree to our
        <a href="#" onclick="termsAlert(); return false;">Terms &amp; Conditions</a>
        and <a href="#" onclick="privacyAlert(); return false;">Privacy Notice</a>.
      </p>
      <p class="login-link">Don't have an account? <a href="signup.php">Create one here</a></p>
    </div>

    <div class="signup-form">
      <div class="form-title">Sign In to Your Account</div>

      <?php if ($loginError): ?>
        <div class="error-banner">
          <i class="fa-solid fa-circle-exclamation"></i>
          <?= htmlspecialchars($loginError) ?>
        </div>
      <?php endif; ?>

      <form action="login.php" method="post">
        <label>Username *</label>
        <input type="text" name="username"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
               placeholder="Enter your username" required
               <?= $loginError ? 'class="input-error"' : '' ?>>

        <label>Password *</label>
        <div class="password-wrapper">
          <input type="password" name="password" id="loginPassword"
                 placeholder="Enter your password" required
                 <?= $loginError ? 'class="input-error"' : '' ?>>
          <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <button class="create-account-btn" type="submit" name="login">Log In</button>
      </form>
    </div>

  </div>
</div>

<div id="termsModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Terms &amp; Conditions</h3>
    <p>By creating an account, you agree to use Alberto's Pizza services responsibly. You agree not to misuse the ordering system, provide false information, or disrupt operations.</p>
    <button onclick="closeTerms()">Close</button>
  </div>
</div>
<div id="privacyModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h3>Privacy Notice</h3>
    <p>Your personal information is collected solely for account creation and order processing. Alberto's Pizza does not share your data with third parties without consent.</p>
    <button onclick="closePrivacy()">Close</button>
  </div>
</div>

<script src="js/home.js"></script>
<script>
function togglePassword() {
  const input = document.getElementById("loginPassword");
  input.type = input.type === "password" ? "text" : "password";
}
function termsAlert()  { document.getElementById("termsModal").style.display   = "block"; }
function privacyAlert(){ document.getElementById("privacyModal").style.display = "block"; }
function closeTerms()  { document.getElementById("termsModal").style.display   = "none";  }
function closePrivacy(){ document.getElementById("privacyModal").style.display = "none";  }
</script>
</body>
</html>
