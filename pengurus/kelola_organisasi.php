<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];
$pesan_error = "";

$query_organisasi = $koneksi->prepare(
    "SELECT
        o.id,
        o.nama,
        o.kategori,
        o.deskripsi,
        o.kegiatan,
        o.persyaratan,
        o.periode_pendaftaran,
        o.status
    FROM pengurus pg
    JOIN organisasi o ON pg.organisasi_id = o.id
    WHERE pg.user_id = ?
    ORDER BY pg.id ASC
    LIMIT 1"
);

$query_organisasi->bind_param("i", $user_id);
$query_organisasi->execute();
$data_organisasi = $query_organisasi->get_result();

if ($data_organisasi->num_rows === 0) {
    exit("Akun pengurus belum terhubung ke organisasi.");
}

$organisasi = $data_organisasi->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $organisasi_id = (int) $_POST["organisasi_id"];
    $deskripsi = trim($_POST["deskripsi"]);
    $kegiatan = trim($_POST["kegiatan"]);
    $persyaratan = trim($_POST["persyaratan"]);
    $periode_pendaftaran = trim($_POST["periode_pendaftaran"]);
    $status = $_POST["status"];

    if (
        empty($deskripsi) ||
        empty($kegiatan) ||
        empty($persyaratan) ||
        empty($periode_pendaftaran)
    ) {
        $pesan_error = "Semua data wajib diisi.";
    } elseif (!in_array($status, ["buka", "tutup"])) {
        $pesan_error = "Status pendaftaran tidak valid.";
    } else {
        $query_update = $koneksi->prepare(
            "UPDATE organisasi o
             JOIN pengurus pg ON pg.organisasi_id = o.id
             SET
                o.deskripsi = ?,
                o.kegiatan = ?,
                o.persyaratan = ?,
                o.periode_pendaftaran = ?,
                o.status = ?
             WHERE o.id = ?
               AND pg.user_id = ?"
        );

        $query_update->bind_param(
            "sssssii",
            $deskripsi,
            $kegiatan,
            $persyaratan,
            $periode_pendaftaran,
            $status,
            $organisasi_id,
            $user_id
        );

        if ($query_update->execute()) {
            header("Location: kelola_organisasi.php?sukses=1");
            exit;
        }

        $pesan_error = "Informasi organisasi gagal diperbarui.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Kelola Organisasi Saya - SIMORA</title>
</head>
<body>
    <h1>Kelola Organisasi Saya</h1>

    <p>
        <a href="dashboard.php">Kembali ke Dashboard</a> |
        <a href="../logout.php">Logout</a>
    </p>

    <?php if (isset($_GET["sukses"])) : ?>
        <p style="color: green;">Informasi organisasi berhasil diperbarui.</p>
    <?php endif; ?>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <p><strong>Nama Organisasi:</strong> <?php echo htmlspecialchars($organisasi["nama"]); ?></p>
    <p><strong>Kategori:</strong> <?php echo htmlspecialchars($organisasi["kategori"]); ?></p>

    <form method="POST" action="">
        <input type="hidden" name="organisasi_id" value="<?php echo $organisasi["id"]; ?>">

        <p>
            <label>Deskripsi Organisasi</label><br>
            <textarea name="deskripsi" rows="5" cols="50" required><?php echo htmlspecialchars($organisasi["deskripsi"]); ?></textarea>
        </p>

        <p>
            <label>Kegiatan Organisasi</label><br>
            <textarea name="kegiatan" rows="5" cols="50" required><?php echo htmlspecialchars($organisasi["kegiatan"]); ?></textarea>
        </p>

        <p>
            <label>Persyaratan Berkas</label><br>
            <textarea name="persyaratan" rows="5" cols="50" required><?php echo htmlspecialchars($organisasi["persyaratan"]); ?></textarea>
        </p>

        <p>
            <label>Periode Pendaftaran</label><br>
            <input
                type="text"
                name="periode_pendaftaran"
                value="<?php echo htmlspecialchars($organisasi["periode_pendaftaran"]); ?>"
                required
            >
        </p>

        <p>
            <label>Status Pendaftaran</label><br>

            <select name="status" required>
                <option value="buka" <?php echo $organisasi["status"] === "buka" ? "selected" : ""; ?>>
                    Buka
                </option>

                <option value="tutup" <?php echo $organisasi["status"] === "tutup" ? "selected" : ""; ?>>
                    Tutup
                </option>
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>