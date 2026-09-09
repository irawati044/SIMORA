<?php
require_once "config/koneksi.php";
require_once "config/auth.php";

$pesan_error = "";
$email = "";

if (isset($_SESSION["user_id"])) {
    arahkan_dashboard();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {
        $pesan_error = "Email dan password wajib diisi.";
    } else {
        $query = $koneksi->prepare(
            "SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1"
        );

        $query->bind_param("s", $email);
        $query->execute();
        $query->store_result();

        if ($query->num_rows === 1) {
            $query->bind_result($id, $nama, $email_db, $password_hash, $role);
            $query->fetch();

            if (password_verify($password, $password_hash)) {
                $_SESSION["user_id"] = $id;
                $_SESSION["nama"] = $nama;
                $_SESSION["email"] = $email_db;
                $_SESSION["role"] = $role;

                arahkan_dashboard();
            } else {
                $pesan_error = "Email atau password salah.";
            }
        } else {
            $pesan_error = "Email atau password salah.";
        }

        $query->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - SIMORA</title>
</head>
<body>
    <h1>Login SIMORA</h1>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <p>
            <label>Email</label><br>
            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
        </p>

        <p>
            <label>Password</label><br>
            <input type="password" name="password" required>
        </p>

        <button type="submit">Login</button>
    </form>

    <p>
        Belum punya akun?
        <a href="register.php">Daftar sebagai mahasiswa</a>
    </p>
</body>
</html>