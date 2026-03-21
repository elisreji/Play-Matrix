<?php
session_start();
$_SESSION['user'] = 'admin@gmail.com';
header("Location: admin.php");
exit;
?>
