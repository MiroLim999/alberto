<?php
session_start();
session_destroy();
// All roles land on login page after logout
header("Location: login.php");
exit;
?>
