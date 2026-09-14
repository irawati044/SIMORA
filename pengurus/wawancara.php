<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];
$status_lolos = "lolos_berkas";

$query_lolos = $koneksi->prepare(
    "SELECT DISTINCT
        p.id,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi
    FROM pendaftaran p
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON pg.organisasi_id = o.id
    WHERE pg.user_id = ? AND p.status = ?
    ORDER BY u.nama ASC"
);

$query_lolos->bind_param("is", $user_id, $status_lolos);
$query_lolos->execute();
$data_lolos = $query_lolos->get_result();

$query_jadwal = $koneksi->prepare(
    "SELECT DISTINCT
        w.id,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi,
        w.jadwal,
        w.status
    FROM wawancara w
    JOIN pendaftaran p ON w.pendaftaran_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON pg.organisasi_id = o.id
    WHERE pg.user_id = ?
    ORDER BY w.jadwal ASC"
);

$query_jadwal->bind_param("i", $user_id);
$query_jadwal->execute();
$data_jadwal = $query_jadwal->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Jadwal Wawancara - SIMORA</title>
</head>
<body>
    <h1>Jadwal Wawancara</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Jadwal wawancara berhasil dibuat.</p>
    <?php endif; ?>

    <h2>Mahasiswa Lolos Seleksi Berkas</h2>

    <?php if ($data_lolos->num_rows > 0) : ?>
        <table border="1" cellpadding="8">
            <tr>
                <th>No.</th>
                <th>Nama</th>
                <th>NIM</th>
                <th>Organisasi</th>
                <th>Jadwal Wawancara</th>
                <th>Aksi</th>
            </tr>

            <?php $nomor = 1; ?>

            <?php while ($mahasiswa = $data_lolos->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $nomor++; ?></td>
                    <td><?php echo htmlspecialchars($mahasiswa["nama"]); ?></td>
                    <td><?php echo htmlspecialchars($mahasiswa["nim"]); ?></td>
                    <td><?php echo htmlspecialchars($mahasiswa["nama_organisasi"]); ?></td>
                    <td>
                        <form method="POST" action="proses_jadwal.php">
                            <input type="hidden" name="pendaftaran_id" value="<?php echo $mahasiswa["id"]; ?>">

                            <input
                                type="datetime-local"
                                name="jadwal"
                                min="<?php echo date("Y-m-d\TH:i"); ?>"
                                required
                            >
                    </td>
                    <td>
                            <button type="submit">Simpan Jadwal</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else : ?>
        <p>Belum ada mahasiswa yang lolos seleksi berkas.</p>
    <?php endif; ?>

    <hr>

 <h2>Jadwal Wawancara yang Sudah Dibuat</h2>

<table border="1" cellpadding="8">
    <tr>
        <th>No.</th>
        <th>Nama</th>
        <th>NIM</th>
        <th>Organisasi</th>
        <th>Jadwal</th>
        <th>Status</th>
        <th>Aksi</th>
    </tr>

    <?php if ($data_jadwal->num_rows > 0) : ?>
        <?php $nomor = 1; ?>

        <?php while ($jadwal = $data_jadwal->fetch_assoc()) : ?>
            <tr>
                <td><?php echo $nomor++; ?></td>
                <td><?php echo htmlspecialchars($jadwal["nama"]); ?></td>
                <td><?php echo htmlspecialchars($jadwal["nim"]); ?></td>
                <td><?php echo htmlspecialchars($jadwal["nama_organisasi"]); ?></td>
                <td><?php echo htmlspecialchars($jadwal["jadwal"]); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($jadwal["status"])); ?></td>
                <td>
                    <?php if ($jadwal["status"] === "dijadwalkan") : ?>
                        <a href="edit_jadwal.php?id=<?php echo $jadwal["id"]; ?>">
                            Edit
                        </a>

                        <form
                            method="POST"
                            action="hapus_jadwal.php"
                            style="display: inline;"
                            onsubmit="return confirm('Yakin ingin menghapus jadwal ini?');"
                        >
                            <input type="hidden" name="wawancara_id" value="<?php echo $jadwal["id"]; ?>">

                            <button type="submit">Hapus</button>
                        </form>
                    <?php else : ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else : ?>
        <tr>
            <td colspan="7">Belum ada jadwal wawancara.</td>
        </tr>
    <?php endif; ?>
</table>