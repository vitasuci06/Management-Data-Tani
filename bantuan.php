<?php
include 'koneksi.php';
include 'header.php';
?>

<main class="main">
<div class="container">

<?php
// ================= JUDUL FILTER =================
$jenisbantuan = "Semua Jenis Bantuan";
if (@$_GET['jenis'] == 1) $jenisbantuan = "Bantuan Alat Tani";
elseif (@$_GET['jenis'] == 2) $jenisbantuan = "Bantuan Bibit Pertanian";
?>

<h2 class="main-title">Data <?= $jenisbantuan; ?></h2>
<div class="filter-action filter-responsive mb-3">

    <form method="GET" id="formbantuan" class="filter-form">

        <!-- FILTER JENIS -->
        <div class="filter-item">
            <select name="jenis"
                    class="form-select"
                    onchange="this.form.submit()">
                <option value="">Semua Jenis Bantuan</option>
                <option value="1" <?= (@$_GET['jenis']==1?'selected':''); ?>>
                    Bantuan Alat Tani
                </option>
                <option value="2" <?= (@$_GET['jenis']==2?'selected':''); ?>>
                    Bantuan Bibit Pertanian
                </option>
            </select>
        </div>

        <!-- FILTER TAHUN -->
        <div class="filter-item">
            <select name="tahun"
                    class="form-select"
                    onchange="this.form.submit()">
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

    </form>
</div>


<?php
// ================= DATA KETUA =================
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
</tr>
</thead>
<tbody>

<?php
$no = 1;
$where = " WHERE 1=1 ";

if (isset($_GET['jenis']) && $_GET['jenis'] != "") {
    if ($_GET['jenis'] == 1) {
        $where .= " AND b.jenis_bantuan='Alat'";
    } elseif ($_GET['jenis'] == 2) {
        $where .= " AND b.jenis_bantuan='Bibit'";
    }
}

if (isset($_GET['search']) && $_GET['search'] != "") {
    $search = mysqli_real_escape_string($koneksi, $_GET['search']);
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

</tr>
<?php } } ?>
</tbody>
</table>
</div>

</div>
</main>
<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;
    if (q.trim() === "") {
        window.location.href = "bantuan.php";
    }
}
</script>
<?php include 'footer.php'; ?>
