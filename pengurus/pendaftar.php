<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];

$query = $koneksi->prepare(
    "SELECT
        p.id,
        u.nama,
        m.nim,
        m.prodi,
        m.angkatan,
        o.nama AS nama_organisasi,
        p.tgl_daftar,
        p.status
    FROM pengurus pg
    JOIN organisasi o ON pg.organisasi_id = o.id
    JOIN pendaftaran p ON p.organisasi_id = o.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE pg.user_id = ?
    ORDER BY p.tgl_daftar DESC"
);

$query->bind_param("i", $user_id);
$query->execute();
$data_pendaftar = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Data Pendaftar - SIMORA</title>
</head>
<body>
    <h1>Data Pendaftar Organisasi</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Status seleksi berkas berhasil diperbarui.</p>
    <?php endif; ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama Mahasiswa</th>
            <th>NIM</th>
            <th>Organisasi</th>
            <th>Tanggal Daftar</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php if ($data_pendaftar->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($pendaftar = $data_pendaftar->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["tgl_daftar"]); ?></td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            ucwords(str_replace("_", " ", $pendaftar["status"]))
                        );
                        ?>
                    </td>
                    <td>
                        <a href="detail_pendaftar.php?id=<?php echo $pendaftar["id"]; ?>">
                            Lihat Berkas
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="7">Belum ada pendaftar untuk organisasi Anda.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query->close();
?>