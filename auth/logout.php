<?php
session_start();
session_destroy();
header('Location: /lislty/auth/login.php');
exit();
?>
