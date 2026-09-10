<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("pengurus");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pendaftar.php");
    exit;
}

if (!isset($_POST["pendaftaran_id"]) || !ctype_digit($_POST["pendaftaran_id"])) {
    exit("ID pendaftaran tidak valid.");
}

$pendaftaran_id = (int) $_POST["pendaftaran_id"];
$status = $_POST["status"];
$user_id = $_SESSION["user_id"];

$status_diizinkan = ["lolos_berkas", "ditolak_berkas"];

if (!in_array($status, $status_diizinkan)) {
    exit("Status seleksi tidak valid.");
}

$query = $koneksi->prepare(
    "UPDATE pendaftaran p
     JOIN pengurus pg ON pg.organisasi_id = p.organisasi_id
     SET p.status = ?
     WHERE p.id = ?
       AND pg.user_id = ?
       AND p.status = 'menunggu'"
);

$query->bind_param("sii", $status, $pendaftaran_id, $user_id);
$query->execute();

if ($query->affected_rows === 1) {
    header("Location: pendaftar.php?sukses=1");
    exit;
}

exit("Status tidak dapat diperbarui. Data mungkin sudah diseleksi atau akses Anda tidak sesuai.");