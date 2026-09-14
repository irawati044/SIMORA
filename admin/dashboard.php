<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$query_users = $koneksi->prepare(
    "SELECT COUNT(*) AS total FROM users"
);
$query_users->execute();
$total_users = $query_users->get_result()->fetch_assoc()["total"];

$query_organisasi = $koneksi->prepare(
    "SELECT COUNT(*) AS total FROM organisasi"
);
$query_organisasi->execute();
$total_organisasi = $query_organisasi->get_result()->fetch_assoc()["total"];

$query_pendaftaran = $koneksi->prepare(
    "SELECT COUNT(*) AS total FROM pendaftaran"
);
$query_pendaftaran->execute();
$total_pendaftaran = $query_pendaftaran->get_result()->fetch_assoc()["total"];

$query_diterima = $koneksi->prepare(
    "SELECT COUNT(*) AS total
     FROM pendaftaran
     WHERE status = 'diterima'"
);
$query_diterima->execute();
$total_diterima = $query_diterima->get_result()->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Dashboard Admin - SIMORA</title>
</head>
<body>
    <h1>Dashboard Admin SIMORA</h1>

    <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION["nama"]); ?>!</h2>

    <hr>

    <h3>Ringkasan Sistem</h3>

    <ul>
        <li>Total pengguna: <?php echo $total_users; ?></li>
        <li>Total organisasi: <?php echo $total_organisasi; ?></li>
        <li>Total pendaftaran: <?php echo $total_pendaftaran; ?></li>
        <li>Total mahasiswa diterima: <?php echo $total_diterima; ?></li>
    </ul>

    <hr>

    <h3>Menu Admin</h3>

    <ul>
        <li>
            <a href="monitoring.php">Monitoring Pendaftaran</a>
        </li>

        <li>
            <a href="users.php">Kelola Pengguna</a>
        </li>

        <li>
            <a href="organisasi.php">Kelola Organisasi</a>
        </li>

        <li>
            <a href="pengurus.php">Kelola Pengurus</a>
        </li>
    </ul>

    <hr>

    <a href="../logout.php">Logout</a>
</body>
</html>

<?php
$query_users->close();
$query_organisasi->close();
$query_pendaftaran->close();
$query_diterima->close();
?>