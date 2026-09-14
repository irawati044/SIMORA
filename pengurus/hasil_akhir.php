<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];
$status_proses = "jadwal_wawancara";

$query_belum_ditetapkan = $koneksi->prepare(
    "SELECT DISTINCT
        p.id,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi,
        pn.nilai,
        pn.keputusan,
        pn.catatan
    FROM pendaftaran p
    JOIN wawancara w ON w.pendaftaran_id = p.id
    JOIN penilaian pn ON pn.wawancara_id = w.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON w.pengurus_id = pg.id
    WHERE pg.user_id = ?
      AND p.status = ?
      AND w.status = 'selesai'
    ORDER BY u.nama ASC"
);

$query_belum_ditetapkan->bind_param("is", $user_id, $status_proses);
$query_belum_ditetapkan->execute();
$data_belum_ditetapkan = $query_belum_ditetapkan->get_result();

$query_hasil = $koneksi->prepare(
    "SELECT DISTINCT
        u.nama,
        m.nim,
        o.nama AS nama_organisasi,
        p.status,
        pn.nilai,
        pn.keputusan
    FROM pendaftaran p
    JOIN wawancara w ON w.pendaftaran_id = p.id
    JOIN penilaian pn ON pn.wawancara_id = w.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON w.pengurus_id = pg.id
    WHERE pg.user_id = ?
      AND p.status IN ('diterima', 'ditolak')
    ORDER BY u.nama ASC"
);

$query_hasil->bind_param("i", $user_id);
$query_hasil->execute();
$data_hasil = $query_hasil->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Penetapan Hasil Akhir - SIMORA</title>
</head>
<body>
    <h1>Penetapan Hasil Akhir</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Hasil akhir berhasil ditetapkan.</p>
    <?php endif; ?>

    <h2>Menunggu Penetapan Hasil Akhir</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Organisasi</th>
            <th>Nilai</th>
            <th>Keputusan Wawancara</th>
            <th>Catatan</th>
            <th>Hasil Akhir</th>
        </tr>

        <?php if ($data_belum_ditetapkan->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($pendaftar = $data_belum_ditetapkan->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($pendaftar["nilai"]); ?></td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            ucwords(str_replace("_", " ", $pendaftar["keputusan"]))
                        );
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($pendaftar["catatan"]); ?></td>
                    <td>
                        <form method="POST" action="proses_hasil_akhir.php">
                            <input type="hidden" name="pendaftaran_id" value="<?php echo $pendaftar["id"]; ?>">

                            <button type="submit" name="status" value="diterima">
                                Diterima
                            </button>

                            <button type="submit" name="status" value="ditolak">
                                Ditolak
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="8">Belum ada pendaftar yang menunggu hasil akhir.</td>
            </tr>
        <?php endif; ?>
    </table>

    <hr>

    <h2>Riwayat Hasil Akhir</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Organisasi</th>
            <th>Nilai Wawancara</th>
            <th>Keputusan Wawancara</th>
            <th>Hasil Akhir</th>
        </tr>

        <?php if ($data_hasil->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($hasil = $data_hasil->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($hasil["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($hasil["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($hasil["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($hasil["nilai"]); ?></td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            ucwords(str_replace("_", " ", $hasil["keputusan"]))
                        );
                        ?>
                    </td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            ucwords(str_replace("_", " ", $hasil["status"]))
                        );
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="7">Belum ada hasil akhir.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query_belum_ditetapkan->close();
$query_hasil->close();
?>