<?php
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$username   = trim($_POST['username'] ?? '');
$password   = trim($_POST['password'] ?? '');
$role       = strtolower(trim($_POST['role'] ?? ''));
$birth_date = trim($_POST['birth_date'] ?? '');
$gender     = trim($_POST['gender'] ?? '');
$mobile     = trim($_POST['mobile'] ?? '');
$email      = trim($_POST['email'] ?? '');

if ($username === '' || $password === '' || $role === '') {
    echo "error: missing required fields";
    exit;
}

$allowedRoles = ['admin', 'cashier', 'customer', 'driver'];
if (!in_array($role, $allowedRoles, true)) {
    echo "error: invalid role";
    exit;
}

// Check duplicate username
$check = $conn->prepare("SELECT user_id FROM users WHERE username = ? LIMIT 1");
$check->bind_param("s", $username);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo "error: username already exists";
    $check->close();
    exit;
}
$check->close();

$stmt = $conn->prepare("
  INSERT INTO users (username, password, role, birth_date, gender, mobile_number, email, created_at)
  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");
$stmt->bind_param("sssssss", $username, $password, $role, $birth_date, $gender, $mobile, $email);
echo $stmt->execute() ? "success" : "error: " . $stmt->error;
$stmt->close();
?>
