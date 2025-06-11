<?php
session_start();
require '../includes/config.php';
if (!isset($_SESSION['user_id'])) header("Location: ../login.php");

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Ambil data tugas yang mau diedit
$tugas = $conn->query("SELECT * FROM tugas WHERE id = $id AND user_id = $user_id")->fetch_assoc();

if (!$tugas) {
    die("Tugas tidak ditemukan atau bukan milik Anda.");
}

if (isset($_POST['update'])) {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $tanggal = $_POST['tanggal_jatuh_tempo'];

    $stmt = $conn->prepare("UPDATE tugas SET judul=?, deskripsi=?, tanggal_jatuh_tempo=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sssii", $judul, $deskripsi, $tanggal, $id, $user_id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>Edit Tugas</h3>
    <form method="POST">
        <input type="text" name="judul" class="form-control mb-2" value="<?= $tugas['judul'] ?>" required>
        <textarea name="deskripsi" class="form-control mb-2"><?= $tugas['deskripsi'] ?></textarea>
        <input type="date" name="tanggal_jatuh_tempo" class="form-control mb-3" value="<?= $tugas['tanggal_jatuh_tempo'] ?>" required>
        <button type="submit" name="update" class="btn btn-primary">Update</button>
        <a href="index.php" class="btn btn-secondary">Batal</a>
    </form>
</div>
</body>
</html>
