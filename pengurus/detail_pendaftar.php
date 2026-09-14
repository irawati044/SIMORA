<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: pendaftar.php");
    exit;
}

$pendaftaran_id = (int) $_GET["id"];
$user_id = $_SESSION["user_id"];

$query_pendaftar = $koneksi->prepare(
    "SELECT
        p.id,
        p.status,
        p.tgl_daftar,
        u.nama,
        u.email,
        m.nim,
        m.prodi,
        m.angkatan,
        o.nama AS nama_organisasi
    FROM pendaftaran p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON pg.organisasi_id = o.id
    WHERE p.id = ? AND pg.user_id = ?"
);

$query_pendaftar->bind_param("ii", $pendaftaran_id, $user_id);
$query_pendaftar->execute();
$data_pendaftar = $query_pendaftar->get_result();

if ($data_pendaftar->num_rows === 0) {
    exit("Data pendaftar tidak ditemukan atau Anda tidak memiliki akses.");
}

$pendaftar = $data_pendaftar->fetch_assoc();

$query_berkas = $koneksi->prepare(
    "SELECT nama_file, jenis
     FROM berkas
     WHERE pendaftaran_id = ?"
);

$query_berkas->bind_param("i", $pendaftaran_id);
$query_berkas->execute();
$data_berkas = $query_berkas->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Detail Pendaftar - SIMORA</title>
</head>
<body>
    <h1>Detail Pendaftar</h1>

    <p><a href="pendaftar.php">Kembali ke Data Pendaftar</a></p>

    <h2>Data Mahasiswa</h2>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($pendaftar["nama"]); ?></li>
        <li>Email: <?php echo htmlspecialchars($pendaftar["email"]); ?></li>
        <li>NIM: <?php echo htmlspecialchars($pendaftar["nim"]); ?></li>
        <li>Program Studi: <?php echo htmlspecialchars($pendaftar["prodi"]); ?></li>
        <li>Angkatan: <?php echo htmlspecialchars($pendaftar["angkatan"]); ?></li>
        <li>Organisasi: <?php echo htmlspecialchars($pendaftar["nama_organisasi"]); ?></li>
        <li>Status: <?php echo htmlspecialchars(ucwords(str_replace("_", " ", $pendaftar["status"]))); ?></li>
    </ul>

    <h2>Berkas Pendaftaran</h2>

    <?php if ($data_berkas->num_rows > 0) : ?>
        <ul>
            <?php while ($berkas = $data_berkas->fetch_assoc()) : ?>
                <li>
                    <?php echo htmlspecialchars($berkas["jenis"]); ?>:
                    <a
                        href="../assets/uploads/berkas/<?php echo rawurlencode($berkas["nama_file"]); ?>"
                        target="_blank"
                    >
                        Lihat Berkas
                    </a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else : ?>
        <p>Berkas belum tersedia.</p>
    <?php endif; ?>

    <?php if ($pendaftar["status"] === "menunggu") : ?>
        <hr>

        <h2>Seleksi Berkas</h2>

        <p>Pastikan seluruh berkas sudah diperiksa sebelum menentukan hasil.</p>

        <form method="POST" action="proses_seleksi.php">
            <input type="hidden" name="pendaftaran_id" value="<?php echo $pendaftaran_id; ?>">

            <button type="submit" name="status" value="lolos_berkas">
                Lolos Seleksi Berkas
            </button>

            <button type="submit" name="status" value="ditolak_berkas">
                Tolak Seleksi Berkas
            </button>
        </form>
    <?php else : ?>
        <p>
            <strong>
                Seleksi berkas sudah dilakukan dan tidak dapat diubah dari halaman ini.
            </strong>
        </p>
    <?php endif; ?>
</body>
</html>

<?php
$query_pendaftar->close();
$query_berkas->close();
?>