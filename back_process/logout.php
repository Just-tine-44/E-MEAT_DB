<?php
session_start();
session_unset();
session_destroy();
header('location: ../users/login.php');
exit;
?>