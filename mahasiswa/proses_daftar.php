<?php
require_once "../config/koneksi.php";
require_once "../config/auth.php";

wajib_role("mahasiswa");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: organisasi.php");
    exit;
}

if (!isset($_POST["organisasi_id"]) || !ctype_digit($_POST["organisasi_id"])) {
    exit("ID organisasi tidak valid.");
}

$id_organisasi = (int) $_POST["organisasi_id"];
$user_id = $_SESSION["user_id"];
$status_buka = "buka";

$query_mahasiswa = $koneksi->prepare(
    "SELECT id FROM mahasiswa WHERE user_id = ?"
);

$query_mahasiswa->bind_param("i", $user_id);
$query_mahasiswa->execute();
$data_mahasiswa = $query_mahasiswa->get_result();

if ($data_mahasiswa->num_rows === 0) {
    exit("Data mahasiswa tidak ditemukan.");
}

$mahasiswa = $data_mahasiswa->fetch_assoc();
$mahasiswa_id = $mahasiswa["id"];

$query_organisasi = $koneksi->prepare(
    "SELECT id FROM organisasi WHERE id = ? AND status = ?"
);

$query_organisasi->bind_param("is", $id_organisasi, $status_buka);
$query_organisasi->execute();
$query_organisasi->store_result();

if ($query_organisasi->num_rows === 0) {
    exit("Pendaftaran organisasi sedang ditutup.");
}

$query_cek = $koneksi->prepare(
    "SELECT id FROM pendaftaran WHERE mahasiswa_id = ? AND organisasi_id = ?"
);

$query_cek->bind_param("ii", $mahasiswa_id, $id_organisasi);
$query_cek->execute();
$query_cek->store_result();

if ($query_cek->num_rows > 0) {
    exit("Anda sudah pernah mendaftar pada organisasi ini.");
}

$jenis_berkas = [
    "ktm" => "Kartu Tanda Mahasiswa",
    "cv" => "Curriculum Vitae",
    "pas_foto" => "Pas Foto",
    "berkas_tambahan" => "Berkas Tambahan"
];

$berkas_wajib = ["ktm", "cv", "pas_foto"];
$format_diizinkan = ["pdf", "jpg", "jpeg", "png"];
$ukuran_maksimal = 5 * 1024 * 1024;

$berkas_valid = [];
$pesan_error = "";

foreach ($jenis_berkas as $nama_input => $jenis) {
    $file = $_FILES[$nama_input];

    if ($file["error"] === UPLOAD_ERR_NO_FILE) {
        if (in_array($nama_input, $berkas_wajib)) {
            $pesan_error = "Berkas " . $jenis . " wajib diunggah.";
            break;
        }

        continue;
    }

    if ($file["error"] !== UPLOAD_ERR_OK) {
        $pesan_error = "Upload berkas " . $jenis . " gagal.";
        break;
    }

    if ($file["size"] > $ukuran_maksimal) {
        $pesan_error = "Ukuran berkas " . $jenis . " melebihi 5 MB.";
        break;
    }

    $ekstensi = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($ekstensi, $format_diizinkan)) {
        $pesan_error = "Format berkas " . $jenis . " tidak diizinkan.";
        break;
    }

    if ($nama_input === "pas_foto" && !in_array($ekstensi, ["jpg", "jpeg", "png"])) {
        $pesan_error = "Pas foto harus menggunakan format JPG, JPEG, atau PNG.";
        break;
    }

    $berkas_valid[] = [
        "jenis" => $jenis,
        "file" => $file,
        "ekstensi" => $ekstensi
    ];
}

if (!empty($pesan_error)) {
    exit($pesan_error . " <br><br><a href='daftar.php?id=" . $id_organisasi . "'>Kembali ke Form</a>");
}

$folder_upload = "../assets/uploads/berkas/";

if (!is_dir($folder_upload)) {
    mkdir($folder_upload, 0755, true);
}

$nama_file_tersimpan = [];

try {
    $koneksi->begin_transaction();

    $status_pendaftaran = "menunggu";

    $simpan_pendaftaran = $koneksi->prepare(
        "INSERT INTO pendaftaran (mahasiswa_id, organisasi_id, status)
         VALUES (?, ?, ?)"
    );

    $simpan_pendaftaran->bind_param(
        "iis",
        $mahasiswa_id,
        $id_organisasi,
        $status_pendaftaran
    );

    $simpan_pendaftaran->execute();

    $pendaftaran_id = $koneksi->insert_id;

    foreach ($berkas_valid as $berkas) {
        $nama_baru = uniqid("berkas_", true) . "." . $berkas["ekstensi"];
        $tujuan_file = $folder_upload . $nama_baru;

        if (!move_uploaded_file($berkas["file"]["tmp_name"], $tujuan_file)) {
            throw new Exception("Berkas gagal disimpan.");
        }

        $nama_file_tersimpan[] = $tujuan_file;

        $simpan_berkas = $koneksi->prepare(
            "INSERT INTO berkas (pendaftaran_id, nama_file, jenis)
             VALUES (?, ?, ?)"
        );

        $simpan_berkas->bind_param(
            "iss",
            $pendaftaran_id,
            $nama_baru,
            $berkas["jenis"]
        );

        $simpan_berkas->execute();
        $simpan_berkas->close();
    }

    $koneksi->commit();

    header("Location: status_pendaftaran.php?sukses=1");
    exit;
} catch (Exception $e) {
    $koneksi->rollback();

    foreach ($nama_file_tersimpan as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    exit("Pendaftaran gagal dikirim. Silakan coba lagi.");
}