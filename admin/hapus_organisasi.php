<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: organisasi.php");
    exit;
}

if (!isset($_POST["id"]) || !ctype_digit($_POST["id"])) {
    exit("ID organisasi tidak valid.");
}

$id = (int) $_POST["id"];

$query_cek = $koneksi->prepare(
    "SELECT COUNT(*) AS total
     FROM pendaftaran
     WHERE organisasi_id = ?"
);

$query_cek->bind_param("i", $id);
$query_cek->execute();

$total_pendaftaran = $query_cek->get_result()->fetch_assoc()["total"];

if ($total_pendaftaran > 0) {
    header("Location: organisasi.php?gagal=1");
    exit;
}

$query_hapus = $koneksi->prepare(
    "DELETE FROM organisasi WHERE id = ?"
);

$query_hapus->bind_param("i", $id);
$query_hapus->execute();

header("Location: organisasi.php?sukses=1");
exit;