<?php
require_once "../config/auth.php";

wajib_role("admin");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - SIMORA</title>
</head>
<body>
    <h1>Dashboard Admin</h1>

    <hr>

    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</h2>

    <p>Berikut data akun Anda:</p>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($_SESSION["nama"]); ?></li>
        <li>Email: <?php echo htmlspecialchars($_SESSION["email"]); ?></li>
        <li>Role: Admin</li>
        <li>Status akun: Aktif</li>
    </ul>

    <hr>

    <h3>Menu yang akan tersedia</h3>

    <ul>
        <li>Mengelola data pengguna</li>
        <li>Mengelola data organisasi</li>
        <li>Mengelola data pengurus</li>
        <li>Melakukan monitoring pendaftaran</li>
    </ul>

    <a href="../logout.php">Logout</a>
</body>
</html>