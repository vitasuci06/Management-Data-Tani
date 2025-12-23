<?php 
include 'header_pengurus.php';
include 'koneksi.php';

//DATA KPI 
$queryAnggota  = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengguna WHERE jabatan='Anggota'");
$totalAnggota  = mysqli_fetch_assoc($queryAnggota)['total'] ?? 0;

$queryPengurus = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM pengurus");
$totalPengurus = mysqli_fetch_assoc($queryPengurus)['total'] ?? 0;

// KPI BANTUAN ALAT
$queryBantuanAlat = mysqli_query($koneksi, "
    SELECT SUM(jumlah_bantuan) AS total
    FROM bantuan
    WHERE jenis_bantuan = 'Alat'
");
$totalBantuanAlat = mysqli_fetch_assoc($queryBantuanAlat)['total'] ?? 0;

// KPI BANTUAN BIBIT
$queryBantuanBibit = mysqli_query($koneksi, "
    SELECT SUM(jumlah_bantuan) AS total
    FROM bantuan
    WHERE jenis_bantuan = 'Bibit'
");
$totalBantuanBibit = mysqli_fetch_assoc($queryBantuanBibit)['total'] ?? 0;


//  BAGIAN 2: DATA GRAFIK MULTI MUSIM 
$tahun_range = range(2020, 2025);

$config_musim = [
    'musim1' => [
        'bulan' => 'IN (2,3)',
        'label' => 'Musim 1 (Feb–Mar)'
    ],
    'musim2' => [
        'bulan' => 'IN (6,7)',
        'label' => 'Musim 2 (Jun–Jul)'
    ],
    'musim3' => [
        'bulan' => 'IN (10,11)',
        'label' => 'Musim 3 (Okt–Nov)'
    ]
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

        <div class="row mb-20">
            <!-- Anggota -->
            <div class="col-md-6 mb-3">
                <div class="kpi-card">
                    <div class="kpi-icon-purple">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div class="kpi-content">
                        <p>Total Anggota</p>
                        <div class="kpi-row">
                            <h2><?= $totalAnggota ?></h2>
                            <a href="kelola_anggota.php" class="kpi-btn purple">
                                Kelola Data <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengurus -->
            <div class="col-md-6 mb-3">
                <div class="kpi-card">
                    <div class="kpi-icon-green">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="kpi-content">
                        <p>Total Pengurus</p>
                        <div class="kpi-row">
                            <h2><?= $totalPengurus ?></h2>
                            <a href="kelola_pengurus.php" class="kpi-btn green">
                                Kelola Data <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bantuan Bibit -->
            <div class="col-md-6 mb-3">
                <div class="kpi-card">
                    <div class="kpi-icon-yellow">
                        <i class="bi bi-basket-fill"></i>
                    </div>
                    <div class="kpi-content">
                        <p>Total Bantuan Bibit</p>
                        <div class="kpi-row">
                            <h2><?= number_format($totalBantuanBibit, 0, ',', '.') ?> <small>kg</small></h2>
                            <a href="kelola_bantuan.php?jenis=2" class="kpi-btn yellow">
                                Kelola Data <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bantuan Alat -->
            <div class="col-md-6 mb-3">
                <div class="kpi-card">
                    <div class="kpi-icon-orange">
                        <i class="bi bi-tools"></i>
                    </div>
                    <div class="kpi-content">
                        <p>Total Bantuan Alat</p>
                        <div class="kpi-row">
                            <h2><?= number_format($totalBantuanAlat, 0, ',', '.') ?> <small>buah</small></h2>
                            <a href="kelola_bantuan.php?jenis=1" class="kpi-btn orange">
                                Kelola Data <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
            backgroundColor: 'transparent',
            backgroundColor: 'rgba(9, 132, 227, 0.25)',
            fill: true,           
            borderWidth: 3,
            tension: 0.35,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx =>
                        ctx.parsed.y.toLocaleString('id-ID') + ' kg'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => val.toLocaleString('id-ID') + ' kg'
                },
                grid: {
                    color: 'rgba(0,0,0,0.05)'
                }
            }
        }
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

// LOAD DEFAULT
updateChart(document.getElementById('filterTahun').value);

// EVENT DROPDOWN
document.getElementById('filterTahun').addEventListener('change', function () {
    updateChart(this.value);
});
</script>
<?php include 'footer.php'; ?>