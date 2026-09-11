<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: wawancara.php");
    exit;
}

if (!isset($_POST["wawancara_id"]) || !ctype_digit($_POST["wawancara_id"])) {
    exit("ID wawancara tidak valid.");
}

$wawancara_id = (int) $_POST["wawancara_id"];
$user_id = $_SESSION["user_id"];

$query_data = $koneksi->prepare(
    "SELECT p.id AS pendaftaran_id
     FROM wawancara w
     JOIN pendaftaran p ON w.pendaftaran_id = p.id
     JOIN pengurus pg ON pg.organisasi_id = p.organisasi_id
     WHERE w.id = ?
       AND pg.user_id = ?
       AND w.status = 'dijadwalkan'
     LIMIT 1"
);

$query_data->bind_param("ii", $wawancara_id, $user_id);
$query_data->execute();
$data = $query_data->get_result();

if ($data->num_rows === 0) {
    exit("Jadwal tidak ditemukan atau tidak dapat dihapus.");
}

$wawancara = $data->fetch_assoc();
$pendaftaran_id = $wawancara["pendaftaran_id"];

try {
    $koneksi->begin_transaction();

    $hapus_jadwal = $koneksi->prepare(
        "DELETE FROM wawancara WHERE id = ?"
    );

    $hapus_jadwal->bind_param("i", $wawancara_id);
    $hapus_jadwal->execute();

    $status_pendaftaran = "lolos_berkas";

    $update_pendaftaran = $koneksi->prepare(
        "UPDATE pendaftaran
         SET status = ?
         WHERE id = ?"
    );

    $update_pendaftaran->bind_param(
        "si",
        $status_pendaftaran,
        $pendaftaran_id
    );

    $update_pendaftaran->execute();

    $koneksi->commit();

    header("Location: wawancara.php?sukses=1");
    exit;
} catch (Exception $e) {
    $koneksi->rollback();
    exit("Jadwal wawancara gagal dihapus.");
}