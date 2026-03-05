<?php
include 'koneksi.php';
include 'header_pengurus.php';

$jenisDipilih  = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$tahunDipilih  = isset($_GET['tahun']) ? $_GET['tahun'] : '';
$search        = isset($_GET['search']) ? $_GET['search'] : '';

$jenisbantuan = "Semua Jenis Bantuan";
if ($jenisDipilih == 1) {
    $jenisbantuan = "Bantuan Alat Tani";
} elseif ($jenisDipilih == 2) {
    $jenisbantuan = "Bantuan Bibit Pertanian";
}
?>

<main class="main">
<div class="container">

<h2 class="main-title">Kelola Data <?= $jenisbantuan; ?></h2>

<div class="filter-action filter-responsive mb-3">

    <button class="btn btn-primary btn-add"
            data-bs-toggle="modal"
            data-bs-target="#modalTambah">
        + Tambah Bantuan
    </button>

    <form method="GET" class="filter-form">

        <!-- FILTER JENIS -->
        <div class="filter-item">
            <select name="jenis" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Jenis Bantuan</option>
                <option value="1" <?= ($jenisDipilih==1?'selected':''); ?>>
                    Bantuan Alat Tani
                </option>
                <option value="2" <?= ($jenisDipilih==2?'selected':''); ?>>
                    Bantuan Bibit Pertanian
                </option>
            </select>
        </div>

        <!-- FILTER TAHUN -->
        <div class="filter-item">
            <select name="tahun" class="form-select" onchange="this.form.submit()">
                <option value="">Semua Tahun</option>
                <?php
                $tahunQuery = mysqli_query($koneksi, "
                    SELECT DISTINCT YEAR(tanggal_diberikan) AS tahun
                    FROM bantuan
                    ORDER BY tahun DESC
                ");
                while ($t = mysqli_fetch_assoc($tahunQuery)) {
                    $selected = ($tahunDipilih == $t['tahun']) ? 'selected' : '';
                    echo "<option value='{$t['tahun']}' $selected>{$t['tahun']}</option>";
                }
                ?>
            </select>
        </div>

        <?php if ($search != "") { ?>
            <input type="hidden" name="search" value="<?= htmlspecialchars($search); ?>">
        <?php } ?>

    </form>
</div>

<?php
// DATA KETUA
$qKetua = mysqli_query($koneksi, "
    SELECT id_pengguna, nama_pengguna, jabatan
    FROM pengguna
    WHERE LOWER(jabatan)='ketua'
    LIMIT 1
");
$ketua = mysqli_fetch_assoc($qKetua);
?>

<div class="penerima-box mb-3">
    <span class="label">Penerima</span><br>
    <span class="value"><?= htmlspecialchars($ketua['nama_pengguna']); ?></span>
    <span class="jabatan"> | <?= htmlspecialchars($ketua['jabatan']); ?></span>
</div>

<div class="table-wrapper">
<table class="table table-bordered table-striped">
<thead class="table-hijau">
<tr>
    <th>No</th>
    <th>Jenis Bantuan</th>
    <th>Nama Bantuan</th>
    <th>Tanggal Diberikan</th>
    <th>Jumlah</th>
    <th width="180px">Aksi</th>
</tr>
</thead>
<tbody>

<?php
$no = 1;
$where = " WHERE 1=1 ";

// FILTER JENIS
if ($jenisDipilih != "") {
    if ($jenisDipilih == 1) {
        $where .= " AND b.jenis_bantuan='Alat'";
    } elseif ($jenisDipilih == 2) {
        $where .= " AND b.jenis_bantuan='Bibit'";
    }
}

// FILTER TAHUN
if ($tahunDipilih != "") {
    $tahunDipilih = mysqli_real_escape_string($koneksi, $tahunDipilih);
    $where .= " AND YEAR(b.tanggal_diberikan) = '$tahunDipilih'";
}

// FILTER SEARCH
if ($search != "") {
    $search = mysqli_real_escape_string($koneksi, $search);
    $where .= " AND (
        b.nama_bantuan LIKE '%$search%' OR
        b.jenis_bantuan LIKE '%$search%' OR
        b.tanggal_diberikan LIKE '%$search%'
    )";
}

$query = "
    SELECT b.*, p.nama_pengguna
    FROM bantuan b
    JOIN pengguna p ON b.id_pengguna = p.id_pengguna
    $where
    ORDER BY b.tanggal_diberikan DESC
";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<tr>
            <td colspan='6' class='text-center'>
                Data bantuan belum tersedia
            </td>
          </tr>";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
    <td><?= $no++; ?></td>
    <td><?= $row['jenis_bantuan']; ?></td>
    <td><?= htmlspecialchars($row['nama_bantuan']); ?></td>
    <td><?= date('d-m-Y', strtotime($row['tanggal_diberikan'])); ?></td>
    <td>
        <?php
        if (strtolower($row['jenis_bantuan']) == 'alat') {
            echo $row['jumlah_bantuan'] . ' buah';
        } elseif (strtolower($row['jenis_bantuan']) == 'bibit') {
            echo $row['jumlah_bantuan'] . ' kg';
        } else {
            echo $row['jumlah_bantuan'];
        }
        ?>
    </td>

    <td>
        <button class="btn btn-warning btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#modalEdit<?= $row['id_bantuan']; ?>">
            Edit
        </button>
        <a href="?hapus=<?= $row['id_bantuan']; ?>"
           onclick="return confirm('Hapus data bantuan ini?')"
           class="btn btn-danger btn-sm">
           Hapus
        </a>
    </td>
</tr>
<?php } } ?>
</tbody>
</table>
</div>
<!-- form tambah -->
<div class="modal fade" id="modalTambah">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Tambah Bantuan</h5>
</div>

<div class="modal-body">
<input type="hidden" name="id_pengguna" value="<?= $ketua['id_pengguna']; ?>">

<label class="mt-2">Jenis Bantuan</label>
<select name="jenis_bantuan" class="form-select" required>
    <option value="">-- Pilih Jenis Bantuan --</option>
    <option value="Alat">Bantuan Alat Tani</option>
    <option value="Bibit">Bantuan Bibit Pertanian</option>
</select>

<label class="mt-2">Nama Bantuan</label>
<input type="text" name="nama_bantuan" class="form-control" required>

<label class="mt-2">Tanggal Diberikan</label>
<input type="date" name="tanggal_diberikan" class="form-control" required>

<label class="mt-2">Jumlah Bantuan</label>
<input type="number" name="jumlah_bantuan" class="form-control" required>
</div>

<div class="modal-footer">
    <button type="submit" name="tambah" class="btn btn-primary">Tambah</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
</div>

</form>
</div>
</div>
</div>

<?php
// action tambah
if (isset($_POST['tambah'])) {
    mysqli_query($koneksi, "
        INSERT INTO bantuan
        (id_pengguna, jenis_bantuan, nama_bantuan, tanggal_diberikan, jumlah_bantuan)
        VALUES (
            '$_POST[id_pengguna]',
            '$_POST[jenis_bantuan]',
            '$_POST[nama_bantuan]',
            '$_POST[tanggal_diberikan]',
            '$_POST[jumlah_bantuan]'
        )
    ");

    echo "<script>
        alert('Data bantuan berhasil ditambahkan');
        location='kelola_bantuan.php';
    </script>";
}
?>

<!-- form edit -->
<?php
$qEdit = mysqli_query($koneksi, "SELECT * FROM bantuan");
while ($e = mysqli_fetch_assoc($qEdit)) {
?>
<div class="modal fade" id="modalEdit<?= $e['id_bantuan']; ?>">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Edit Bantuan</h5>
</div>

<div class="modal-body">
<input type="hidden" name="id_bantuan" value="<?= $e['id_bantuan']; ?>">

<label>Jenis Bantuan</label>
<div class="readonly-text">
    <?= htmlspecialchars($e['jenis_bantuan']); ?>
</div>
<label class="mt-2">Nama Bantuan</label>
<input type="text" name="nama_bantuan"
       value="<?= htmlspecialchars($e['nama_bantuan']); ?>"
       class="form-control" required>

<label class="mt-2">Tanggal Diberikan</label>
<input type="date" name="tanggal_diberikan"
       value="<?= $e['tanggal_diberikan']; ?>"
       class="form-control" required>

<label class="mt-2">Jumlah Bantuan</label>
<input type="number" name="jumlah_bantuan"
       value="<?= $e['jumlah_bantuan']; ?>"
       class="form-control" required>
</div>

<div class="modal-footer">
    <button type="submit" name="update" class="btn btn-success">Simpan</button>
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
</div>

</form>
</div>
</div>
</div>
<?php } ?>

<?php
// action update
if (isset($_POST['update'])) {
    mysqli_query($koneksi, "
        UPDATE bantuan SET
        nama_bantuan='$_POST[nama_bantuan]',
        tanggal_diberikan='$_POST[tanggal_diberikan]',
        jumlah_bantuan='$_POST[jumlah_bantuan]'
        WHERE id_bantuan='$_POST[id_bantuan]'
    ");

    echo "<script>
        alert('Data bantuan berhasil diupdate');
        location='kelola_bantuan.php';
    </script>";
}

//hapus
if (isset($_GET['hapus'])) {
    mysqli_query($koneksi, "
        DELETE FROM bantuan
        WHERE id_bantuan='$_GET[hapus]'
    ");

    echo "<script>
        alert('Data bantuan berhasil dihapus');
        location='kelola_bantuan.php';
    </script>";
}
?>

</div>
</main>
<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;
    if (q.trim() === "") {
        window.location.href = "kelola_bantuan.php";
    }
}
</script>
<?php include 'footer.php'; ?>
