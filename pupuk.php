<?php
include 'koneksi.php';
include 'header.php';
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
</tr>
<?php }} ?>
</tbody>
</table>
</div>

</div>
</main>

<?php include 'footer.php'; ?>