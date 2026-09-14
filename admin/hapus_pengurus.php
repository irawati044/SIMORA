<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("admin");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: pengurus.php");
    exit;
}

if (!isset($_POST["id"]) || !ctype_digit($_POST["id"])) {
    exit("ID pengurus tidak valid.");
}

$pengurus_id = (int) $_POST["id"];

$query_data = $koneksi->prepare(
    "SELECT user_id
     FROM pengurus
     WHERE id = ?"
);

$query_data->bind_param("i", $pengurus_id);
$query_data->execute();
$data = $query_data->get_result();

if ($data->num_rows === 0) {
    exit("Data pengurus tidak ditemukan.");
}

$pengurus = $data->fetch_assoc();
$user_id = $pengurus["user_id"];

try {
    $koneksi->begin_transaction();

    $hapus_pengurus = $koneksi->prepare(
        "DELETE FROM pengurus WHERE id = ?"
    );
    $hapus_pengurus->bind_param("i", $pengurus_id);
    $hapus_pengurus->execute();

    $hapus_user = $koneksi->prepare(
        "DELETE FROM users WHERE id = ?"
    );
    $hapus_user->bind_param("i", $user_id);
    $hapus_user->execute();

    $koneksi->commit();

    header("Location: pengurus.php?sukses=1");
    exit;
} catch (Exception $e) {
    $koneksi->rollback();
    exit("Pengurus tidak dapat dihapus. Akun mungkin sudah dipakai pada jadwal wawancara.");
}