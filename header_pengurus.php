<?php
session_start();
$currentPage = basename($_SERVER['PHP_SELF']);
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelompok Tani Makmur</title>    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
      rel="stylesheet" 
      integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" 
      crossorigin="anonymous">
      <link rel="stylesheet" href="./css/style.css">
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
   
</head>

<body>
    <!-- ! Body -->
    <div class="page-flex">
        <!-- ! Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-start">
                <div class="sidebar-head">
                    <div class="sidebar-user-info">
                        <span class="sidebar-user__title"><?= $_SESSION['nama_pengguna']; ?></span>
                        <span class="sidebar-user__subtitle"><?= $_SESSION['jabatan']; ?></span>
                    </div>
                    <button class="sidebar-toggle transparent-btn" title="Menu" type="button">
                        <span class="sr-only">Toggle menu</span>
                        <span class="icon menu-toggle" aria-hidden="true"></span>
                    </button>
                </div>
                <div class="sidebar-body">
                    <ul class="sidebar-body-menu">
                        <li class="new_menu">
                            <a class="show-cat-btn <?php echo ($currentPage == 'index_pengurus.php') ? 'active' : ''; ?>" href="index_pengurus.php" >
                                <span class="menu-content">
                                    <i data-feather="home"></i>
                                    <span class="menu-text">Dashboard</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="show-cat-btn <?php echo ($currentPage == 'kelola_pengurus.php') ? 'active' : ''; ?>" href="kelola_pengurus.php" >
                                <span class="menu-content">
                                    <i data-feather="user" aria-hidden="true"></i>
                                    <span class="menu-text">Pengurus</span>
                                </span>
                                </a>

                        </li>
                        <li>
                            <a class="show-cat-btn <?php echo ($currentPage == 'kelola_anggota.php') ? 'active' : ''; ?>" href="kelola_anggota.php" >
                                <span class="menu-content">
                                    <i data-feather="users" aria-hidden="true"></i>
                                    <span class="menu-text">Anggota</span>
                                </span>
                            </a>
                        </li>
                        <li>
                             <a class="show-cat-btn <?php echo ($currentPage == 'kelola_pupuk.php') ? 'active' : ''; ?>" href="kelola_pupuk.php" >
                                <span class="menu-content">
                                    <i data-feather="package" aria-hidden="true"></i>
                                    <span class="menu-text">Pupuk Subsidi</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="show-cat-btn <?php echo ($currentPage == 'kelola_panen.php') ? 'active' : ''; ?>" href="kelola_panen.php" >
                                <span class="menu-content">
                                    <i data-feather="feather" aria-hidden="true"></i>
                                    <span class="menu-text">Data Panen</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="show-cat-btn <?php echo ($currentPage == 'kelola_bantuan.php') ? 'active' : ''; ?>" href="kelola_bantuan.php" >
                                <span class="menu-content">
                                    <i data-feather="truck" aria-hidden="true"></i>
                                    <span class="menu-text">Bantuan</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="show-cat-btn <?php echo ($currentPage == 'kelola_laporan_panen.php') ? 'active' : ''; ?>" href="kelola_laporan_panen.php" >
                                <span class="menu-content">
                                    <i data-feather="cloud-snow" aria-hidden="true"></i>
                                    <span class="menu-text">Laporan Hasil Panen</span>
                                </span>
                            </a>
                        </li>
                        <li>
                            <a class="show-cat-btn <?php echo ($currentPage == 'kelola_laporan.php') ? 'active' : ''; ?>" href="kelola_laporan.php" >
                                <span class="menu-content">
                                    <i data-feather="calendar" aria-hidden="true"></i>
                                    <span class="menu-text">Laporan Tahunan</span>
                                </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
        <div class="main-wrapper">
            <!-- ! Main nav -->
            <nav class="main-nav--bg">
                <div class="container main-nav">
                    <div class="main-nav-start">
                        <form action="<?php echo $currentPage; ?>" method="GET" class="search-form">
                            <div class="search-wrapper">
                                <i data-feather="search" aria-hidden="true"></i>
                                <input type="text" 
                                    name="search" 
                                    id="search"
                                    placeholder="Cari Disini ..." 
                                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
                                    oninput="autoResetSearch()">
                            </div>
                        </form>
                    </div>
                    <div class="main-nav-end">
                        <button class="sidebar-toggle transparent-btn" title="Menu" type="button">
                            <span class="sr-only">Toggle menu</span>
                            <span class="icon menu-toggle--gray" aria-hidden="true"></span>
                        </button>
                         <a class="nav-logout-btn btn-danger-icon" href="logout.php"> <i data-feather="log-out" aria-hidden="true"></i> <span>LOG OUT</span> </a>
                         

                    </div>
                </div>
            </nav>
