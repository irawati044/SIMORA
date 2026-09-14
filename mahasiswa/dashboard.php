<?php
require_once "../config/auth.php";

wajib_role("mahasiswa");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Dashboard Mahasiswa - SIMORA</title>
</head>
<body>
    <h1>Dashboard Mahasiswa</h1>

    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</h2>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($_SESSION["nama"]); ?></li>
        <li>Email: <?php echo htmlspecialchars($_SESSION["email"]); ?></li>
        <li>Role: Mahasiswa</li>
    </ul>

    <hr>

    <h3>Menu</h3>

<ul>
    <li><a href="organisasi.php">Lihat Daftar Organisasi</a></li>
    <li><a href="status_pendaftaran.php">Status Pendaftaran Saya</a></li>
    <li><a href="jadwal_wawancara.php">Jadwal Wawancara Saya</a></li>
    <li><a href="hasil_seleksi.php">Hasil Seleksi Saya</a></li>
</ul>

    <a href="../logout.php">Logout</a>
</body>
</html>