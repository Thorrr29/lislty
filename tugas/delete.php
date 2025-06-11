<?php
session_start();
require '../includes/config.php';
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Pastikan hanya user yang punya tugas yang bisa hapus
$conn->query("DELETE FROM tugas WHERE id=$id AND user_id=$user_id");

header("Location: index.php");
exit();
