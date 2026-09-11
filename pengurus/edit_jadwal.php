<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $wawancara_id = (int) $_POST["wawancara_id"];
    $jadwal_input = $_POST["jadwal"];

    $waktu = DateTime::createFromFormat("Y-m-d\TH:i", $jadwal_input);

    if (!$waktu || $waktu->format("Y-m-d\TH:i") !== $jadwal_input) {
        exit("Format jadwal tidak valid.");
    }

    if ($waktu <= new DateTime()) {
        exit("Jadwal wawancara harus lebih dari waktu saat ini.");
    }

    $jadwal_database = $waktu->format("Y-m-d H:i:s");

    $query_update = $koneksi->prepare(
        "UPDATE wawancara w
         JOIN pendaftaran p ON w.pendaftaran_id = p.id
         JOIN pengurus pg ON pg.organisasi_id = p.organisasi_id
         SET w.jadwal = ?
         WHERE w.id = ?
           AND pg.user_id = ?
           AND w.status = 'dijadwalkan'"
    );

    $query_update->bind_param("sii", $jadwal_database, $wawancara_id, $user_id);
    $query_update->execute();

    header("Location: wawancara.php?sukses=1");
    exit;
}

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) {
    header("Location: wawancara.php");
    exit;
}

$wawancara_id = (int) $_GET["id"];

$query = $koneksi->prepare(
    "SELECT
        w.id,
        w.jadwal,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi
    FROM wawancara w
    JOIN pendaftaran p ON w.pendaftaran_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON pg.organisasi_id = o.id
    WHERE w.id = ?
      AND pg.user_id = ?
      AND w.status = 'dijadwalkan'
    LIMIT 1"
);

$query->bind_param("ii", $wawancara_id, $user_id);
$query->execute();
$data = $query->get_result();

if ($data->num_rows === 0) {
    exit("Jadwal tidak ditemukan atau Anda tidak memiliki akses.");
}

$wawancara = $data->fetch_assoc();
$jadwal_form = date("Y-m-d\TH:i", strtotime($wawancara["jadwal"]));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Jadwal Wawancara - SIMORA</title>
</head>
<body>
    <h1>Edit Jadwal Wawancara</h1>

    <p><a href="wawancara.php">Kembali ke Jadwal Wawancara</a></p>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($wawancara["nama"]); ?></li>
        <li>NIM: <?php echo htmlspecialchars($wawancara["nim"]); ?></li>
        <li>Organisasi: <?php echo htmlspecialchars($wawancara["nama_organisasi"]); ?></li>
    </ul>

    <form method="POST" action="">
        <input type="hidden" name="wawancara_id" value="<?php echo $wawancara["id"]; ?>">

        <p>
            <label>Jadwal Wawancara Baru</label><br>
            <input
                type="datetime-local"
                name="jadwal"
                value="<?php echo $jadwal_form; ?>"
                min="<?php echo date("Y-m-d\TH:i"); ?>"
                required
            >
        </p>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>