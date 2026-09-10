<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

$status_buka = "buka";

$query_buka = $koneksi->prepare(
    "SELECT id, nama, kategori, periode_pendaftaran, status
     FROM organisasi
     WHERE status = ?
     ORDER BY nama ASC"
);

$query_buka->bind_param("s", $status_buka);
$query_buka->execute();
$data_buka = $query_buka->get_result();

$query_semua = $koneksi->prepare(
    "SELECT id, nama, kategori, periode_pendaftaran, status
     FROM organisasi
     ORDER BY nama ASC"
);

$query_semua->execute();
$data_semua = $query_semua->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Organisasi - SIMORA</title>
</head>
<body>
    <h1>Daftar Organisasi Mahasiswa</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <hr>

    <h2>Organisasi yang Sedang Membuka Pendaftaran</h2>

    <?php if ($data_buka->num_rows > 0) : ?>
        <?php while ($organisasi = $data_buka->fetch_assoc()) : ?>
            <div>
                <h3><?php echo htmlspecialchars($organisasi["nama"]); ?></h3>

                <p>Kategori: <?php echo htmlspecialchars($organisasi["kategori"]); ?></p>
                <p>Periode: <?php echo htmlspecialchars($organisasi["periode_pendaftaran"]); ?></p>
                <p>Status: <strong>Buka</strong></p>

                <a href="detail_organisasi.php?id=<?php echo $organisasi["id"]; ?>">
                    Lihat Detail dan Persyaratan
                </a>
            </div>

            <hr>
        <?php endwhile; ?>
    <?php else : ?>
        <p>Belum ada organisasi yang membuka pendaftaran.</p>
    <?php endif; ?>

    <h2>Semua Organisasi</h2>

    <?php if ($data_semua->num_rows > 0) : ?>
        <?php while ($organisasi = $data_semua->fetch_assoc()) : ?>
            <div>
                <h3><?php echo htmlspecialchars($organisasi["nama"]); ?></h3>

                <p>Kategori: <?php echo htmlspecialchars($organisasi["kategori"]); ?></p>
                <p>Periode: <?php echo htmlspecialchars($organisasi["periode_pendaftaran"]); ?></p>
                <p>
                    Status:
                    <strong><?php echo htmlspecialchars(ucfirst($organisasi["status"])); ?></strong>
                </p>

                <a href="detail_organisasi.php?id=<?php echo $organisasi["id"]; ?>">
                    Lihat Detail dan Persyaratan
                </a>
            </div>

            <hr>
        <?php endwhile; ?>
    <?php else : ?>
        <p>Data organisasi belum tersedia.</p>
    <?php endif; ?>
</body>
</html>

<?php
$query_buka->close();
$query_semua->close();
?>