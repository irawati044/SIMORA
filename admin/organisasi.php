<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

$query = $koneksi->prepare(
    "SELECT id, nama, kategori, periode_pendaftaran, status
     FROM organisasi
     ORDER BY nama ASC"
);

$query->execute();
$data_organisasi = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Kelola Organisasi - SIMORA</title>
</head>
<body>
    <h1>Kelola Organisasi</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="tambah_organisasi.php">+ Tambah Organisasi</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Data organisasi berhasil diperbarui.</p>
    <?php endif; ?>

    <?php if (isset($_GET["gagal"])) : ?>
        <p style="color: red;">
            Organisasi tidak dapat dihapus karena sudah memiliki pendaftar.
        </p>
    <?php endif; ?>

    <table border="1" cellpadding="8">
        <tr>
            <th>No.</th>
            <th>Nama Organisasi</th>
            <th>Kategori</th>
            <th>Periode</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

        <?php if ($data_organisasi->num_rows > 0) : ?>
            <?php $nomor = 1; ?>

            <?php while ($organisasi = $data_organisasi->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($organisasi["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($organisasi["kategori"]); ?></td>
                    <td><?php echo htmlspecialchars($organisasi["periode_pendaftaran"]); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($organisasi["status"])); ?></td>
                    <td>
                        <a href="edit_organisasi.php?id=<?php echo $organisasi["id"]; ?>">
                            Edit Nama/Kategori
                        </a>

                        <form
                            method="POST"
                            action="hapus_organisasi.php"
                            style="display: inline;"
                            onsubmit="return confirm('Yakin ingin menghapus organisasi ini?');"
                        >
                            <input type="hidden" name="id" value="<?php echo $organisasi["id"]; ?>">
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else : ?>
            <tr>
                <td colspan="6">Belum ada organisasi.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>