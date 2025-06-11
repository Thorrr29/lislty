<?php
session_start();
require '../includes/config.php';
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM tugas WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Daftar Tugas</h3>
    <a href="create.php" class="btn btn-success mb-3">+ Tambah Tugas</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Deadline</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['judul'] ?></td>
                <td><?= $row['deskripsi'] ?></td>
                <td><?= $row['tanggal_jatuh_tempo'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus tugas ini?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile ?>
        </tbody>
    </table>
</div>
</body>
</html>
