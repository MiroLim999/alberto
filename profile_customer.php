<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = '$user_id' LIMIT 1";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit;
}

$homeLink = "index.php";
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === "cashier")      $homeLink = "cashier.php";
    elseif ($_SESSION['role'] === "admin")    $homeLink = "admin.php";
    elseif ($_SESSION['role'] === "driver")   $homeLink = "driver.php";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Alberto's Pizza | My Profile</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background: var(--bg); }

    /* ── Page wrapper ── */
    .profile-wrapper {
      min-height: calc(100vh - 72px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }

    /* ── Card ── */
    .profile-card {
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      width: 100%;
      max-width: 600px;
      overflow: hidden;
    }

    /* ── Card header ── */
    .profile-card-header {
      background: linear-gradient(135deg, #FFF9C4 0%, #FFE082 100%);
      border-bottom: 3px solid var(--yellow-dark);
      padding: 28px 32px 24px;
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .profile-avatar {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: var(--orange);
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 22px;
      font-weight: 900;
      font-family: var(--font-main);
      flex-shrink: 0;
      box-shadow: 0 3px 10px rgba(255,107,0,0.35);
    }

    .profile-header-text h2 {
      font-family: var(--font-main);
      font-size: 22px;
      font-weight: 900;
      color: var(--text-dark);
      margin-bottom: 2px;
    }

    .profile-header-text p {
      font-size: 13px;
      color: var(--text-mid);
    }

    /* ── Rows ── */
    .profile-body {
      padding: 8px 0 16px;
    }

    .profile-row {
      display: flex;
      align-items: center;
      padding: 14px 32px;
      border-bottom: 1px solid var(--border);
      gap: 12px;
      transition: background 0.15s;
    }

    .profile-row:last-child {
      border-bottom: none;
    }

    .profile-row:hover {
      background: #FFFDE7;
    }

    .profile-label {
      font-family: var(--font-main);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 0.6px;
      text-transform: uppercase;
      color: var(--text-light);
      min-width: 130px;
      flex-shrink: 0;
    }

    .profile-value {
      font-family: var(--font-body);
      font-size: 14px;
      color: var(--text-dark);
      font-weight: 500;
      flex: 1;
    }

    /* Password row override */
    .profile-row .password-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      position: relative;
    }

    .profile-row .password-wrapper input {
      border: none !important;
      background: transparent !important;
      outline: none !important;
      box-shadow: none !important;
      padding: 0 30px 0 0 !important;
      margin: 0 !important;
      font-size: 14px;
      font-family: var(--font-body);
      color: var(--text-dark);
      font-weight: 500;
      width: 100%;
    }

    .profile-row .toggle-password {
      position: absolute;
      right: 0;
      top: 50%;
      transform: translateY(-50%);
      font-size: 16px;
      cursor: pointer;
      color: var(--text-light);
      transition: color 0.15s;
    }

    .profile-row .toggle-password:hover { color: var(--orange); }

    /* Edit button */
    .edit-btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: var(--bg);
      border: 1.5px solid var(--border);
      color: var(--text-mid);
      text-decoration: none;
      font-size: 13px;
      flex-shrink: 0;
      transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.15s;
    }

    .edit-btn:hover {
      background: var(--orange);
      border-color: var(--orange);
      color: #fff;
      transform: scale(1.1);
    }

    @media (max-width: 600px) {
      .profile-card-header { padding: 20px; }
      .profile-row { padding: 12px 20px; }
      .profile-label { min-width: 100px; font-size: 10px; }
    }
  </style>
</head>

<body>

<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="<?= $homeLink ?>">HOME</a>
    <a href="profile_customer.php"><?= htmlspecialchars($_SESSION['username']); ?></a>
    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i></a>
  </div>
</header>

<div class="profile-wrapper">
  <div class="profile-card">

    <!-- Card Header -->
    <div class="profile-card-header">
      <div class="profile-avatar">
        <?= strtoupper(substr($user['username'], 0, 1)); ?>
      </div>
      <div class="profile-header-text">
        <h2><?= htmlspecialchars($user['username']); ?></h2>
        <p>View and manage your account details</p>
      </div>
    </div>

    <!-- Card Body -->
    <div class="profile-body">

      <div class="profile-row">
        <span class="profile-label">Username</span>
        <span class="profile-value"><?= htmlspecialchars($user['username']); ?></span>
        <a href="profile_customer_edit.php?field=username" class="edit-btn">
          <i class="fa-solid fa-pen"></i>
        </a>
      </div>

      <div class="profile-row">
        <span class="profile-label">Password</span>
        <div class="password-wrapper">
          <input type="password" id="viewPassword" value="<?= htmlspecialchars($user['password']); ?>" readonly>
          <span class="toggle-password" onclick="toggleViewPassword()">👁</span>
        </div>
        <a href="profile_customer_edit.php?field=password" class="edit-btn">
          <i class="fa-solid fa-pen"></i>
        </a>
      </div>

      <div class="profile-row">
        <span class="profile-label">Date of Birth</span>
        <span class="profile-value"><?= date("m / d / Y", strtotime($user['birth_date'])); ?></span>
        <a href="profile_customer_edit.php?field=dob" class="edit-btn">
          <i class="fa-solid fa-pen"></i>
        </a>
      </div>

      <div class="profile-row">
        <span class="profile-label">Gender</span>
        <span class="profile-value"><?= htmlspecialchars($user['gender']); ?></span>
        <a href="profile_customer_edit.php?field=gender" class="edit-btn">
          <i class="fa-solid fa-pen"></i>
        </a>
      </div>

      <div class="profile-row">
        <span class="profile-label">Mobile Number</span>
        <span class="profile-value"><?= htmlspecialchars($user['mobile_number']); ?></span>
        <a href="profile_customer_edit.php?field=mobile" class="edit-btn">
          <i class="fa-solid fa-pen"></i>
        </a>
      </div>

      <div class="profile-row">
        <span class="profile-label">Email</span>
        <span class="profile-value"><?= htmlspecialchars($user['email']); ?></span>
        <a href="profile_customer_edit.php?field=email" class="edit-btn">
          <i class="fa-solid fa-pen"></i>
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function toggleViewPassword() {
  const pass = document.getElementById("viewPassword");
  pass.type = pass.type === "password" ? "text" : "password";
}
</script>
<script src="js/home.js"></script>

</body>
</html>