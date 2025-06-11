<?php
session_start();
require '../includes/config.php';
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");

if (isset($_POST['simpan'])) {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal = $_POST['tanggal_jatuh_tempo'];
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO tugas (user_id, judul, deskripsi, tanggal_jatuh_tempo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $judul, $deskripsi, $tanggal);
    $stmt->execute();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Tambah Tugas</h3>
    <form method="POST">
        <input type="text" name="judul" class="form-control mb-2" placeholder="Judul Tugas" required>
        <textarea name="deskripsi" class="form-control mb-2" placeholder="Deskripsi"></textarea>
        <input type="date" name="tanggal_jatuh_tempo" class="form-control mb-3" required>
        <button type="submit" name="simpan" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
