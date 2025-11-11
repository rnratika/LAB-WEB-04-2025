<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'db_manajemen_proyek');

$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

function check_login() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: login.php");
        exit;
    }
}

function check_role($role) {
    if ($_SESSION["role"] !== $role) {
        echo "<div class='container card'><h2>Akses Ditolak</h2><p>Anda tidak memiliki izin untuk mengakses halaman ini.</p><a href='index.php'>Kembali ke Dashboard</a></div>";
        include 'footer.php';
        exit;
    }
}

function format_tanggal($date) {
    return date("d M Y", strtotime($date));
}
?>