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

    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</h2>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($_SESSION["nama"]); ?></li>
        <li>Email: <?php echo htmlspecialchars($_SESSION["email"]); ?></li>
        <li>Role: Pengurus</li>
    </ul>

    <hr>

    <h3>Menu</h3>

    <ul>
        <li><a href="pendaftar.php">Data Pendaftar Organisasi</a></li>
    </ul>

    <a href="../logout.php">Logout</a>
</body>
</html>