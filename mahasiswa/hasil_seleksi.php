<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

$user_id = $_SESSION["user_id"];

$query = $koneksi->prepare(
    "SELECT
        o.nama AS nama_organisasi,
        p.status,
        pn.nilai,
        pn.keputusan,
        pn.catatan
    FROM pendaftaran p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN organisasi o ON p.organisasi_id = o.id
    LEFT JOIN wawancara w ON w.pendaftaran_id = p.id
    LEFT JOIN penilaian pn ON pn.wawancara_id = w.id
    WHERE m.user_id = ?
      AND p.status IN ('diterima', 'ditolak')
    ORDER BY o.nama ASC"
);

$query->bind_param("i", $user_id);
$query->execute();
$data_hasil = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Hasil Seleksi Saya - SIMORA</title>
</head>
<body>
    <h1>Hasil Seleksi Saya</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="status_pendaftaran.php">Status Pendaftaran</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Organisasi</th>
            <th>Nilai Wawancara</th>
            <th>Keputusan Wawancara</th>
            <th>Hasil Akhir</th>
            <th>Catatan</th>
        </tr>

        <?php if ($data_hasil->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($hasil = $data_hasil->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>

                    <td>
                        <?php echo htmlspecialchars($hasil["nama_organisasi"]); ?>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($hasil["nilai"] ?? "-"); ?>
                    </td>

                    <td>
                        <?php
                        if ($hasil["keputusan"] !== null) {
                            echo htmlspecialchars(
                                ucwords(
                                    str_replace("_", " ", $hasil["keputusan"])
                                )
                            );
                        } else {
                            echo "-";
                        }
                        ?>
                    </td>

                    <td>
                        <strong>
                            <?php
                            echo htmlspecialchars(
                                ucwords(
                                    str_replace("_", " ", $hasil["status"])
                                )
                            );
                            ?>
                        </strong>
                    </td>

                    <td>
                        <?php echo htmlspecialchars($hasil["catatan"] ?? "-"); ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="6">Hasil seleksi belum tersedia.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query->close();
?>