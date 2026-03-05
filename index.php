<?php 
include 'header.php'; 
include 'koneksi.php';

// KPI USER

$queryAnggota  = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna WHERE jabatan='Anggota'");
$totalAnggota  = mysqli_fetch_assoc($queryAnggota)['total'] ?? 0;

$queryPengurus = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengurus");
$totalPengurus = mysqli_fetch_assoc($queryPengurus)['total'] ?? 0;

// DATA PUPUK

// Total pupuk digunakan
$queryTotalPupuk = mysqli_query($koneksi,"
    SELECT SUM(jumlah_pupuk) AS total
    FROM pupuk_subsidi
");
$totalPupuk = mysqli_fetch_assoc($queryTotalPupuk)['total'] ?? 0;


// Ambil SEMUA jenis pupuk dari kuota_pupuk
$queryJenis = mysqli_query($koneksi,"
    SELECT 
        k.jenis_pupuk,
        COALESCE(SUM(p.jumlah_pupuk),0) AS total
    FROM kuota_pupuk k
    LEFT JOIN pupuk_subsidi p 
        ON k.jenis_pupuk = p.jenis_pupuk
    GROUP BY k.jenis_pupuk
");

$dataPie = [];
$jenisTerbanyak = "-";
$totalTerbanyak = 0;

while ($row = mysqli_fetch_assoc($queryJenis)) {

    $dataPie[] = $row;

    if ($row['total'] > $totalTerbanyak) {
        $jenisTerbanyak = $row['jenis_pupuk'];
        $totalTerbanyak = $row['total'];
    }
}

// DATA GRAFIK PANEN

$tahun_range = range(2020, 2025);

$config_musim = [
    'musim1' => ['bulan' => 'IN (2,3)'],
    'musim2' => ['bulan' => 'IN (6,7)'],
    'musim3' => ['bulan' => 'IN (10,11)']
];

$data_chart = [];

foreach ($tahun_range as $tahun) {
    foreach ($config_musim as $key => $conf) {

        $sql = "
            SELECT SUM(jumlah_panen) AS total
            FROM hasil_panen
            WHERE YEAR(periode_panen) = $tahun
              AND MONTH(periode_panen) {$conf['bulan']}
        ";

        $res = mysqli_query($koneksi, $sql);
        $row = mysqli_fetch_assoc($res);

        $data_chart[$tahun][$key] = (float)($row['total'] ?? 0);
    }
}

?>

<main class="main users chart-page">
<div class="container">

<h2 class="main-title">Dashboard Analisis Produksi</h2>

<div class="row mb-4">

<!-- PIE CHART -->
<div class="col-md-6">

<h5 class="mb-3">Distribusi Penggunaan Pupuk</h5>

<div style="height:300px; max-width:450px;">
<canvas id="pieChart"></canvas>
</div>

</div>

<!--KPI PUPUK -->
<div class="col-md-6">

<!-- TOTAL PUPUK -->
<div class="kpi-card mb-3">

<div class="kpi-icon-green">
<i class="bi bi-flower1"></i>
</div>

<div class="kpi-content">
<p>Total Pupuk Digunakan</p>

<div class="kpi-row">
<h2><?= number_format($totalPupuk,0,',','.') ?> sak</h2>
</div>

</div>
</div>

<!-- PUPUK TERBANYAK -->
<div class="kpi-card mb-3">

<div class="kpi-icon-yellow">
<i class="bi bi-bar-chart-fill"></i>
</div>

<div class="kpi-content">
<p>Pupuk Paling Sering Digunakan</p>

<div class="kpi-row">
<h2><?= $jenisTerbanyak ?> : 
<?= number_format($totalTerbanyak,0,',','.') ?> sak</h2>

</div>

</div>
</div>

<!-- TOTAL PER JENIS -->
<div class="kpi-card">

<div class="kpi-icon-purple">
<i class="bi bi-list-ul"></i>
</div>

<div class="kpi-content">

<p class="mb-3">Total Penggunaan per Jenis</p>

<div class="row">

<?php
$half = ceil(count($dataPie)/2);
$left = array_slice($dataPie,0,$half);
$right = array_slice($dataPie,$half);
?>

<!-- KOLOM KIRI -->
<div class="col-6">

<?php foreach($left as $d): ?>

<div class="d-flex justify-content-between mb-1">

<span><?= $d['jenis_pupuk'] ?></span>

<strong><?= number_format($d['total'],0,',','.') ?> kg</strong>

</div>

<?php endforeach; ?>

</div>

<!-- KOLOM KANAN -->
<div class="col-6">

<?php foreach($right as $d): ?>

<div class="d-flex justify-content-between mb-1">

<span><?= $d['jenis_pupuk'] ?></span>

<strong><?= number_format($d['total'],0,',','.') ?> kg</strong>

</div>

<?php endforeach; ?>

</div>

</div>
</div>
</div>
</div>

<!-- CHART PANEN -->

<div class="white-block p-20 chart-box">

<div class="chart-header mb-3">

<h3 class="chart-title">Perbandingan Produksi per Musim Panen</h3>

<select id="filterTahun" class="form-select filter-tahun">

<?php foreach ($tahun_range as $th): ?>

<option value="<?= $th ?>" <?= $th == 2025 ? 'selected' : '' ?>>

Tahun <?= $th ?>

</option>

<?php endforeach; ?>

</select>

</div>

<div style="height:400px;">
<canvas id="hasilPanenChart"></canvas>
</div>

</div>

</div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

// PIE CHART

const pieData = <?= json_encode($dataPie) ?>;

const pieLabels = pieData.map(item => item.jenis_pupuk);
const pieValues = pieData.map(item => item.total);

new Chart(document.getElementById('pieChart'), {

type: 'pie',

data: {

labels: pieLabels,

datasets: [{

data: pieValues,

backgroundColor: [

'#27ae60',
'#f1c40f',
'#2980b9',
'#e74c3c',
'#8e44ad',
'#16a085'

]

}]

},

options: {

responsive:true,

maintainAspectRatio:false,

plugins: {

legend: {

position: 'top'

}

}

}

});

// LINE CHART PANEN

const dataChart = <?= json_encode($data_chart) ?>;

const ctx = document.getElementById('hasilPanenChart');

let chartPanen = new Chart(ctx, {

type: 'line',

data: {

labels: ['Musim 1 (Feb–Mar)', 'Musim 2 (Jun–Jul)', 'Musim 3 (Okt–Nov)'],

datasets: [{

label: 'Total Produksi',

data: [],

borderColor: '#0984e3',

backgroundColor: 'rgba(9, 132, 227, 0.25)',

fill: true,

borderWidth: 3,

tension: 0.35

}]

},

options: {

responsive: true,

maintainAspectRatio: false

}

});

function updateChart(tahun) {

chartPanen.data.datasets[0].data = [

dataChart[tahun].musim1,
dataChart[tahun].musim2,
dataChart[tahun].musim3

];

chartPanen.update();

}

updateChart(document.getElementById('filterTahun').value);

document.getElementById('filterTahun')
.addEventListener('change', function () {

updateChart(this.value);

});

</script>

<?php include 'footer.php'; ?>