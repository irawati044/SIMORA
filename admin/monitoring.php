<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$query = $koneksi->prepare(
    "SELECT
        p.id,
        u.nama AS nama_mahasiswa,
        m.nim,
        m.prodi,
        o.nama AS nama_organisasi,
        p.tgl_daftar,
        p.status
    FROM pendaftaran p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    ORDER BY p.tgl_daftar DESC"
);

$query->execute();
$data_pendaftaran = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Pendaftaran - SIMORA</title>
</head>
<body>
    <h1>Monitoring Pendaftaran</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard Admin</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama Mahasiswa</th>
            <th>NIM</th>
            <th>Program Studi</th>
            <th>Organisasi</th>
            <th>Tanggal Daftar</th>
            <th>Status</th>
        </tr>

        <?php if ($data_pendaftaran->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($pendaftaran = $data_pendaftaran->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($pendaftaran["nama_mahasiswa"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftaran["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftaran["prodi"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftaran["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftaran["tgl_daftar"]); ?></td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            ucwords(str_replace("_", " ", $pendaftaran["status"]))
                        );
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="7">Belum ada data pendaftaran.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query->close();
?>