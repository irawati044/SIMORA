<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: organisasi.php");
    exit;
}

$id_organisasi = (int) $_GET["id"];
$status_buka = "buka";

$query_organisasi = $koneksi->prepare(
    "SELECT id, nama, persyaratan
     FROM organisasi
     WHERE id = ? AND status = ?"
);

$query_organisasi->bind_param("is", $id_organisasi, $status_buka);
$query_organisasi->execute();
$data_organisasi = $query_organisasi->get_result();

if ($data_organisasi->num_rows === 0) {
    exit("Organisasi tidak ditemukan atau pendaftaran sedang ditutup.");
}

$organisasi = $data_organisasi->fetch_assoc();

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

$query_cek = $koneksi->prepare(
    "SELECT id FROM pendaftaran WHERE mahasiswa_id = ? AND organisasi_id = ?"
);

$query_cek->bind_param("ii", $mahasiswa_id, $id_organisasi);
$query_cek->execute();
$query_cek->store_result();

$sudah_daftar = $query_cek->num_rows > 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Pendaftaran Organisasi - SIMORA</title>
</head>
<body>
    <h1>Pendaftaran Organisasi</h1>

    <p><a href="detail_organisasi.php?id=<?php echo $id_organisasi; ?>">Kembali</a></p>

    <h2><?php echo htmlspecialchars($organisasi["nama"]); ?></h2>

    <?php if ($sudah_daftar) : ?>
        <p style="color: red;">
            Anda sudah pernah mendaftar pada organisasi ini.
        </p>

        <a href="status_pendaftaran.php">Lihat Status Pendaftaran</a>
    <?php else : ?>
        <h3>Persyaratan</h3>
        <p><?php echo nl2br(htmlspecialchars($organisasi["persyaratan"])); ?></p>

        <p>
            Format file yang diterima: PDF, JPG, JPEG, atau PNG.<br>
            Ukuran maksimal setiap file: 5 MB.
        </p>

        <form method="POST" action="proses_daftar.php" enctype="multipart/form-data">
            <input type="hidden" name="organisasi_id" value="<?php echo $id_organisasi; ?>">

            <p>
                <label>Kartu Tanda Mahasiswa (KTM)</label><br>
                <input type="file" name="ktm" accept=".pdf,.jpg,.jpeg,.png" required>
            </p>

            <p>
                <label>Curriculum Vitae (CV)</label><br>
                <input type="file" name="cv" accept=".pdf,.jpg,.jpeg,.png" required>
            </p>

            <p>
                <label>Pas Foto</label><br>
                <input type="file" name="pas_foto" accept=".jpg,.jpeg,.png" required>
            </p>

            <p>
                <label>Berkas Tambahan Sesuai Persyaratan Organisasi</label><br>
                <input type="file" name="berkas_tambahan" accept=".pdf,.jpg,.jpeg,.png">
            </p>

            <button type="submit">Kirim Pendaftaran</button>
        </form>
    <?php endif; ?>
</body>
</html>

<?php
$query_organisasi->close();
$query_mahasiswa->close();
$query_cek->close();
?>