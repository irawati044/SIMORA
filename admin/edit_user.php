<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = (int) $_POST["id"];
} elseif (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $user_id = (int) $_GET["id"];
} else {
    header("Location: users.php");
    exit;
}

$query_data = $koneksi->prepare(
    "SELECT id, nama, email, role
     FROM users
     WHERE id = ?"
);

$query_data->bind_param("i", $user_id);
$query_data->execute();
$data = $query_data->get_result();

if ($data->num_rows === 0) {
    exit("Data pengguna tidak ditemukan.");
}

$user = $data->fetch_assoc();

$nama = $user["nama"];
$email = $user["email"];
$role = $user["role"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = trim($_POST["nama"]);
    $email = trim($_POST["email"]);

    if (empty($nama) || empty($email)) {
        $pesan_error = "Nama dan email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pesan_error = "Format email tidak valid.";
    } else {
        $cek_email = $koneksi->prepare(
            "SELECT id
             FROM users
             WHERE email = ? AND id != ?"
        );

        $cek_email->bind_param("si", $email, $user_id);
        $cek_email->execute();
        $cek_email->store_result();

        if ($cek_email->num_rows > 0) {
            $pesan_error = "Email sudah digunakan pengguna lain.";
        } else {
            $query_update = $koneksi->prepare(
                "UPDATE users
                 SET nama = ?, email = ?
                 WHERE id = ?"
            );

            $query_update->bind_param("ssi", $nama, $email, $user_id);

            if ($query_update->execute()) {
                header("Location: users.php?sukses=1");
                exit;
            }

            $pesan_error = "Data pengguna gagal diperbarui.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Edit Pengguna - SIMORA</title>
</head>
<body>
    <h1>Edit Pengguna</h1>

    <p><a href="users.php">Kembali ke Kelola Pengguna</a></p>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <p><strong>Role:</strong> <?php echo htmlspecialchars(ucfirst($role)); ?></p>

    <form method="POST" action="">
        <input type="hidden" name="id" value="<?php echo $user_id; ?>">

        <p>
            <label>Nama</label><br>
            <input
                type="text"
                name="nama"
                value="<?php echo htmlspecialchars($nama); ?>"
                required
            >
        </p>

        <p>
            <label>Email</label><br>
            <input
                type="email"
                name="email"
                value="<?php echo htmlspecialchars($email); ?>"
                required
            >
        </p>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>