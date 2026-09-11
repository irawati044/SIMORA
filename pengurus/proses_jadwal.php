<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: wawancara.php");
    exit;
}

if (!isset($_POST["pendaftaran_id"]) || !ctype_digit($_POST["pendaftaran_id"])) {
    exit("ID pendaftaran tidak valid.");
}

if (empty($_POST["jadwal"])) {
    exit("Jadwal wawancara wajib diisi.");
}

$pendaftaran_id = (int) $_POST["pendaftaran_id"];
$jadwal_input = $_POST["jadwal"];
$user_id = $_SESSION["user_id"];

$waktu = DateTime::createFromFormat("Y-m-d\TH:i", $jadwal_input);

if (!$waktu || $waktu->format("Y-m-d\TH:i") !== $jadwal_input) {
    exit("Format jadwal tidak valid.");
}

if ($waktu <= new DateTime()) {
    exit("Jadwal wawancara harus lebih dari waktu saat ini.");
}

$jadwal_database = $waktu->format("Y-m-d H:i:s");

$query_pengurus = $koneksi->prepare(
    "SELECT pg.id
     FROM pengurus pg
     JOIN pendaftaran p ON pg.organisasi_id = p.organisasi_id
     WHERE pg.user_id = ? AND p.id = ? AND p.status = 'lolos_berkas'
     LIMIT 1"
);

$query_pengurus->bind_param("ii", $user_id, $pendaftaran_id);
$query_pengurus->execute();
$data_pengurus = $query_pengurus->get_result();

if ($data_pengurus->num_rows === 0) {
    exit("Data pendaftaran tidak dapat dijadwalkan.");
}

$pengurus = $data_pengurus->fetch_assoc();
$pengurus_id = $pengurus["id"];

$query_cek = $koneksi->prepare(
    "SELECT id FROM wawancara WHERE pendaftaran_id = ?"
);

$query_cek->bind_param("i", $pendaftaran_id);
$query_cek->execute();
$query_cek->store_result();

if ($query_cek->num_rows > 0) {
    exit("Wawancara untuk pendaftar ini sudah dijadwalkan.");
}

try {
    $koneksi->begin_transaction();

    $status_wawancara = "dijadwalkan";

    $simpan_wawancara = $koneksi->prepare(
        "INSERT INTO wawancara (pendaftaran_id, pengurus_id, jadwal, status)
         VALUES (?, ?, ?, ?)"
    );

    $simpan_wawancara->bind_param(
        "iiss",
        $pendaftaran_id,
        $pengurus_id,
        $jadwal_database,
        $status_wawancara
    );

    $simpan_wawancara->execute();

    $status_pendaftaran = "jadwal_wawancara";

    $update_pendaftaran = $koneksi->prepare(
        "UPDATE pendaftaran
         SET status = ?
         WHERE id = ? AND status = 'lolos_berkas'"
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
    exit("Jadwal wawancara gagal disimpan.");
}