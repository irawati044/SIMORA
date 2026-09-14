<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pengurus_id = (int) $_POST["id"];
} elseif (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $pengurus_id = (int) $_GET["id"];
} else {
    header("Location: pengurus.php");
    exit;
}

$query_data = $koneksi->prepare(
    "SELECT
        pg.id,
        pg.organisasi_id,
        pg.jabatan,
        u.nama,
        u.email
    FROM pengurus pg
    JOIN users u ON pg.user_id = u.id
    WHERE pg.id = ?"
);

$query_data->bind_param("i", $pengurus_id);
$query_data->execute();
$data = $query_data->get_result();

if ($data->num_rows === 0) {
    exit("Data pengurus tidak ditemukan.");
}

$pengurus = $data->fetch_assoc();

$nama = $pengurus["nama"];
$email = $pengurus["email"];
$organisasi_id = $pengurus["organisasi_id"];
$jabatan = $pengurus["jabatan"];

$query_organisasi = $koneksi->prepare(
    "SELECT id, nama FROM organisasi ORDER BY nama ASC"
);
$query_organisasi->execute();
$data_organisasi = $query_organisasi->get_result();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $email = trim($_POST["email"]);
    $organisasi_id = $_POST["organisasi_id"];
    $jabatan = trim($_POST["jabatan"]);

    if (empty($nama) || empty($email) || empty($organisasi_id) || empty($jabatan)) {
        $pesan_error = "Semua data wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid.";
    } elseif (!ctype_digit($organisasi_id)) {
        $pesan_error = "Organisasi tidak valid.";
    } else {
        $cek_email = $koneksi->prepare(
            "SELECT id
             FROM users
             WHERE email = ?
               AND id != (
                   SELECT user_id FROM pengurus WHERE id = ?
               )"
        );

        $cek_email->bind_param("si", $email, $pengurus_id);
        $cek_email->execute();
        $cek_email->store_result();

        if ($cek_email->num_rows > 0) {
            $pesan_error = "Email sudah digunakan akun lain.";
        } else {
            try {
                $koneksi->begin_transaction();

                $update_user = $koneksi->prepare(
                    "UPDATE users u
                     JOIN pengurus pg ON u.id = pg.user_id
                     SET u.nama = ?, u.email = ?
                     WHERE pg.id = ?"
                );
                $update_user->bind_param("ssi", $nama, $email, $pengurus_id);
                $update_user->execute();

                $update_pengurus = $koneksi->prepare(
                    "UPDATE pengurus
                     SET organisasi_id = ?, jabatan = ?
                     WHERE id = ?"
                );
                $update_pengurus->bind_param(
                    "isi",
                    $organisasi_id,
                    $jabatan,
                    $pengurus_id
                );
                $update_pengurus->execute();

                $koneksi->commit();

                header("Location: pengurus.php?sukses=1");
                exit;
            } catch (Exception $e) {
                $koneksi->rollback();
                $pesan_error = "Data pengurus gagal diperbarui.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Edit Pengurus - SIMORA</title>
</head>
<body>
    <h1>Edit Pengurus</h1>

    <p><a href="pengurus.php">Kembali</a></p>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $pengurus_id; ?>">

        <p>
            <label>Nama</label><br>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
        </p>

        <p>
            <label>Email</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </p>

        <p>
            <label>Organisasi</label><br>
            <select name="organisasi_id" required>
                <?php while ($organisasi = $data_organisasi->fetch_assoc()) : ?>
                    <option
                        value="<?php echo $organisasi["id"]; ?>"
                        <?php echo $organisasi_id == $organisasi["id"] ? "selected" : ""; ?>
                    >
                        <?php echo htmlspecialchars($organisasi["nama"]); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </p>

        <p>
            <label>Jabatan</label><br>
            <input type="text" name="jabatan" value="<?php echo htmlspecialchars($jabatan); ?>" required>
        </p>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>