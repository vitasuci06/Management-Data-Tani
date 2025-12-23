<?php
include 'koneksi.php';
include 'header.php';
?>

<main class="main">
<div class="container">

<?php
$judulMusim = "Semua Musim Panen";
if (@$_GET['musim'] == 1) $judulMusim = "Musim Panen I (Februari - Maret)";
elseif (@$_GET['musim'] == 2) $judulMusim = "Musim Panen II (Oktober - November)";
elseif (@$_GET['musim'] == 3) $judulMusim = "Musim Panen III (Juni - Juli)";

?>

<h2 class="main-title">Data <?= $judulMusim; ?></h2>

<div class="filter-action filter-mobile">
    <form method="GET" id="formFilter" class="filter-form">
        
        <div class="filter-item">
            <select name="musim"
                class="form-select"
                onchange="this.form.submit()">
                <option value="">Semua Musim Panen</option>
                <option value="1" <?= (@$_GET['musim']==1?'selected':''); ?>>
                    Musim Panen I (Februari - Maret)
                </option>
                <option value="2" <?= (@$_GET['musim']==2?'selected':''); ?>>
                    Musim Panen II (Oktober - November)
                </option>
                <option value="3" <?= (@$_GET['musim']==3?'selected':''); ?>>
                    Musim Panen III (Juni - Juli)
                </option>
            </select>
        </div>

        <div class="filter-item">
            <select name="tahun"
                class="form-select"
                onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                <?php 
                for($thn=2020; $thn<=2025; $thn++){
                    $selected = (@$_GET['tahun'] == $thn) ? 'selected' : '';
                    echo "<option value='$thn' $selected>$thn</option>";
                }
                ?>
            </select>
        </div>
    </form>
</div>


<?php
$limit = 10; // jumlah data per halaman
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;
$conditions = [];

// Filter Musim
if (isset($_GET['musim']) && $_GET['musim'] != "") {
    if ($_GET['musim'] == '1') {
        $conditions[] = "MONTH(hp.periode_panen) BETWEEN 2 AND 3";
    } elseif ($_GET['musim'] == '2') {
        $conditions[] = "MONTH(hp.periode_panen) BETWEEN 10 AND 11";
    } elseif ($_GET['musim'] == '3') {
        $conditions[] = "MONTH(hp.periode_panen) BETWEEN 6 AND 7";
    }
}

// Filter Tahun
if (isset($_GET['tahun']) && $_GET['tahun'] != "") {
    $tahun = mysqli_real_escape_string($koneksi, $_GET['tahun']);
    $conditions[] = "YEAR(hp.periode_panen) = '$tahun'";
}

// Filter Search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $conditions[] = "(p.nama_pengguna LIKE '%$search%' OR hp.jenis_tanaman LIKE '%$search%')";
}

// Build WHERE Clause
$where = "";
if (count($conditions) > 0) {
    $where = "WHERE " . implode(" AND ", $conditions);
}

$countQuery = "
    SELECT COUNT(*) as total
    FROM hasil_panen hp
    JOIN pengguna p ON hp.id_pengguna = p.id_pengguna
    $where
";

$countResult = mysqli_fetch_assoc(mysqli_query($koneksi, $countQuery));
$totalData = $countResult['total'];
$totalPage = ceil($totalData / $limit);

$query = "
    SELECT hp.*, p.nama_pengguna
    FROM hasil_panen hp
    JOIN pengguna p ON hp.id_pengguna = p.id_pengguna
    $where
    ORDER BY hp.periode_panen DESC
    LIMIT $limit OFFSET $offset
";

$result = mysqli_query($koneksi, $query);
?>

<div class="table-wrapper">
<table class="table table-bordered table-striped">
<thead class="table-hijau">
<tr>
    <th>No</th>
    <th>Nama Petani</th>
    <th>Jenis Tanaman</th>
    <th>Luas Lahan (m²)</th>
    <th>Periode Tanam</th>
    <th>Periode Panen</th>
    <th>Jumlah Panen (Kg)</th>
    <th>Jumlah Pupuk (Kg)</th>
</tr>
</thead>

<tbody>
<?php
$no = 1;
if (mysqli_num_rows($result) == 0) {
    echo '<tr><td colspan="9" class="text-center">Data panen tidak ditemukan</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
    <td><?= htmlspecialchars($row['jenis_tanaman']); ?></td>
    <td><?= number_format($row['luas_lahan'],2,',','.'); ?> m²</td>
    <td><?= date('d-m-Y', strtotime($row['periode_tanam'])); ?></td>
    <td><?= date('d-m-Y', strtotime($row['periode_panen'])); ?></td>
    <td><?= number_format($row['jumlah_panen'],1,',','.'); ?> Kg</td>
    <td><?= number_format($row['jumlah_pupuk'],1,',','.'); ?> Kg</td>
</tr>
<?php } } ?>
</tbody>
<?php if ($totalPage > 1) { ?>
    <tfoot>
<tr>
<td colspan="9">
    <div class="d-flex justify-content-between align-items-center">

        <!-- Info halaman -->
        <small>
            Halaman <?= $page; ?> dari <?= $totalPage; ?>
        </small>

        <!-- Tombol pagination -->
        <div class="btn-group">

            <?php
            // Ambil semua parameter GET kecuali page
            $params = $_GET;
            unset($params['page']);
            $queryString = http_build_query($params);
            ?>

            <!-- Prev -->
            <?php if ($page > 1) { ?>
                <a class="btn btn-outline-secondary btn-sm"
                   href="?<?= $queryString; ?>&page=<?= $page - 1; ?>">
                   « Prev
                </a>
            <?php } ?>

            <!-- Next -->
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
        window.location.href = "panen.php";
    }
}
</script>
<?php include 'footer.php'; ?>