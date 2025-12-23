<?php
include 'koneksi.php';
include 'header.php';

$tahunDipilih = isset($_GET['tahun']) ? $_GET['tahun'] : '';
?>

<main class="main">
<div class="container">

<h2 class="main-title">Data Pengajuan Pupuk Subsidi</h2>

<div class="filter-action filter-responsive mb-3">
    <form method="GET" id="formpupuk" class="filter-form">

        <!-- FILTER TAHUN -->
        <div class="filter-item">
            <select name="tahun"
                    class="form-select"
                    onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                <?php
                $tahunQuery = mysqli_query($koneksi, "
                    SELECT DISTINCT YEAR(tanggal_pengajuan) AS tahun
                    FROM pupuk_subsidi
                    ORDER BY tahun DESC
                ");
                while ($t = mysqli_fetch_assoc($tahunQuery)) {
                    $selected = ($tahunDipilih == $t['tahun']) ? 'selected' : '';
                    echo "<option value='{$t['tahun']}' $selected>{$t['tahun']}</option>";
                }
                ?>
            </select>
        </div>

    </form>
</div>

<!--  TABLE  -->
<div class="table-wrapper">
<table class="table table-bordered table-striped">
<thead class="table-hijau">
<tr>
    <th>No</th>
    <th>Nama Petani</th>
    <th>No Kartu Tani</th>
    <th>Tanggal Pengajuan</th>
    <th>Jumlah Pupuk</th>
    <th>Harga Satuan</th>
    <th>Total Harga</th>
    <th width="170px">Aksi</th>
</tr>
</thead>
<tbody>

<?php
$no = 1;
$where = " WHERE 1=1 ";

/* FILTER TAHUN */
if (isset($_GET['tahun']) && $_GET['tahun'] != "") {
    $tahun = mysqli_real_escape_string($koneksi, $_GET['tahun']);
    $where .= " AND YEAR(ps.tanggal_pengajuan) = '$tahun'";
}

/* SEARCH */
if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
    $where .= " AND (
        u.nama_pengguna LIKE '%$search%' OR
        u.no_kartutani LIKE '%$search%'
    )";
}

$query = "
    SELECT 
        ps.id_pupuk,
        u.nama_pengguna,
        u.no_kartutani,
        ps.tanggal_pengajuan,
        ps.jumlah_pupuk,
        ps.harga_satuan,
        ps.total_harga
    FROM pupuk_subsidi ps
    JOIN pengguna u ON ps.id_pengguna = u.id_pengguna
    $where
    ORDER BY ps.tanggal_pengajuan DESC
";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<tr>
            <td colspan='8' class='text-center'>
                Data pupuk subsidi belum tersedia
            </td>
          </tr>";
} else {
while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
    <td><?= htmlspecialchars($row['no_kartutani']); ?></td>
    <td><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
    <td><?= $row['jumlah_pupuk']; ?></td>
    <td>Rp <?= number_format($row['harga_satuan']); ?></td>
    <td>Rp <?= number_format($row['total_harga']); ?></td>
    <td>
        <button class="btn btn-warning btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#modalEdit<?= $row['id_pupuk']; ?>">
            Edit
        </button>

        <a href="?hapus=<?= $row['id_pupuk']; ?>"
           onclick="return confirm('Hapus data pupuk?')"
           class="btn btn-danger btn-sm">
           Hapus
        </a>
    </td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>

<!-- Modal tambah -->
<div class="modal fade" id="modalTambah">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Tambah Pupuk Subsidi</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<label>Petani</label>
<select name="id_pengguna" class="form-control" required>
    <option value="">-- Pilih Petani --</option>
    <?php
    $petani = mysqli_query($koneksi, "
        SELECT id_pengguna, nama_pengguna, no_kartutani
        FROM pengguna
        ORDER BY nama_pengguna ASC
    ");
    while ($p = mysqli_fetch_assoc($petani)) {
        echo "<option value='{$p['id_pengguna']}'>
            {$p['nama_pengguna']} - {$p['no_kartutani']}
        </option>";
    }
    ?>
</select>

<label class="mt-2">Tanggal Pengajuan</label>
<input type="date" name="tanggal_pengajuan" class="form-control" required>

<label class="mt-2">Jumlah Pupuk</label>
<input type="number" name="jumlah_pupuk" class="form-control" required>

<label class="mt-2">Harga Satuan</label>
<input type="number" name="harga_satuan" class="form-control" required>

</div>

<div class="modal-footer">
    <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
</div>

</form>
</div>
</div>
</div>

<?php
/* PROSES TAMBAH */
if (isset($_POST['tambah'])) {

    $total = $_POST['jumlah_pupuk'] * $_POST['harga_satuan'];

    mysqli_query($koneksi, "
        INSERT INTO pupuk_subsidi
        (id_pengguna, tanggal_pengajuan, jumlah_pupuk, harga_satuan, total_harga)
        VALUES (
            '$_POST[id_pengguna]',
            '$_POST[tanggal_pengajuan]',
            '$_POST[jumlah_pupuk]',
            '$_POST[harga_satuan]',
            '$total'
        )
    ");

    echo "<script>
        alert('Data pupuk berhasil ditambahkan');
        location='kelola_pupuk.php';
    </script>";
}
?>

<!--  MODAL EDIT  -->
<?php
$qEdit = mysqli_query($koneksi, "SELECT * FROM pupuk_subsidi");
while ($e = mysqli_fetch_assoc($qEdit)) {
?>
<div class="modal fade" id="modalEdit<?= $e['id_pupuk']; ?>">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Edit Pupuk Subsidi</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
<input type="hidden" name="id_pupuk" value="<?= $e['id_pupuk']; ?>">

<label>Jumlah Pupuk</label>
<input type="number" name="jumlah_pupuk"
       value="<?= $e['jumlah_pupuk']; ?>"
       class="form-control" required>

<label class="mt-2">Harga Satuan</label>
<input type="number" name="harga_satuan"
       value="<?= $e['harga_satuan']; ?>"
       class="form-control" required>
</div>

<div class="modal-footer">
    <button type="submit" name="update" class="btn btn-success">Update</button>
</div>

</form>
</div>
</div>
</div>
<?php } ?>

<?php
/* PROSES UPDATE */
if (isset($_POST['update'])) {

    $total = $_POST['jumlah_pupuk'] * $_POST['harga_satuan'];

    mysqli_query($koneksi, "
        UPDATE pupuk_subsidi SET
            jumlah_pupuk='$_POST[jumlah_pupuk]',
            harga_satuan='$_POST[harga_satuan]',
            total_harga='$total'
        WHERE id_pupuk='$_POST[id_pupuk]'
    ");

    echo "<script>
        alert('Data pupuk berhasil diupdate');
        location='kelola_pupuk.php';
    </script>";
}

/* PROSES HAPUS */
if (isset($_GET['hapus'])) {
    mysqli_query($koneksi, "
        DELETE FROM pupuk_subsidi
        WHERE id_pupuk='$_GET[hapus]'
    ");

    echo "<script>
        alert('Data pupuk berhasil dihapus');
        location='kelola_pupuk.php';
    </script>";
}
?>

</div>
</main>

<!-- AUTO RESET SEARCH = -->
<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;
    if (q.trim() === "") {
        window.location.href = "pupuk.php";
    }
}
</script>

<?php include 'footer.php'; ?>
