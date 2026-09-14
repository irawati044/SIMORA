<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

$user_id = $_SESSION["user_id"];
$pesan_error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $penilaian_id = (int) $_POST["penilaian_id"];
} elseif (isset($_GET["id"]) && ctype_digit($_GET["id"])) {
    $penilaian_id = (int) $_GET["id"];
} else {
    header("Location: penilaian.php");
    exit;
}

$query_data = $koneksi->prepare(
    "SELECT
        pn.id,
        pn.nilai,
        pn.keputusan,
        pn.catatan,
        u.nama,
        m.nim,
        o.nama AS nama_organisasi
    FROM penilaian pn
    JOIN wawancara w ON pn.wawancara_id = w.id
    JOIN pendaftaran p ON w.pendaftaran_id = p.id
    JOIN mahasiswa m ON p.mahasiswa_id = m.id
    JOIN users u ON m.user_id = u.id
    JOIN organisasi o ON p.organisasi_id = o.id
    JOIN pengurus pg ON w.pengurus_id = pg.id
    WHERE pn.id = ?
      AND pg.user_id = ?
      AND w.status = 'selesai'
    LIMIT 1"
);

$query_data->bind_param("ii", $penilaian_id, $user_id);
$query_data->execute();
$data = $query_data->get_result();

if ($data->num_rows === 0) {
    exit("Penilaian tidak ditemukan atau tidak dapat diedit.");
}

$penilaian_lama = $data->fetch_assoc();

$nilai = $penilaian_lama["nilai"];
$keputusan = $penilaian_lama["keputusan"];
$catatan = $penilaian_lama["catatan"];

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

        $query_update = $koneksi->prepare(
            "UPDATE penilaian pn
             JOIN wawancara w ON pn.wawancara_id = w.id
             JOIN pengurus pg ON w.pengurus_id = pg.id
             SET pn.nilai = ?, pn.keputusan = ?, pn.catatan = ?
             WHERE pn.id = ?
               AND pg.user_id = ?
               AND w.status = 'selesai'"
        );

        $query_update->bind_param(
            "dssii",
            $nilai,
            $keputusan,
            $catatan,
            $penilaian_id,
            $user_id
        );

        $query_update->execute();

        header("Location: penilaian.php?sukses=1");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <link rel="stylesheet" href="/simora/assets/css/style.css">
    <title>Edit Penilaian - SIMORA</title>
</head>
<body>
    <h1>Edit Penilaian Wawancara</h1>

    <p><a href="penilaian.php">Kembali ke Penilaian Wawancara</a></p>

    <ul>
        <li>Nama: <?php echo htmlspecialchars($penilaian_lama["nama"]); ?></li>
        <li>NIM: <?php echo htmlspecialchars($penilaian_lama["nim"]); ?></li>
        <li>Organisasi: <?php echo htmlspecialchars($penilaian_lama["nama_organisasi"]); ?></li>
    </ul>

    <?php if (!empty($pesan_error)) : ?>
        <p style="color: red;"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="penilaian_id" value="<?php echo $penilaian_id; ?>">

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

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>