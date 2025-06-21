<?php
session_start();
session_destroy();
header('Location: /todo-list-app/auth/login.php');
exit();
?>
