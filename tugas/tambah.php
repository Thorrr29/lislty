<?php
include "../includes/auth.php";
include "../includes/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = $_POST["judul"];
    $deskripsi = $_POST["deskripsi"];
    $tanggal = $_POST["tanggal_jatuh_tempo"];
    $user_id = $_SESSION["user_id"];

    $query = "INSERT INTO tugas (user_id, judul, deskripsi, tanggal_jatuh_tempo)
              VALUES ($user_id, '$judul', '$deskripsi', '$tanggal')";
    mysqli_query($conn, $query);
    header("Location: index.php");
}
?>

<form method="POST">
    Judul: <input type="text" name="judul" required><br>
    Deskripsi: <textarea name="deskripsi"></textarea><br>
    Jatuh Tempo: <input type="date" name="tanggal_jatuh_tempo" required><br>
    <button type="submit">Simpan</button>
</form>
