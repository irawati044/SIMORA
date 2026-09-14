<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$query = $koneksi->prepare(
    "SELECT id, nama, email, role
     FROM users
     ORDER BY role ASC, nama ASC"
);

$query->execute();
$data_users = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Kelola Pengguna - SIMORA</title>
</head>
<body>
    <h1>Kelola Pengguna</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Data pengguna berhasil diperbarui.</p>
    <?php endif; ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
        </tr>

        <?php $nomor = 1; ?>

        <?php while ($user = $data_users->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $nomor++; ?></td>
                <td><?php echo htmlspecialchars($user["nama"]); ?></td>
                <td><?php echo htmlspecialchars($user["email"]); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($user["role"])); ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $user["id"]; ?>">
                        Edit
                    </a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>

<?php
$query->close();
?>