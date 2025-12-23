<?php
include 'koneksi.php';
include 'header_pengurus.php';
?>

<main class="main">
<div class="container">

<?php
$judulMusim = "Semua Musim Panen";
if (@$_GET['musim'] == 1) $judulMusim = "Musim Panen I (Februari - Maret)";
elseif (@$_GET['musim'] == 2) $judulMusim = "Musim Panen II (Oktober - November)";
elseif (@$_GET['musim'] == 3) $judulMusim = "Musim Panen III (Juni - Juli)";

?>

<h2 class="main-title">Kelola Data <?= $judulMusim; ?></h2>

<div class="filter-action filter-responsive mb-3">

    <button class="btn btn-primary btn-add"
        data-bs-toggle="modal"
        data-bs-target="#modalTambah">
        + Tambah Data Panen
    </button>

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
//  PAGINATION 
$limit = 10; // jumlah data per halaman
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;

/*  FILTER DATA LOGIC  */
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
    <th width="170">Aksi</th>
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
    <td>
        <button class="btn btn-warning btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit<?= $row['id_panen']; ?>">
            Edit
        </button>
        <a href="?hapus=<?= $row['id_panen']; ?>"
           onclick="return confirm('Hapus data ini?')"
           class="btn btn-danger btn-sm">
           Hapus
        </a>
    </td>
</tr>
<?php } } ?>
</tbody>
<?php if ($totalPage > 1) { ?>
    <tfoot>
<tr>
<td colspan="9">
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
<!-- form tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Tambah Data Panen</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<label>Nama Petani</label>
<select name="id_pengguna" class="form-control" required>
<option value="">- Pilih Petani -</option>
<?php
$q = mysqli_query($koneksi, "SELECT * FROM pengguna ORDER BY nama_pengguna ASC");
while ($p = mysqli_fetch_assoc($q)) {
    echo "<option value='{$p['id_pengguna']}'>{$p['nama_pengguna']}</option>";
}
?>
</select>

<label class="mt-2">Jenis Tanaman</label>
<input type="text" name="jenis_tanaman" class="form-control" required>

<label class="mt-2">Luas Lahan (m²)</label>
<input type="number" step="0.01" name="luas_lahan" class="form-control" required>

<label class="mt-2">Periode Tanam</label>
<input type="date" name="periode_tanam" class="form-control" required>

<label class="mt-2">Periode Panen</label>
<input type="date" name="periode_panen" class="form-control" required>

<label class="mt-2">Jumlah Panen (Ton)</label>
<input type="number" step="0.1" name="jumlah_panen" class="form-control" required>

<label class="mt-2">Jumlah Pupuk (Kg)</label>
<input type="number" step="0.1" name="jumlah_pupuk" class="form-control" required>
</div>

<div class="modal-footer">
<button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
</div>

</form>
</div>
</div>
</div>

<?php
/* simpan*/
if (isset($_POST['tambah'])) {
    mysqli_query($koneksi, "
        INSERT INTO hasil_panen 
        (id_pengguna, jenis_tanaman, luas_lahan, periode_tanam, periode_panen, jumlah_panen, jumlah_pupuk)
        VALUES (
            '$_POST[id_pengguna]',
            '$_POST[jenis_tanaman]',
            '$_POST[luas_lahan]',
            '$_POST[periode_tanam]',
            '$_POST[periode_panen]',
            '$_POST[jumlah_panen]',
            '$_POST[jumlah_pupuk]'
        )
    ");

    echo "<script>alert('Data panen berhasil ditambahkan');location='kelola_panen.php';</script>";
}

/* form edit*/
$qEdit = mysqli_query($koneksi, "SELECT * FROM hasil_panen");
while ($e = mysqli_fetch_assoc($qEdit)) {
?>
<div class="modal fade" id="modalEdit<?= $e['id_panen']; ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
<h5 class="modal-title">Edit Data Panen</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="id_panen" value="<?= $e['id_panen']; ?>">

<label>Jenis Tanaman</label>
<input type="text" name="jenis_tanaman" value="<?= $e['jenis_tanaman']; ?>" class="form-control" required>

<label class="mt-2">Luas Lahan (m²)</label>
<input type="number" step="0.01" name="luas_lahan" value="<?= $e['luas_lahan']; ?>" class="form-control" required>

<label class="mt-2">Periode Tanam</label>
<input type="date" name="periode_tanam" value="<?= $e['periode_tanam']; ?>" class="form-control" required>

<label class="mt-2">Periode Panen</label>
<input type="date" name="periode_panen" value="<?= $e['periode_panen']; ?>" class="form-control" required>

<label class="mt-2">Jumlah Panen (Ton)</label>
<input type="number" step="0.1" name="jumlah_panen" value="<?= $e['jumlah_panen']; ?>" class="form-control" required>

<label class="mt-2">Jumlah Pupuk (Kg)</label>
<input type="number" step="0.1" name="jumlah_pupuk" value="<?= $e['jumlah_pupuk']; ?>" class="form-control" required>
</div>

<div class="modal-footer">
<button type="submit" name="update" class="btn btn-success">Simpan</button>
</div>

</form>
</div>
</div>
</div>
<?php } ?>

<?php
/* update*/
if (isset($_POST['update'])) {
    mysqli_query($koneksi, "
        UPDATE hasil_panen SET
        jenis_tanaman='$_POST[jenis_tanaman]',
        luas_lahan='$_POST[luas_lahan]',
        periode_tanam='$_POST[periode_tanam]',
        periode_panen='$_POST[periode_panen]',
        jumlah_panen='$_POST[jumlah_panen]',
        jumlah_pupuk='$_POST[jumlah_pupuk]'
        WHERE id_panen='$_POST[id_panen]'
    ");

    echo "<script>alert('Data panen berhasil diupdate');location='kelola_panen.php';</script>";
}

/* hapus*/
if (isset($_GET['hapus'])) {
    mysqli_query($koneksi, "DELETE FROM hasil_panen WHERE id_panen='$_GET[hapus]'");
    echo "<script>alert('Data panen berhasil dihapus');location='kelola_panen.php';</script>";
}
?>

</div>
</main>

<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;

    if (q.trim() === "") {
        window.location.href = "kelola_panen.php";
    }
}
</script>
<?php include 'footer.php'; ?>