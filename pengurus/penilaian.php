<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];
$status_dijadwalkan = "dijadwalkan";

$query_belum_dinilai = $koneksi->prepare(
    "SELECT
        w.id,
        w.jadwal,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi
    FROM wawancara w
    JOIN pendaftaran p ON w.pendaftaran_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON w.pengurus_id = pg.id
    LEFT JOIN penilaian pn ON pn.wawancara_id = w.id
    WHERE pg.user_id = ?
      AND w.status = ?
      AND pn.id IS NULL
    ORDER BY w.jadwal ASC"
);

$query_belum_dinilai->bind_param("is", $user_id, $status_dijadwalkan);
$query_belum_dinilai->execute();
$data_belum_dinilai = $query_belum_dinilai->get_result();

$query_sudah_dinilai = $koneksi->prepare(
    "SELECT
        pn.id AS penilaian_id,
        w.jadwal,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi,
        pn.nilai,
        pn.keputusan,
        pn.catatan
    FROM penilaian pn
    JOIN wawancara w ON pn.wawancara_id = w.id
    JOIN pendaftaran p ON w.pendaftaran_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON w.pengurus_id = pg.id
    WHERE pg.user_id = ?
    ORDER BY w.jadwal DESC"
);

$query_sudah_dinilai->bind_param("i", $user_id);
$query_sudah_dinilai->execute();
$data_sudah_dinilai = $query_sudah_dinilai->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Penilaian Wawancara - SIMORA</title>
</head>
<body>
    <h1>Penilaian Wawancara</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Data penilaian berhasil diperbarui.</p>
    <?php endif; ?>

    <h2>Wawancara yang Belum Dinilai</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Organisasi</th>
            <th>Jadwal</th>
            <th>Aksi</th>
        </tr>

        <?php if ($data_belum_dinilai->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($wawancara = $data_belum_dinilai->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($wawancara["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($wawancara["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($wawancara["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($wawancara["jadwal"]); ?></td>
                    <td>
                        <a href="input_penilaian.php?id=<?php echo $wawancara["id"]; ?>">
                            Beri Penilaian
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="6">Belum ada wawancara yang perlu dinilai.</td>
            </tr>
        <?php endif; ?>
    </table>

    <hr>

    <h2>Riwayat Penilaian</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>NIM</th>
            <th>Organisasi</th>
            <th>Nilai</th>
            <th>Keputusan Wawancara</th>
            <th>Catatan</th>
            <th>Aksi</th>
        </tr>

        <?php if ($data_sudah_dinilai->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($penilaian = $data_sudah_dinilai->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($penilaian["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($penilaian["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($penilaian["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($penilaian["nilai"]); ?></td>
                    <td>
                        <?php
                        echo htmlspecialchars(
                            ucwords(str_replace("_", " ", $penilaian["keputusan"]))
                        );
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($penilaian["catatan"]); ?></td>
                    <td>
                        <a href="edit_penilaian.php?id=<?php echo $penilaian["penilaian_id"]; ?>">
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="hapus_penilaian.php"
                            style="display: inline;"
                            onsubmit="return confirm('Yakin ingin menghapus penilaian ini?');"
                        >
                            <input
                                type="hidden"
                                name="penilaian_id"
                                value="<?php echo $penilaian["penilaian_id"]; ?>"
                            >

                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="8">Belum ada penilaian wawancara.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query_belum_dinilai->close();
$query_sudah_dinilai->close();
?>