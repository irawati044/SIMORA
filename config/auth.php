<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define("BASE_URL", "/simora");

function wajib_login()
{
    if (!isset($_SESSION["user_id"])) {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }
}

function wajib_role($role)
{
    wajib_login();

    if ($_SESSION["role"] !== $role) {
        http_response_code(403);
        exit("Akses ditolak. Anda tidak memiliki izin membuka halaman ini.");
    }
}

function arahkan_dashboard()
{
    if ($_SESSION["role"] === "mahasiswa") {
        header("Location: " . BASE_URL . "/mahasiswa/dashboard.php");
    } elseif ($_SESSION["role"] === "pengurus") {
        header("Location: " . BASE_URL . "/pengurus/dashboard.php");
    } elseif ($_SESSION["role"] === "admin") {
        header("Location: " . BASE_URL . "/admin/dashboard.php");
    }

    exit;
}