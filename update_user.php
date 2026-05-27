<?php
include "db_connect.php";

$id = $_POST['user_id'];

$conn->query("
  UPDATE users SET
    username='{$_POST['username']}',
    password='{$_POST['password']}',
    role='{$_POST['role']}',
    birth_date='{$_POST['birth']}',
    gender='{$_POST['gender']}',
    mobile_number='{$_POST['mobile']}',
    email='{$_POST['email']}'
  WHERE user_id='$id'
");
?>