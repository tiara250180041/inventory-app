<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /inventory-app/public/login.php");
        exit;
    }
}

function check_role($allowed_roles) {
    check_login();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        die("Akses ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.");
    }
}
?>