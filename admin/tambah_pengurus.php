<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$pesan_error = "";

$nama = "";
$email = "";
$organisasi_id = "";
$jabatan = "";

$query_organisasi = $koneksi->prepare(
    "SELECT id, nama FROM organisasi ORDER BY nama ASC"
);

$query_organisasi->execute();
$data_organisasi = $query_organisasi->get_result();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $organisasi_id = $_POST["organisasi_id"];
    $jabatan = trim($_POST["jabatan"]);

    if (
        empty($nama) ||
        empty($email) ||
        empty($password) ||
        empty($organisasi_id) ||
        empty($jabatan)
    ) {
        $pesan_error = "Semua data wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid.";
    } elseif (strlen($password) < 8) {
        $pesan_error = "Password minimal 8 karakter.";
    } elseif (!ctype_digit($organisasi_id)) {
        $pesan_error = "Organisasi tidak valid.";
    } else {
        $cek_email = $koneksi->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $cek_email->bind_param("s", $email);
        $cek_email->execute();
        $cek_email->store_result();

        if ($cek_email->num_rows > 0) {
            $pesan_error = "Email sudah terdaftar.";
        } else {
            try {
                $koneksi->begin_transaction();

                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $role = "pengurus";

                $simpan_user = $koneksi->prepare(
                    "INSERT INTO users (nama, email, password, role)
                     VALUES (?, ?, ?, ?)"
                );

                $simpan_user->bind_param(
                    "ssss",
                    $nama,
                    $email,
                    $password_hash,
                    $role
                );

                $simpan_user->execute();

                $user_id = $koneksi->insert_id;

                $simpan_pengurus = $koneksi->prepare(
                    "INSERT INTO pengurus (user_id, organisasi_id, jabatan)
                     VALUES (?, ?, ?)"
                );

                $simpan_pengurus->bind_param(
                    "iis",
                    $user_id,
                    $organisasi_id,
                    $jabatan
                );

                $simpan_pengurus->execute();

                $koneksi->commit();

                header("Location: pengurus.php?sukses=1");
                exit;
            } catch (Exception $e) {
                $koneksi->rollback();
                $pesan_error = "Data pengurus gagal disimpan.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Tambah Pengurus - SIMORA</title>
</head>
<body>
    <h1>Tambah Pengurus Organisasi</h1>

    <p><a href="pengurus.php">Kembali</a></p>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label>Nama Pengurus</label><br>
            <input type="text" name="nama" value="<?php echo htmlspecialchars($nama); ?>" required>
        </p>

        <p>
            <label>Email</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </p>

        <p>
            <label>Password</label><br>
            <input type="password" name="password" required>
        </p>

        <p>
            <label>Organisasi</label><br>
            <select name="organisasi_id" required>
                <option value="">Pilih organisasi</option>

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

        <button type="submit">Simpan Pengurus</button>
    </form>
</body>
</html>