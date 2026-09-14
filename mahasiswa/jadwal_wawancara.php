<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

$user_id = $_SESSION["user_id"];

$query = $koneksi->prepare(
    "SELECT
        o.nama AS nama_organisasi,
        w.jadwal,
        w.status
    FROM wawancara w
    JOIN pendaftaran p ON w.pendaftaran_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN organisasi o ON p.organisasi_id = o.id
    WHERE m.user_id = ?
    ORDER BY w.jadwal ASC"
);

$query->bind_param("i", $user_id);
$query->execute();
$data_jadwal = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Jadwal Wawancara Saya - SIMORA</title>
</head>
<body>
    <h1>Jadwal Wawancara Saya</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="status_pendaftaran.php">Status Pendaftaran</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Organisasi</th>
            <th>Jadwal Wawancara</th>
            <th>Status</th>
        </tr>

        <?php if ($data_jadwal->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($jadwal = $data_jadwal->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($jadwal["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($jadwal["jadwal"]); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($jadwal["status"])); ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="4">Belum ada jadwal wawancara.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query->close();
?>