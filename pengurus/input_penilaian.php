<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];
$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $wawancara_id = (int) $_POST["wawancara_id"];
} elseif (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $wawancara_id = (int) $_GET["id"];
} else {
    header("Location: penilaian.php");
    exit;
}

$query_wawancara = $koneksi->prepare(
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
    JOIN pengurus pg ON w.pengurus_id = pg.id
    LEFT JOIN penilaian pn ON pn.wawancara_id = w.id
    WHERE w.id = ?
      AND pg.user_id = ?
      AND w.status = 'dijadwalkan'
      AND pn.id IS NULL
    LIMIT 1"
);

$query_wawancara->bind_param("ii", $wawancara_id, $user_id);
$query_wawancara->execute();
$data_wawancara = $query_wawancara->get_result();

if ($data_wawancara->num_rows === 0) {
    exit("Wawancara tidak ditemukan atau sudah dinilai.");
}

$wawancara = $data_wawancara->fetch_assoc();

$nilai = "";
$keputusan = "";
$catatan = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nilai = trim($_POST["nilai"]);
    $keputusan = $_POST["keputusan"];
    $catatan = trim($_POST["catatan"]);

    if ($nilai === "" || $keputusan === "" || $catatan === "") {
        $pesan_error = "Nilai, keputusan, dan catatan wajib diisi.";
    } elseif (filter_var($nilai, FILTER_VALIDATE_INT) === false || $nilai < 1 || $nilai > 100) {
        $pesan_error = "Nilai harus berupa bilangan bulat dari 1 sampai 100.";
    } elseif (!in_array($keputusan, ["lolos", "tidak_lolos"])) {
        $pesan_error = "Keputusan wawancara tidak valid.";
    } else {
        $nilai = (int) $nilai;

        try {
            $koneksi->begin_transaction();

            $simpan_penilaian = $koneksi->prepare(
                "INSERT INTO penilaian (wawancara_id, nilai, keputusan, catatan)
                 VALUES (?, ?, ?, ?)"
            );

            $simpan_penilaian->bind_param(
                "idss",
                $wawancara_id,
                $nilai,
                $keputusan,
                $catatan
            );

            $simpan_penilaian->execute();

            $status_selesai = "selesai";

            $update_wawancara = $koneksi->prepare(
                "UPDATE wawancara
                 SET status = ?
                 WHERE id = ? AND status = 'dijadwalkan'"
            );

            $update_wawancara->bind_param(
                "si",
                $status_selesai,
                $wawancara_id
            );

            $update_wawancara->execute();

            $koneksi->commit();

            header("Location: penilaian.php?sukses=1");
            exit;
        } catch (Exception $e) {
            $koneksi->rollback();
            $pesan_error = "Penilaian gagal disimpan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Penilaian - SIMORA</title>
</head>
<body>
    <h1>Input Penilaian Wawancara</h1>

    <p><a href="penilaian.php">Kembali ke Penilaian Wawancara</a></p>

    <h2>Data Mahasiswa</h2>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($wawancara["nama"]); ?></li>
        <li>NIM: <?php echo htmlspecialchars($wawancara["nim"]); ?></li>
        <li>Organisasi: <?php echo htmlspecialchars($wawancara["nama_organisasi"]); ?></li>
        <li>Jadwal: <?php echo htmlspecialchars($wawancara["jadwal"]); ?></li>
    </ul>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="wawancara_id" value="<?php echo $wawancara["id"]; ?>">

        <p>
            <label>Nilai Wawancara (1 - 100)</label><br>
            <input
                type="number"
                name="nilai"
                min="1"
                max="100"
                step="1"
                value="<?php echo htmlspecialchars($nilai); ?>"
                required
            >
        </p>

        <p>
            <label>Keputusan Wawancara</label><br>
            <select name="keputusan" required>
                <option value="">Pilih keputusan</option>
                <option value="lolos" <?php echo $keputusan === "lolos" ? "selected" : ""; ?>>
                    Lolos
                </option>
                <option value="tidak_lolos" <?php echo $keputusan === "tidak_lolos" ? "selected" : ""; ?>>
                    Tidak Lolos
                </option>
            </select>
        </p>

        <p>
            <label>Catatan Pengurus</label><br>
            <textarea name="catatan" rows="5" cols="40" required><?php echo htmlspecialchars($catatan); ?></textarea>
        </p>

        <button type="submit">Simpan Penilaian</button>
    </form>
</body>
</html>