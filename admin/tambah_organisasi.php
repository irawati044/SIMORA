<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$pesan_error = "";
$nama = "";
$kategori = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $kategori = trim($_POST["kategori"]);

    if (empty($nama) || empty($kategori)) {
        $pesan_error = "Nama organisasi dan kategori wajib diisi.";
    } else {
        $deskripsi = "Belum diatur oleh pengurus.";
        $kegiatan = "Belum diatur oleh pengurus.";
        $persyaratan = "Belum diatur oleh pengurus.";
        $periode_pendaftaran = "Belum diatur oleh pengurus.";
        $status = "tutup";

        $query = $koneksi->prepare(
            "INSERT INTO organisasi
            (nama, kategori, deskripsi, kegiatan, persyaratan, periode_pendaftaran, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $query->bind_param(
            "sssssss",
            $nama,
            $kategori,
            $deskripsi,
            $kegiatan,
            $persyaratan,
            $periode_pendaftaran,
            $status
        );

        if ($query->execute()) {
            header("Location: organisasi.php?sukses=1");
            exit;
        }

        $pesan_error = "Organisasi gagal ditambahkan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Tambah Organisasi - SIMORA</title>
</head>
<body>
    <h1>Tambah Organisasi</h1>

    <p><a href="organisasi.php">Kembali</a></p>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label>Nama Organisasi</label><br>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
        </p>

        <p>
            <label>Kategori</label><br>
            <input type="text" name="kategori" value="<?php echo htmlspecialchars($kategori); ?>" required>
        </p>

        <p>
            Organisasi baru otomatis berstatus <strong>tutup</strong>.
            Pengurus akan mengatur informasi dan membuka pendaftaran.
        </p>

        <button type="submit">Simpan Organisasi</button>
    </form>
</body>
</html>