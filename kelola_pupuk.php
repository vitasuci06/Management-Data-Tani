<?php
include 'koneksi.php';
include 'header_pengurus.php';
?>

<main class="main">
<div class="container">

<h2 class="main-title">Kelola Data Pengajuan Pupuk Subsidi</h2>

<?php
$qKuota = mysqli_query($koneksi, "SELECT * FROM kuota_pupuk");
?>

<!-- ===================== KARTU KUOTA ===================== -->
<div class="row mb-4">
<?php
while ($k = mysqli_fetch_assoc($qKuota)) {

    $jenis = $k['jenis_pupuk'];

    $qTerpakai = mysqli_query($koneksi,"
        SELECT SUM(jumlah_pupuk) as terpakai
        FROM pupuk_subsidi
        WHERE jenis_pupuk='$jenis'
    ");
    $dataTerpakai = mysqli_fetch_assoc($qTerpakai);
    $terpakai = $dataTerpakai['terpakai'] ?? 0;

    $sisa = $k['total_kuota'] - $terpakai;

    if ($sisa <= 0) {
        $warna = "danger";
        $status = "HABIS";
        $icon = "bi-x-circle-fill";
    } elseif ($sisa <= 10) {
        $warna = "warning";
        $status = "Hampir Habis";
        $icon = "bi-exclamation-triangle-fill";
    } else {
        $warna = "success";
        $status = "Tersedia";
        $icon = "bi-check-circle-fill";
    }
?>
<div class="col-md-3 mb-3">
    <div class="kuota-card border-<?= $warna ?>">
        <div class="kuota-icon-<?= $warna ?>">
            <i class="bi <?= $icon ?>"></i>
        </div>
        <div class="kuota-content">
            <p class="fw-semibold mb-2"><?= htmlspecialchars($jenis); ?></p>
            <h2><?= $sisa ?> <small>sak</small></h2>
            <small>
                Total: <?= $k['total_kuota']; ?> sak |
                Terpakai: <?= $terpakai; ?> sak
            </small>
            <div class="mt-2">
                <span class="badge bg-<?= $warna ?>">
                    <?= $status; ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?php } ?>
</div>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
+ Tambah Pengajuan
</button>

<button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#modalKuota">
+ Tambah Kuota
</button>

<!-- ===================== TABLE ===================== -->
<div class="table-wrapper">
<table class="table table-bordered table-striped">
<thead class="table-hijau">
<tr>
<th>No</th>
<th>Nama Petani</th>
<th>No Kartu Tani</th>
<th>Tanggal</th>
<th>Jumlah</th>
<th>Jenis</th>
<th>Harga Satuan</th>
<th>Total Harga</th>
<th width="170px">Aksi</th>
</tr>
</thead>
<tbody>

<?php
$no = 1;

$query = "
SELECT 
    ps.*, 
    u.nama_pengguna,
    u.no_kartutani
FROM pupuk_subsidi ps
JOIN pengguna u ON ps.id_pengguna = u.id_pengguna
ORDER BY ps.tanggal_pengajuan DESC
";

$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    echo "<tr><td colspan='9' class='text-center'>Data belum tersedia</td></tr>";
} else {
    while ($row = mysqli_fetch_assoc($result)) {
?>
<tr>
<td><?= $no++; ?></td>
<td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
<td><?= htmlspecialchars($row['no_kartutani']); ?></td>
<td><?= date('d-m-Y', strtotime($row['tanggal_pengajuan'])); ?></td>
<td><?= $row['jumlah_pupuk']; ?> sak</td>
<td><?= htmlspecialchars($row['jenis_pupuk']); ?></td>
<td>Rp <?= number_format($row['harga_satuan']); ?></td>
<td>Rp <?= number_format($row['total_harga']); ?></td>
<td>
    <button class="btn btn-warning btn-sm"
        data-bs-toggle="modal"
        data-bs-target="#modalEdit<?= $row['id_pupuk']; ?>">
        Edit
    </button>

    <a href="?hapus=<?= $row['id_pupuk']; ?>"
       onclick="return confirm('Hapus data?')"
       class="btn btn-danger btn-sm">
       Hapus
    </a>
</td>
</tr>
<!-- ===================== MODAL EDIT ===================== -->
<div class="modal fade" id="modalEdit<?= $row['id_pupuk']; ?>" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
<h5>Edit Pengajuan</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<input type="hidden" name="id_pupuk" value="<?= $row['id_pupuk']; ?>">

<label>Jenis Pupuk</label>
<input type="text" name="jenis_pupuk"
       value="<?= htmlspecialchars($row['jenis_pupuk']); ?>"
       class="form-control" required>

<label class="mt-2">Jumlah (sak)</label>
<input type="number" name="jumlah_pupuk"
       value="<?= $row['jumlah_pupuk']; ?>"
       class="form-control" required>

<label class="mt-2">Harga Satuan</label>
<input type="number" name="harga_satuan"
       value="<?= $row['harga_satuan']; ?>"
       class="form-control" required>

</div>

<div class="modal-footer">
<button type="submit" name="edit" class="btn btn-primary">
Simpan
</button>
</div>

</form>
</div>
</div>
</div>

<?php }} ?>
</tbody>
</table>
</div>

</div>
</main>

<!-- ===================== MODAL TAMBAH PENGAJUAN ===================== -->
<div class="modal fade" id="modalTambah">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
<h5>Tambah Pengajuan</h5>
</div>

<div class="modal-body">

<label>Petani</label>
<select name="id_pengguna" class="form-select" required>
<option value="">-- Pilih --</option>
<?php
$petani = mysqli_query($koneksi,"SELECT * FROM pengguna");
while ($p = mysqli_fetch_assoc($petani)) {
    echo "<option value='$p[id_pengguna]'>$p[nama_pengguna]</option>";
}
?>
</select>

<label class="mt-2">Jenis Pupuk</label>
<input type="text" name="jenis_pupuk" class="form-control" required>

<label class="mt-2">Jumlah (sak)</label>
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

<!-- ===================== MODAL TAMBAH KUOTA ===================== -->
<div class="modal fade" id="modalKuota">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
<h5>Tambah Kuota</h5>
</div>

<div class="modal-body">

<label>Jenis Pupuk</label>
<input type="text" name="jenis_kuota"
       class="form-control"
       placeholder="Contoh: Urea, NPK"
       required>

<label class="mt-2">Tambah Kuota (sak)</label>
<input type="number"
       name="jumlah_tambah"
       class="form-control"
       min="1"
       required>

</div>

<div class="modal-footer">
<button type="submit" name="tambah_kuota" class="btn btn-success">
Simpan
</button>
</div>

</form>
</div>
</div>
</div>



<?php
// ================= TAMBAH PENGAJUAN =================
if (isset($_POST['tambah'])) {

    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_pupuk']);
    $jumlah = (int) $_POST['jumlah_pupuk'];
    $harga  = (int) $_POST['harga_satuan'];

    $qKuota = mysqli_query($koneksi,"
        SELECT total_kuota FROM kuota_pupuk
        WHERE jenis_pupuk='$jenis'
    ");

    if (mysqli_num_rows($qKuota) == 0) {
        echo "<script>alert('Jenis pupuk belum memiliki kuota!');</script>";
    } else {

        $dataKuota = mysqli_fetch_assoc($qKuota);

        $qTerpakai = mysqli_query($koneksi,"
            SELECT SUM(jumlah_pupuk) as terpakai
            FROM pupuk_subsidi
            WHERE jenis_pupuk='$jenis'
        ");

        $dataTerpakai = mysqli_fetch_assoc($qTerpakai);
        $terpakai = $dataTerpakai['terpakai'] ?? 0;

        $sisa = $dataKuota['total_kuota'] - $terpakai;

        if ($jumlah > $sisa) {
            echo "<script>alert('Kuota tidak mencukupi! Sisa: $sisa sak');</script>";
        } else {

            $total = $jumlah * $harga;

            mysqli_query($koneksi,"
                INSERT INTO pupuk_subsidi
                (id_pengguna,tanggal_pengajuan,jumlah_pupuk,jenis_pupuk,harga_satuan,total_harga)
                VALUES
                ('$_POST[id_pengguna]',NOW(),'$jumlah','$jenis','$harga','$total')
            ");

            echo "<script>
            alert('Berhasil ditambahkan');
            location='kelola_pupuk.php';
            </script>";
        }
    }
}

// ================= TAMBAH KUOTA =================
if (isset($_POST['tambah_kuota'])) {

    $jenis = mysqli_real_escape_string($koneksi, $_POST['jenis_kuota']);
    $jumlah = (int) $_POST['jumlah_tambah'];

    $cek = mysqli_query($koneksi,"
        SELECT * FROM kuota_pupuk
        WHERE jenis_pupuk='$jenis'
    ");

    if (mysqli_num_rows($cek) > 0) {

        mysqli_query($koneksi,"
            UPDATE kuota_pupuk
            SET total_kuota = total_kuota + $jumlah
            WHERE jenis_pupuk='$jenis'
        ");

    } else {

        mysqli_query($koneksi,"
            INSERT INTO kuota_pupuk (jenis_pupuk, total_kuota)
            VALUES ('$jenis', '$jumlah')
        ");
    }

    echo "<script>
    alert('Kuota berhasil disimpan');
    location='kelola_pupuk.php';
    </script>";
}

// ================= EDIT =================
if (isset($_POST['edit'])) {

    $id     = $_POST['id_pupuk'];
    $jenis  = mysqli_real_escape_string($koneksi, $_POST['jenis_pupuk']);
    $jumlah = (int) $_POST['jumlah_pupuk'];
    $harga  = (int) $_POST['harga_satuan'];
    $total  = $jumlah * $harga;

    // CEK KUOTA
    $qKuota = mysqli_query($koneksi,"
        SELECT total_kuota FROM kuota_pupuk
        WHERE jenis_pupuk='$jenis'
    ");

    if (mysqli_num_rows($qKuota) == 0) {
        echo "<script>alert('Jenis pupuk belum memiliki kuota!');</script>";
    } else {

        $dataKuota = mysqli_fetch_assoc($qKuota);

        $qTerpakai = mysqli_query($koneksi,"
            SELECT SUM(jumlah_pupuk) as terpakai
            FROM pupuk_subsidi
            WHERE jenis_pupuk='$jenis'
            AND id_pupuk != '$id'
        ");

        $dataTerpakai = mysqli_fetch_assoc($qTerpakai);
        $terpakai = $dataTerpakai['terpakai'] ?? 0;

        $sisa = $dataKuota['total_kuota'] - $terpakai;

        if ($jumlah > $sisa) {
            echo "<script>alert('Kuota tidak mencukupi! Sisa: $sisa sak');</script>";
        } else {

            mysqli_query($koneksi,"
                UPDATE pupuk_subsidi
                SET 
                    jenis_pupuk='$jenis',
                    jumlah_pupuk='$jumlah',
                    harga_satuan='$harga',
                    total_harga='$total'
                WHERE id_pupuk='$id'
            ");

            echo "<script>
            alert('Data berhasil diupdate');
            location='kelola_pupuk.php';
            </script>";
        }
    }
}

// ================= HAPUS =================
if (isset($_GET['hapus'])) {

    mysqli_query($koneksi,"
        DELETE FROM pupuk_subsidi
        WHERE id_pupuk='$_GET[hapus]'
    ");

    echo "<script>
    alert('Data dihapus');
    location='kelola_pupuk.php';
    </script>";
}
?>

<?php include 'footer.php'; ?>