<?php
require_once "../config/auth.php";

wajib_role("pengurus");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengurus - SIMORA</title>
</head>
<body>
    <h1>Dashboard Pengurus Organisasi</h1>

    <hr>

    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</h2>

    <p>Berikut data akun Anda:</p>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($_SESSION["nama"]); ?></li>
        <li>Email: <?php echo htmlspecialchars($_SESSION["email"]); ?></li>
        <li>Role: Pengurus</li>
        <li>Status akun: Aktif</li>
    </ul>

    <hr>

    <h3>Menu yang akan tersedia</h3>

    <ul>
        <li>Mengelola informasi organisasi</li>
        <li>Mengatur periode pendaftaran</li>
        <li>Melihat data pendaftar</li>
        <li>Melakukan seleksi berkas</li>
        <li>Mengatur jadwal wawancara</li>
        <li>Memberi penilaian wawancara</li>
        <li>Menetapkan hasil akhir</li>
    </ul>

    <a href="../logout.php">Logout</a>
</body>
</html>