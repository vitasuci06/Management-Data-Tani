<?php
include 'koneksi.php';
include 'header_pengurus.php';

$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$limit = 10; 
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page  = ($page < 1) ? 1 : $page;
$offset = ($page - 1) * $limit;
$conditions = [];
?>

<main class="main">
<div class="container laporan-cetak">
    <h2 class="text-center laporan-title">LAPORAN TAHUNAN KELOMPOK TANI</h2>
    <p class="text-center laporan-subtitle">Tahun <?= $tahun; ?></p>
    <div class="filter-responsive no-print mb-4 justify-content-center">
        <form method="GET" class="filter-form">

            <div class="filter-item d-flex gap-2">
                <select name="tahun"
                    class="form-select"
                    onchange="this.form.submit()">
                    <?php
                    for ($i = date('Y'); $i >= 2020; $i--) {
                        $sel = ($i == $tahun) ? 'selected' : '';
                        echo "<option value='$i' $sel>$i</option>";
                    }
                    ?>
                </select>

                <!-- TOMBOL CETAK -->
                <button type="button"
                    onclick="window.print()"
                    class="btn btn-primary filter-cetak">
                    🖨️ Cetak PDF
                </button>
            </div>

        </form>
    </div>

<!-- bantuan -->
    <h4 class="section-title">A. Data Bantuan (Alat & Bibit)</h4>

    <div class="laporan-responsive">
    <table class="table-laporan">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama Petani</th>
                <th>Jenis Bantuan</th>
                <th>Nama Bantuan</th>
                <th>Jumlah</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        $qBantuan = mysqli_query($koneksi, "
            SELECT 
                b.tanggal_diberikan,
                u.nama_pengguna,
                b.jenis_bantuan,
                b.nama_bantuan,
                b.jumlah_bantuan
            FROM bantuan b
            JOIN pengguna u ON b.id_pengguna = u.id_pengguna
            WHERE YEAR(b.tanggal_diberikan) = '$tahun'
            ORDER BY b.tanggal_diberikan ASC
        ");

        if (mysqli_num_rows($qBantuan) > 0) {
            while ($b = mysqli_fetch_assoc($qBantuan)) {
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= date('d-m-Y', strtotime($b['tanggal_diberikan'])); ?></td>
                <td><?= $b['nama_pengguna']; ?></td>
                <td><?= $b['jenis_bantuan']; ?></td>
                <td><?= $b['nama_bantuan']; ?></td>
                <td><?= $b['jumlah_bantuan']; ?></td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='6' align='center'>Tidak ada data</td></tr>";
        }
        ?>
        </tbody>
    </table>
    </div>

<!-- pupuk -->
    <h4 class="section-title">B. Data Pupuk Subsidi</h4>

    <div class="laporan-responsive">
    <table class="table-laporan">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Pengajuan</th>
                <th>Nama Petani</th>
                <th>No Kartu Tani</th>
                <th>Jumlah (Kg)</th>
                <th>Harga Satuan</th>
                <th>Total Harga</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        $qPupuk = mysqli_query($koneksi, "
            SELECT 
                p.tanggal_pengajuan,
                u.nama_pengguna,
                u.no_kartutani,
                p.jumlah_pupuk,
                p.harga_satuan,
                p.total_harga
            FROM pupuk_subsidi p
            JOIN pengguna u ON p.id_pengguna = u.id_pengguna
            WHERE YEAR(p.tanggal_pengajuan) = '$tahun'
            ORDER BY p.tanggal_pengajuan ASC
        ");

        if (mysqli_num_rows($qPupuk) > 0) {
            while ($p = mysqli_fetch_assoc($qPupuk)) {
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= date('d-m-Y', strtotime($p['tanggal_pengajuan'])); ?></td>
                <td><?= $p['nama_pengguna']; ?></td>
                <td><?= $p['no_kartutani']; ?></td>
                <td><?= $p['jumlah_pupuk']; ?></td>
                <td>Rp <?= number_format($p['harga_satuan']); ?></td>
                <td>Rp <?= number_format($p['total_harga']); ?></td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='7' align='center'>Tidak ada data</td></tr>";
        }
        ?>
        </tbody>
    </table>
    </div>
<!-- panen -->
    <h4 class="section-title">C. Data Hasil Panen</h4>

    <div class="laporan-responsive">
    <table class="table-laporan">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Petani</th>
                <th>Jenis Tanaman</th>
                <th>Luas Lahan (Ha)</th>
                <th>Periode Tanam</th>
                <th>Periode Panen</th>
                <th>Jumlah Panen (Kg)</th>
                <th>Jumlah Pupuk (Kg)</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $no = 1;
        $qPanen = mysqli_query($koneksi, "
            SELECT 
                u.nama_pengguna,
                h.jenis_tanaman,
                h.luas_lahan,
                h.periode_tanam,
                h.periode_panen,
                h.jumlah_panen,
                h.jumlah_pupuk
            FROM hasil_panen h
            JOIN pengguna u ON h.id_pengguna = u.id_pengguna
            WHERE YEAR(h.periode_panen) = '$tahun'
            ORDER BY h.periode_panen ASC
        ");

        if (mysqli_num_rows($qPanen) > 0) {
            while ($h = mysqli_fetch_assoc($qPanen)) {
        ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $h['nama_pengguna']; ?></td>
                <td><?= $h['jenis_tanaman']; ?></td>
                <td><?= $h['luas_lahan']; ?></td>
                <td><?= date('m-Y', strtotime($h['periode_tanam'])); ?></td>
                <td><?= date('m-Y', strtotime($h['periode_panen'])); ?></td>
                <td><?= $h['jumlah_panen']; ?></td>
                <td><?= $h['jumlah_pupuk']; ?></td>
            </tr>
        <?php
            }
        } else {
            echo "<tr><td colspan='8' align='center'>Tidak ada data</td></tr>";
        }
        ?>
        </tbody>
    </table>
    </div>
</div>
</main>

<?php include 'footer.php'; ?>
