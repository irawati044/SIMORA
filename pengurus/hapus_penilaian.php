<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: penilaian.php");
    exit;
}

if (!isset($_POST["penilaian_id"]) || !ctype_digit($_POST["penilaian_id"])) {
    exit("ID penilaian tidak valid.");
}

$penilaian_id = (int) $_POST["penilaian_id"];
$user_id = $_SESSION["user_id"];

$query_data = $koneksi->prepare(
    "SELECT w.id AS wawancara_id
     FROM penilaian pn
     JOIN wawancara w ON pn.wawancara_id = w.id
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
    exit("Penilaian tidak ditemukan atau tidak dapat dihapus.");
}

$penilaian = $data->fetch_assoc();
$wawancara_id = $penilaian["wawancara_id"];

try {
    $koneksi->begin_transaction();

    $hapus_penilaian = $koneksi->prepare(
        "DELETE FROM penilaian WHERE id = ?"
    );

    $hapus_penilaian->bind_param("i", $penilaian_id);
    $hapus_penilaian->execute();

    $status_wawancara = "dijadwalkan";

    $update_wawancara = $koneksi->prepare(
        "UPDATE wawancara
         SET status = ?
         WHERE id = ?"
    );

    $update_wawancara->bind_param(
        "si",
        $status_wawancara,
        $wawancara_id
    );

    $update_wawancara->execute();

    $koneksi->commit();

    header("Location: penilaian.php?sukses=1");
    exit;
} catch (Exception $e) {
    $koneksi->rollback();
    exit("Penilaian gagal dihapus.");
}