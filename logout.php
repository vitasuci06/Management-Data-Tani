<?php
session_start();

// Hapus semua session
$_SESSION = [];
session_unset();
session_destroy();

// Redirect ke index.php PUBLIK
header("Location: index.php");
exit;
