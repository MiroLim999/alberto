<?php
include "db_connect.php";

$id = $_POST['user_id'];

$conn->query("DELETE FROM users WHERE user_id='$id'");
?>