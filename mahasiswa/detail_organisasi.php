<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: organisasi.php");
    exit;
}

$id_organisasi = (int) $_GET["id"];

$query = $koneksi->prepare(
    "SELECT nama, kategori, deskripsi, kegiatan, persyaratan, periode_pendaftaran, status
     FROM organisasi
     WHERE id = ?"
);

$query->bind_param("i", $id_organisasi);
$query->execute();

$data = $query->get_result();

if ($data->num_rows === 0) {
    exit("Data organisasi tidak ditemukan.");
}

$organisasi = $data->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Detail Organisasi - SIMORA</title>
</head>
<body>
    <h1><?php echo htmlspecialchars($organisasi["nama"]); ?></h1>

    <p>
        <a href="organisasi.php">Kembali ke Daftar Organisasi</a> |
        <a href="dashboard.php">Dashboard</a>
    </p>

    <hr>

    <p><strong>Kategori:</strong> <?php echo htmlspecialchars($organisasi["kategori"]); ?></p>
    <p><strong>Periode Pendaftaran:</strong> <?php echo htmlspecialchars($organisasi["periode_pendaftaran"]); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars(ucfirst($organisasi["status"])); ?></p>

    <h2>Deskripsi Organisasi</h2>
    <p><?php echo nl2br(htmlspecialchars($organisasi["deskripsi"])); ?></p>

    <h2>Kegiatan Organisasi</h2>
    <p><?php echo nl2br(htmlspecialchars($organisasi["kegiatan"])); ?></p>

    <h2>Persyaratan Berkas</h2>
    <p><?php echo nl2br(htmlspecialchars($organisasi["persyaratan"])); ?></p>

    <?php if ($organisasi["status"] === "buka") : ?>
        <p><strong>Pendaftaran sedang dibuka.</strong></p>

        <a href="daftar.php?id=<?php echo $id_organisasi; ?>">
            Daftar Organisasi Ini
        </a>
    <?php else : ?>
        <p><strong>Pendaftaran sedang ditutup.</strong></p>
    <?php endif; ?>
</body>
</html>

<?php
$query->close();
?>