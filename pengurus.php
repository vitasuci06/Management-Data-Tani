<?php
include 'koneksi.php';
include 'header.php';
?>



<style>
.input-group-text {
    cursor: pointer;
}
</style>

<main class="main">
<div class="container">

<h2 class="main-title">Data Pengurus Kelompok Tani Makmur</h2>
    <div class="table-wrapper">
        <table class="table table-bordered table-striped">
            <thead class="table-hijau">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIK</th>
                    <th>Nomor Kartu Tani</th>
                    <th>Jabatan</th>
                </tr>
            </thead>

            <tbody>
            <?php 
            $no = 1;
            // --- START LOGIKA SEARCH ---
        $search_where = "WHERE p.jabatan != 'Anggota'";

        if (isset($_GET['search']) && !empty($_GET['search'])) {
            $searchTerm = mysqli_real_escape_string($koneksi, $_GET['search']);

            $search_where .= " AND (
                p.nama_pengguna LIKE '%$searchTerm%' OR 
                p.nik_pengguna LIKE '%$searchTerm%' OR 
                p.no_kartutani LIKE '%$searchTerm%' OR
                pg.username LIKE '%$searchTerm%'
            )";
        }
        // --- END LOGIKA SEARCH ---


        // QUERY UTAMA
        $query = "
            SELECT 
                pg.id_pengurus, pg.username, pg.password,
                p.id_pengguna, p.nama_pengguna, 
                p.nik_pengguna, p.no_kartutani, p.jabatan
            FROM pengurus pg
            INNER JOIN pengguna p ON pg.id_pengguna = p.id_pengguna
            $search_where
            ORDER BY p.nama_pengguna ASC
        ";

        $result = mysqli_query($koneksi, $query);

            while ($row = mysqli_fetch_assoc($result)) { ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
                    <td><?= htmlspecialchars($row['nik_pengguna']); ?></td>
                    <td><?= $row['no_kartutani'] ? htmlspecialchars($row['no_kartutani']) : 'N/A'; ?></td>
                    <td><?= htmlspecialchars($row['jabatan']); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
</main>


<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;

    // Jika search dikosongkan → reload tanpa parameter
    if (q.trim() === "") {
        window.location.href = "pengurus.php";
    }
}
</script>

<?php include 'footer.php'; ?>
