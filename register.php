<?php
require_once "config/koneksi.php";

$pesan_sukses = "";
$pesan_error = "";

$nama = "";
$email = "";
$nim = "";
$prodi = "";
$angkatan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $nim = trim($_POST["nim"]);
    $prodi = trim($_POST["prodi"]);
    $angkatan = trim($_POST["angkatan"]);

    if (empty($nama) || empty($email) || empty($password) || empty($nim) || empty($prodi) || empty($angkatan)) {
        $pesan_error = "Semua data wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid.";
    } elseif (strlen($password) < 8) {
        $pesan_error = "Password minimal 8 karakter.";
    } else {
        $cek_email = $koneksi->prepare("SELECT id FROM users WHERE email = ?");
        $cek_email->bind_param("s", $email);
        $cek_email->execute();
        $cek_email->store_result();

        if ($cek_email->num_rows > 0) {
            $pesan_error = "Email sudah terdaftar.";
        } else {
            $cek_nim = $koneksi->prepare("SELECT id FROM mahasiswa WHERE nim = ?");
            $cek_nim->bind_param("s", $nim);
            $cek_nim->execute();
            $cek_nim->store_result();

            if ($cek_nim->num_rows > 0) {
                $pesan_error = "NIM sudah terdaftar.";
            } else {
                $koneksi->begin_transaction();

                try {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $role = "mahasiswa";

                    $simpan_user = $koneksi->prepare(
                        "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)"
                    );
                    $simpan_user->bind_param("ssss", $nama, $email, $password_hash, $role);
                    $simpan_user->execute();

                    $user_id = $koneksi->insert_id;

                    $simpan_mahasiswa = $koneksi->prepare(
                        "INSERT INTO mahasiswa (user_id, nim, prodi, angkatan) VALUES (?, ?, ?, ?)"
                    );
                    $simpan_mahasiswa->bind_param("issi", $user_id, $nim, $prodi, $angkatan);
                    $simpan_mahasiswa->execute();

                    $koneksi->commit();

                    $pesan_sukses = "Registrasi berhasil. Akun mahasiswa sudah dibuat.";

                    $nama = "";
                    $email = "";
                    $nim = "";
                    $prodi = "";
                    $angkatan = "";
                } catch (Exception $e) {
                    $koneksi->rollback();
                    $pesan_error = "Registrasi gagal. Silakan coba lagi.";
                }
            }

            $cek_nim->close();
        }

        $cek_email->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Registrasi Mahasiswa - SIMORA</title>
</head>
<body>
    <h1>Registrasi Mahasiswa SIMORA</h1>

    <?php if (!empty($pesan_sukses)) : ?>
        <p style="color: green;"><?php echo $pesan_sukses; ?></p>
    <?php endif; ?>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label>Nama Lengkap</label><br>
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
            <label>NIM</label><br>
            <input type="text" name="nim" value="<?php echo htmlspecialchars($nim); ?>" required>
        </p>

        <p>
            <label>Program Studi</label><br>
            <input type="text" name="prodi" value="<?php echo htmlspecialchars($prodi); ?>" required>
        </p>

        <p>
            <label>Angkatan</label><br>
            <select name="angkatan" required>
                <option value="">Pilih angkatan</option>

                <?php for ($tahun = 2020; $tahun <= date("Y") + 10; $tahun++) : ?>
                    <option value="<?php echo $tahun; ?>"
                        <?php echo ($angkatan == $tahun) ? "selected" : ""; ?>>
                        <?php echo $tahun; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </p>

        <button type="submit">Daftar</button>
    </form>
</body>
</html>