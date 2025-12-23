<?php
include 'koneksi.php';
include 'header.php';
?>

<main class="main">
<div class="container">

<h2 class="main-title">Data Anggota Kelompok Tani Makmur</h2>
<div class="table-wrapper">
<table class="table table-bordered table-striped">
    <thead class="table-hijau">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Nomor Kartu Tani</th>
        </tr>
    </thead>

    <tbody>
    <?php

    // PAGINATION SETUP
    $limit  = 10;
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page   = ($page < 1) ? 1 : $page;
    $offset = ($page - 1) * $limit;
    $no     = $offset + 1;

    // FILTER SEARCH

    $search_where = "WHERE jabatan = 'Anggota'";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchTerm = mysqli_real_escape_string($koneksi, $_GET['search']);
        $search_where .= " AND (
            nama_pengguna LIKE '%$searchTerm%' OR
            nik_pengguna LIKE '%$searchTerm%' OR
            no_kartutani LIKE '%$searchTerm%'
        )";
    }

    // HITUNG TOTAL DATA
    $countQuery = "
        SELECT COUNT(*) as total
        FROM pengguna
        $search_where
    ";
    $countResult = mysqli_fetch_assoc(mysqli_query($koneksi, $countQuery));
    $totalData = $countResult['total'];
    $totalPage = ceil($totalData / $limit);

    // QUERY DATA
    $result = mysqli_query($koneksi, "
        SELECT * FROM pengguna
        $search_where
        ORDER BY id_pengguna DESC
        LIMIT $limit OFFSET $offset
    ");

    if (mysqli_num_rows($result) == 0) {
        echo '<tr><td colspan="5" class="text-center">Data anggota tidak ditemukan</td></tr>';
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
            <td><?= htmlspecialchars($row['nik_pengguna']); ?></td>
            <td><?= $row['no_kartutani'] ? htmlspecialchars($row['no_kartutani']) : 'N/A'; ?></td>
        </tr>
    <?php } } ?>
    </tbody>

    <?php if ($totalPage > 1) { ?>
    <tfoot>
    <tr>
    <td colspan="5">
        <div class="d-flex justify-content-between align-items-center">
            <small>
                Halaman <?= $page; ?> dari <?= $totalPage; ?>
            </small>

            <div class="btn-group">
                <?php
                $params = $_GET;
                unset($params['page']);
                $queryString = http_build_query($params);
                ?>

                <?php if ($page > 1) { ?>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="?<?= $queryString; ?>&page=<?= $page - 1; ?>">
                        « Prev
                    </a>
                <?php } ?>

                <?php if ($page < $totalPage) { ?>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="?<?= $queryString; ?>&page=<?= $page + 1; ?>">
                        Next »
                    </a>
                <?php } ?>
            </div>
        </div>
    </td>
    </tr>
    </tfoot>
    <?php } ?>
</table>
</div>
</div>
</main>

<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;
    if (q.trim() === "") {
        window.location.href = "anggota.php";
    }
}
</script>
<?php include 'footer.php'; ?>
