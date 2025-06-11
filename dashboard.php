<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark px-3">
    <a class="navbar-brand" href="#">To-Do App</a>
    <a href="logout.php" class="btn btn-outline-light">Logout</a>
</nav>

<div class="container mt-4">
    <h3>Selamat datang!</h3>
    <a href="tugas/index.php" class="btn btn-primary mt-3">Lihat Tugas</a>
</div>
</body>
</html>
