<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: hasil_akhir.php");
    exit;
}

if (!isset($_POST["pendaftaran_id"]) || !ctype_digit($_POST["pendaftaran_id"])) {
    exit("ID pendaftaran tidak valid.");
}

$pendaftaran_id = (int) $_POST["pendaftaran_id"];
$status = $_POST["status"];
$user_id = $_SESSION["user_id"];

$status_diizinkan = ["diterima", "ditolak"];

if (!in_array($status, $status_diizinkan)) {
    exit("Hasil akhir tidak valid.");
}

$query = $koneksi->prepare(
    "UPDATE pendaftaran p
     JOIN wawancara w ON w.pendaftaran_id = p.id
     JOIN penilaian pn ON pn.wawancara_id = w.id
     JOIN pengurus pg ON w.pengurus_id = pg.id
     SET p.status = ?
     WHERE p.id = ?
       AND pg.user_id = ?
       AND p.status = 'jadwal_wawancara'
       AND w.status = 'selesai'"
);

$query->bind_param("sii", $status, $pendaftaran_id, $user_id);
$query->execute();

if ($query->affected_rows === 1) {
    header("Location: hasil_akhir.php?sukses=1");
    exit;
}

exit("Hasil akhir tidak dapat ditetapkan.");