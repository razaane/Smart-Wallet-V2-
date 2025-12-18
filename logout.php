<?php 
session_start();
unset($_SESSION['logged_in']);
unset($_SESSION['user_email']);
session_destroy();
header("Location: login.php");
exit;
?>