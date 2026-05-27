<?php
include "db_connect.php";

$username   = $conn->real_escape_string($_POST['username']);
$password   = $conn->real_escape_string($_POST['password']);
$role       = $conn->real_escape_string($_POST['role']);
$birth_date = $conn->real_escape_string($_POST['birth_date']);
$gender     = $conn->real_escape_string($_POST['gender']);
$mobile     = $conn->real_escape_string($_POST['mobile']);
$email      = $conn->real_escape_string($_POST['email']);

$result = $conn->query("
  INSERT INTO users (username, password, role, birth_date, gender, mobile_number, email, created_at)
  VALUES ('$username', '$password', '$role', '$birth_date', '$gender', '$mobile', '$email', NOW())
");

if ($result) {
  echo "success";
} else {
  echo "error: " . $conn->error;
}
?>