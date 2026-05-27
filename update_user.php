<?php
session_start();
include "db_connect.php";

// ── Role guard: admin only ──
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo "forbidden";
    exit;
}

$id       = intval($_POST['user_id'] ?? 0);
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');
$role     = strtolower(trim($_POST['role'] ?? ''));
$birth    = trim($_POST['birth'] ?? '');
$gender   = trim($_POST['gender'] ?? '');
$mobile   = trim($_POST['mobile'] ?? '');
$email    = trim($_POST['email'] ?? '');

if (!$id || $username === '' || $password === '' || $role === '') {
    echo "error: missing required fields";
    exit;
}

// Validate role to a known set
$allowedRoles = ['admin', 'cashier', 'customer', 'driver'];
if (!in_array($role, $allowedRoles, true)) {
    echo "error: invalid role";
    exit;
}

$stmt = $conn->prepare("
    UPDATE users
    SET username = ?, password = ?, role = ?, birth_date = ?, gender = ?, mobile_number = ?, email = ?
    WHERE user_id = ?
");
$stmt->bind_param("sssssssi", $username, $password, $role, $birth, $gender, $mobile, $email, $id);
echo $stmt->execute() ? "success" : "error: " . $stmt->error;
$stmt->close();
?>
