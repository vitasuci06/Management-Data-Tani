<?php
include 'koneksi.php';
include 'header.php';
?>

<main class="main">
<div class="container laporan-cetak">

<?php

// JUDUL MUSIM
$judulMusim = "Semua Musim Panen";
if (@$_GET['musim'] == 1) $judulMusim = "Musim Panen I (Februari - Maret)";
elseif (@$_GET['musim'] == 2) $judulMusim = "Musim Panen II (Oktober - November)";
elseif (@$_GET['musim'] == 3) $judulMusim = "Musim Panen III (Juni - Juli)";

// SEARCH
$search = isset($_GET['search']) 
    ? mysqli_real_escape_string($koneksi, $_GET['search']) 
    : '';
?>

<h2 class="text-center laporan-title">LAPORAN TAHUNAN KELOMPOK TANI</h2>
<p class="text-center laporan-subtitle">Periode <?= $judulMusim; ?></p>

<!-- FILTER + CETAK -->
<div class="filter-responsive no-print">
    <form method="GET" class="filter-form">

        <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">

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

        <div class="filter-item d-flex gap-2">
            <select name="tahun"
                class="form-select"
                onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                <?php
                for ($thn = 2020; $thn <= 2025; $thn++) {
                    $selected = (@$_GET['tahun'] == $thn) ? 'selected' : '';
                    echo "<option value='$thn' $selected>$thn</option>";
                }
                ?>
            </select>

            <button type="button"
                onclick="window.print()"
                class="btn btn-primary filter-cetak">
                🖨️ Cetak PDF
            </button>
        </div>
    </form>
</div>

<?php
// =======================
// BUILD WHERE
// =======================
$conditions = [];

// Filter Musim
if (!empty($_GET['musim'])) {
    if ($_GET['musim'] == '1') {
        $conditions[] = "MONTH(hp.periode_panen) BETWEEN 2 AND 3";
    } elseif ($_GET['musim'] == '2') {
        $conditions[] = "MONTH(hp.periode_panen) BETWEEN 10 AND 11";
    } elseif ($_GET['musim'] == '3') {
        $conditions[] = "MONTH(hp.periode_panen) BETWEEN 6 AND 7";
    }
}

// Filter Tahun
if (!empty($_GET['tahun'])) {
    $tahun = mysqli_real_escape_string($koneksi, $_GET['tahun']);
    $conditions[] = "YEAR(hp.periode_panen) = '$tahun'";
}

// SEARCH FILTER
if ($search != '') {
    $conditions[] = "(
        p.nama_pengguna LIKE '%$search%' OR
        hp.jenis_tanaman LIKE '%$search%' OR
        hp.periode_tanam LIKE '%$search%' OR
        hp.periode_panen LIKE '%$search%'
    )";
}

$where = "";
if (!empty($conditions)) {
    $where = "WHERE " . implode(" AND ", $conditions);
}
// QUERY
$query = "
    SELECT hp.*, p.nama_pengguna
    FROM hasil_panen hp
    JOIN pengguna p ON hp.id_pengguna = p.id_pengguna
    $where
    ORDER BY hp.periode_panen DESC
";

$result = mysqli_query($koneksi, $query);
?>

<div class="laporan-responsive">
<table class="table-laporan">
<thead>
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
    echo '<tr><td colspan="8" class="text-center">Data laporan panen tidak tersedia</td></tr>';
} else {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
    <td><?= htmlspecialchars($row['jenis_tanaman']); ?></td>
    <td><?= number_format($row['luas_lahan'],2,',','.'); ?> m²</td>
    <td><?= date('M Y', strtotime($row['periode_tanam'])); ?></td>
    <td><?= date('M Y', strtotime($row['periode_panen'])); ?></td>
    <td><?= number_format($row['jumlah_panen'],1,',','.'); ?> Kg</td>
    <td><?= number_format($row['jumlah_pupuk'],1,',','.'); ?> Kg</td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>

</div>
</main>
<!-- AUTO RESET SEARCH = -->
<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;
    if (q.trim() === "") {
        window.location.href = "laporan.php";
    }
}
</script>
<?php include 'footer.php'; ?>