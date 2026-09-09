<?php
require_once "../config/auth.php";

wajib_role("mahasiswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Mahasiswa - SIMORA</title>
</head>
<body>
    <h1>Dashboard Mahasiswa</h1>

    <hr>

    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</h2>

    <p>Berikut data akun Anda:</p>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($_SESSION["nama"]); ?></li>
        <li>Email: <?php echo htmlspecialchars($_SESSION["email"]); ?></li>
        <li>Role: Mahasiswa</li>
        <li>Status akun: Aktif</li>
    </ul>

    <hr>

    <h3>Menu yang akan tersedia</h3>

    <ul>
        <li>Melihat daftar organisasi</li>
        <li>Melihat organisasi yang membuka pendaftaran</li>
        <li>Mendaftar organisasi</li>
        <li>Melihat status pendaftaran</li>
        <li>Melihat jadwal wawancara</li>
        <li>Melihat hasil seleksi</li>
    </ul>

    <a href="../logout.php">Logout</a>
</body>
</html>