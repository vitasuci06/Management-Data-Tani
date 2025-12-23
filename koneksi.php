<?php
$host = "localhost";      // server
$user = "root";           // username MySQL
$pass = "";               // password MySQL (kosong jika pakai XAMPP)
$db   = "tani";           // nama database kamu

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>
