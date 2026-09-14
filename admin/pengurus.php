<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$query = $koneksi->prepare(
    "SELECT
        pg.id,
        u.nama,
        u.email,
        o.nama AS nama_organisasi,
        pg.jabatan
    FROM pengurus pg
    JOIN users u ON pg.user_id = u.id
    JOIN organisasi o ON pg.organisasi_id = o.id
    ORDER BY o.nama ASC, u.nama ASC"
);

$query->execute();
$data_pengurus = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Kelola Pengurus - SIMORA</title>
</head>
<body>
    <h1>Kelola Pengurus Organisasi</h1>

    <p>
        <a href="dashboard.php">Dashboard</a> |
        <a href="tambah_pengurus.php">+ Tambah Pengurus</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Data pengurus berhasil diperbarui.</p>
    <?php endif; ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Organisasi</th>
            <th>Jabatan</th>
            <th>Aksi</th>
        </tr>

        <?php if ($data_pengurus->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($pengurus = $data_pengurus->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($pengurus["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($pengurus["email"]); ?></td>
                    <td><?php echo htmlspecialchars($pengurus["nama_organisasi"]); ?></td>
                    <td><?php echo htmlspecialchars($pengurus["jabatan"]); ?></td>
                   <td>
                    <a href="edit_pengurus.php?id=<?php echo $pengurus["id"]; ?>">
                        Edit
                     </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="6">Belum ada data pengurus.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>

<?php
$query->close();
?>