<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

$user_id = $_SESSION["user_id"];

$query_mahasiswa = $koneksi->prepare(
    "SELECT id FROM mahasiswa WHERE user_id = ?"
);

$query_mahasiswa->bind_param("i", $user_id);
$query_mahasiswa->execute();
$data_mahasiswa = $query_mahasiswa->get_result();

if ($data_mahasiswa->num_rows === 0) {
    exit("Data mahasiswa tidak ditemukan.");
}

$mahasiswa = $data_mahasiswa->fetch_assoc();
$mahasiswa_id = $mahasiswa["id"];

$query_pendaftaran = $koneksi->prepare(
    "SELECT p.id, o.nama, p.status, p.tgl_daftar
     FROM pendaftaran p
     JOIN organisasi o ON p.organisasi_id = o.id
     WHERE p.mahasiswa_id = ?
     ORDER BY p.tgl_daftar DESC"
);

$query_pendaftaran->bind_param("i", $mahasiswa_id);
$query_pendaftaran->execute();
$data_pendaftaran = $query_pendaftaran->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Status Pendaftaran - SIMORA</title>
</head>
<body>
    <h1>Status Pendaftaran Saya</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="organisasi.php">Daftar Organisasi</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Pendaftaran berhasil dikirim.</p>
    <?php endif; ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Organisasi</th>
            <th>Tanggal Daftar</th>
            <th>Status</th>
        </tr>

        <?php if ($data_pendaftaran->num_rows > 0) : ?>
            <?php $nomor = 1; ?>
            <?php while ($pendaftaran = $data_pendaftaran->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($pendaftaran["nama"]); ?></td>
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
                <td colspan="4">Belum ada pendaftaran organisasi.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query_mahasiswa->close();
$query_pendaftaran->close();
?>