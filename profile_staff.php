<?php
session_start();
include "db_connect.php";

// ── Auth: staff only (cashier, driver, admin) ──
$role = strtolower($_SESSION['role'] ?? '');
if (!isset($_SESSION['user_id']) || !in_array($role, ['cashier', 'driver', 'admin'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// ── Back link per role ──
$homeLink = match($role) {
    'cashier' => 'cashier.php',
    'driver'  => 'driver.php',
    'admin'   => 'admin.php',
    default   => 'index.php',
};

$roleLabel = match($role) {
    'cashier' => 'Cashier',
    'driver'  => 'Driver',
    'admin'   => 'Admin',
    default   => ucfirst($role),
};

$editError   = '';
$editSuccess = false;

// ── SAVE CHANGES ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username']   ?? '');
    $password   = trim($_POST['password']   ?? '');
    $mobile     = trim($_POST['mobile']     ?? '');
    $email      = trim($_POST['email']      ?? '');
    $gender     = $_POST['gender']          ?? '';
    $month      = str_pad($_POST['month']   ?? '', 2, '0', STR_PAD_LEFT);
    $day        = str_pad($_POST['day']     ?? '', 2, '0', STR_PAD_LEFT);
    $year       = $_POST['year']            ?? '';
    $birth_date = "$year-$month-$day";

    // Validation
    if ($username === '' || strlen($username) < 3) {
        $editError = 'Username must be at least 3 characters.';
    } elseif ($password === '' || strlen($password) < 6) {
        $editError = 'Password must be at least 6 characters.';
    } elseif (!preg_match('/^09\d{9}$/', $mobile)) {
        $editError = 'Mobile must be 11 digits starting with 09.';
    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $editError = 'Please enter a valid email address.';
    }

    // Duplicate username (excluding self)
    if (!$editError) {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? AND user_id <> ? LIMIT 1");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $editError = 'This username is already taken by another account.';
        }
        $stmt->close();
    }

    // Duplicate email (excluding self, only if email provided)
    if (!$editError && $email !== '') {
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ? LIMIT 1");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $editError = 'This email is already in use by another account.';
        }
        $stmt->close();
    }

    if (!$editError) {
        $stmt = $conn->prepare("
            UPDATE users SET
                username      = ?,
                password      = ?,
                mobile_number = ?,
                email         = ?,
                gender        = ?,
                birth_date    = ?
            WHERE user_id = ?
        ");
        $stmt->bind_param("ssssssi", $username, $password, $mobile, $email, $gender, $birth_date, $user_id);

        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            $stmt->close();
            // Reload fresh user data
            $stmt2 = $conn->prepare("SELECT * FROM users WHERE user_id = ? LIMIT 1");
            $stmt2->bind_param("i", $user_id);
            $stmt2->execute();
            $user = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
            $editSuccess = true;
        } else {
            $editError = "Error updating profile: " . $stmt->error;
            $stmt->close();
        }
    }
}

$birthMonth = date('n', strtotime($user['birth_date']));
$birthDay   = date('j', strtotime($user['birth_date']));
$birthYear  = date('Y', strtotime($user['birth_date']));
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

    .signup-wrapper {
      min-height: calc(100vh - 72px);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px;
    }

    .signup-card {
      display: flex;
      width: 100%;
      max-width: 880px;
      background: var(--white);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }

    /* ── Left info panel ── */
    .signup-info {
      width: 36%;
      background: linear-gradient(160deg, #FFF9C4 0%, #FFF3CD 60%, #FFE082 100%);
      padding: 48px 32px;
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
      width: fit-content;
    }

    .signup-info h2 {
      font-family: var(--font-main);
      font-size: 28px;
      font-weight: 900;
      color: var(--text-dark);
      margin-bottom: 12px;
      line-height: 1.2;
    }

    .signup-info h2 span { color: var(--orange); }

    .signup-info p {
      font-size: 14px;
      color: var(--text-mid);
      line-height: 1.7;
      margin-bottom: 12px;
    }

    .role-pill {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(255,107,0,0.12);
      color: var(--orange);
      border: 1.5px solid rgba(255,107,0,0.25);
      font-family: var(--font-main);
      font-size: 11px;
      font-weight: 800;
      letter-spacing: 1px;
      text-transform: uppercase;
      padding: 4px 12px;
      border-radius: var(--radius-pill);
      margin-bottom: 16px;
      width: fit-content;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid rgba(0,0,0,0.1);
      font-size: 13px;
      color: var(--orange);
      font-weight: 700;
      text-decoration: none;
      transition: gap 0.15s;
    }
    .back-link:hover { gap: 10px; }

    /* ── Right form panel ── */
    .signup-form {
      width: 64%;
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

    .password-wrapper {
      position: relative;
      width: 100%;
    }
    .password-wrapper input { padding-right: 44px !important; }
    .toggle-password {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 17px;
      color: var(--text-light);
      user-select: none;
      transition: color 0.15s;
    }
    .toggle-password:hover { color: var(--orange); }

    .dob { display: flex; gap: 8px; }
    .dob select { flex: 1; }

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
    .gender-inline label:hover { border-color: var(--orange); background: #FFF3E0; }
    .gender-inline input[type="radio"] { accent-color: var(--orange); margin: 0; }

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

    .alert-error {
      background: #fff5f5;
      border: 1.5px solid #FFCDD2;
      border-radius: 8px;
      padding: 10px 14px;
      color: #c62828;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .alert-success {
      background: #f1f8e9;
      border: 1.5px solid #AED581;
      border-radius: 8px;
      padding: 10px 14px;
      color: #2e7d32;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    @media (max-width: 720px) {
      .signup-card { flex-direction: column; }
      .signup-info, .signup-form { width: 100%; }
      .signup-info { padding: 28px 20px; }
      .signup-form { padding: 28px 20px; max-height: none; }
    }
  </style>
</head>

<body>

<header class="navbar">
  <img src="logo/Alberto's Pizza.png" class="logo-img" alt="Alberto's Pizza Logo">
  <div class="nav-links">
    <a href="<?= $homeLink ?>">
      <?= $role === 'driver' ? 'DASHBOARD' : 'HOME' ?>
    </a>
    <a href="profile_staff.php"><?= htmlspecialchars($_SESSION['username']) ?></a>
    <a href="logout.php">LOG OUT</a>
  </div>
</header>

<div class="signup-wrapper">
  <div class="signup-card">

    <!-- LEFT INFO PANEL -->
    <div class="signup-info">
      <div class="brand-badge">
        <i class="fa-solid fa-user-pen"></i> My Profile
      </div>
      <div class="role-pill">
        <?php
          $roleIcon = match($role) {
              'cashier' => 'fa-cash-register',
              'driver'  => 'fa-motorcycle',
              'admin'   => 'fa-shield-halved',
              default   => 'fa-user',
          };
        ?>
        <i class="fa-solid <?= $roleIcon ?>"></i>
        <?= $roleLabel ?>
      </div>
      <h2>Edit Your<br><span>Details</span></h2>
      <p>Update your account information below. Make sure all details are correct before saving.</p>
      <a href="<?= $homeLink ?>" class="back-link">
        <i class="fa-solid fa-arrow-left"></i>
        Back to <?= $role === 'driver' ? 'Dashboard' : 'Home' ?>
      </a>
    </div>

    <!-- RIGHT FORM PANEL -->
    <div class="signup-form">
      <div class="form-title">Account Information</div>

      <?php if ($editSuccess): ?>
        <div class="alert-success">
          <i class="fa-solid fa-circle-check"></i>
          Profile updated successfully!
        </div>
      <?php endif; ?>

      <?php if ($editError): ?>
        <div class="alert-error">
          <i class="fa-solid fa-circle-exclamation"></i>
          <?= htmlspecialchars($editError) ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <label>Username *</label>
        <input type="text" name="username"
               value="<?= htmlspecialchars($user['username']) ?>"
               placeholder="Your username" required>

        <label>Password *</label>
        <div class="password-wrapper">
          <input type="password" id="staffPassword" name="password"
                 value="<?= htmlspecialchars($user['password']) ?>"
                 placeholder="Your password" required>
          <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <label>Date of Birth</label>
        <div class="dob">
          <select name="month" id="dobMonth">
            <option value="">Month</option>
            <?php
            $months = [1=>"January",2=>"February",3=>"March",4=>"April",5=>"May",6=>"June",
                       7=>"July",8=>"August",9=>"September",10=>"October",11=>"November",12=>"December"];
            foreach ($months as $num => $name) {
                echo "<option value='$num'" . ($num == $birthMonth ? " selected" : "") . ">$name</option>";
            }
            ?>
          </select>
          <select name="day" id="dobDay">
            <option value="">Day</option>
            <?php for ($i = 1; $i <= 31; $i++): ?>
              <option value="<?= $i ?>"<?= $i == $birthDay ? " selected" : "" ?>><?= $i ?></option>
            <?php endfor; ?>
          </select>
          <select name="year" id="dobYear">
            <option value="">Year</option>
            <?php for ($y = date("Y"); $y >= 1900; $y--): ?>
              <option value="<?= $y ?>"<?= $y == $birthYear ? " selected" : "" ?>><?= $y ?></option>
            <?php endfor; ?>
          </select>
        </div>

        <label class="gender-label">Gender *</label>
        <div class="gender-inline">
          <?php foreach (['Male', 'Female', 'Other'] as $g): ?>
            <label>
              <input type="radio" name="gender" value="<?= $g ?>"
                     <?= $user['gender'] === $g ? 'checked' : '' ?> required>
              <?= $g ?>
            </label>
          <?php endforeach; ?>
        </div>

        <label>Mobile Number *</label>
        <input type="text" name="mobile"
               value="<?= htmlspecialchars($user['mobile_number']) ?>"
               placeholder="09XXXXXXXXX" maxlength="11" required>

        <label>Email</label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($user['email'] ?? '') ?>"
               placeholder="your@email.com">

        <button class="create-account-btn" type="submit">
          <i class="fa-solid fa-floppy-disk" style="margin-right:8px;"></i>
          Save Changes
        </button>

      </form>
    </div>

  </div>
</div>

<script>
function togglePassword() {
  const input = document.getElementById('staffPassword');
  input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>
