<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = (int) $_POST["id"];
} elseif (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $id = (int) $_GET["id"];
} else {
    header("Location: organisasi.php");
    exit;
}

$query_data = $koneksi->prepare(
    "SELECT nama, kategori
     FROM organisasi
     WHERE id = ?"
);

$query_data->bind_param("i", $id);
$query_data->execute();
$data = $query_data->get_result();

if ($data->num_rows === 0) {
    exit("Organisasi tidak ditemukan.");
}

$organisasi = $data->fetch_assoc();

$nama = $organisasi["nama"];
$kategori = $organisasi["kategori"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $kategori = trim($_POST["kategori"]);

    if (empty($nama) || empty($kategori)) {
        $pesan_error = "Nama organisasi dan kategori wajib diisi.";
    } else {
        $query_update = $koneksi->prepare(
            "UPDATE organisasi
             SET nama = ?, kategori = ?
             WHERE id = ?"
        );

        $query_update->bind_param("ssi", $nama, $kategori, $id);

        if ($query_update->execute()) {
            header("Location: organisasi.php?sukses=1");
            exit;
        }

        $pesan_error = "Data organisasi gagal diperbarui.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Edit Organisasi - SIMORA</title>
</head>
<body>
    <h1>Edit Organisasi</h1>

    <p><a href="organisasi.php">Kembali</a></p>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $id; ?>">

        <p>
            <label>Nama Organisasi</label><br>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
        </p>

        <p>
            <label>Kategori</label><br>
            <input type="text" name="kategori" value="<?php echo htmlspecialchars($kategori); ?>" required>
        </p>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>