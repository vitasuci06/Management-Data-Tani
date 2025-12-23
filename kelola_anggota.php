<?php
include 'koneksi.php';
include 'header_pengurus.php';
?>

<main class="main">
<div class="container">

<h2 class="main-title">Kelola Data Anggota Kelompok Tani Makmur</h2>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
    + Tambah Anggota
</button>

<div class="table-wrapper">
<table class="table table-bordered table-striped">
    <thead class="table-hijau">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Nomor Kartu Tani</th>
            <th width="180px">Aksi</th>
        </tr>
    </thead>

    <tbody>
    <?php

    // PAGINATION SETUP
    $limit  = 10;
    $page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $page   = ($page < 1) ? 1 : $page;
    $offset = ($page - 1) * $limit;
    $no     = $offset + 1;

    // FILTER SEARCH

    $search_where = "WHERE jabatan = 'Anggota'";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $searchTerm = mysqli_real_escape_string($koneksi, $_GET['search']);
        $search_where .= " AND (
            nama_pengguna LIKE '%$searchTerm%' OR
            nik_pengguna LIKE '%$searchTerm%' OR
            no_kartutani LIKE '%$searchTerm%'
        )";
    }

    // HITUNG TOTAL DATA
    $countQuery = "
        SELECT COUNT(*) as total
        FROM pengguna
        $search_where
    ";
    $countResult = mysqli_fetch_assoc(mysqli_query($koneksi, $countQuery));
    $totalData = $countResult['total'];
    $totalPage = ceil($totalData / $limit);

    // QUERY DATA
    $result = mysqli_query($koneksi, "
        SELECT * FROM pengguna
        $search_where
        ORDER BY id_pengguna DESC
        LIMIT $limit OFFSET $offset
    ");

    if (mysqli_num_rows($result) == 0) {
        echo '<tr><td colspan="5" class="text-center">Data anggota tidak ditemukan</td></tr>';
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
    ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
            <td><?= htmlspecialchars($row['nik_pengguna']); ?></td>
            <td><?= $row['no_kartutani'] ? htmlspecialchars($row['no_kartutani']) : 'N/A'; ?></td>
            <td>
                <button class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#modalEdit<?= $row['id_pengguna']; ?>">
                    Edit
                </button>

                <a href="kelola_anggota.php?hapus=<?= $row['id_pengguna']; ?>"
                   onclick="return confirm('Hapus anggota ini?')"
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
    <td colspan="5">
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

                <?php if ($page > 1) { ?>
                    <a class="btn btn-outline-secondary btn-sm"
                       href="?<?= $queryString; ?>&page=<?= $page - 1; ?>">
                        « Prev
                    </a>
                <?php } ?>

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

<!--MODAL TAMBAH-->
<div class="modal fade" id="modalTambah">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Tambah Anggota</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <label>Nama</label>
    <input type="text" name="nama_pengguna" class="form-control" required>

    <label class="mt-2">NIK</label>
    <input type="text" name="nik_pengguna" class="form-control" required>

    <label class="mt-2">Nomor Kartu Tani</label>
    <input type="text" name="no_kartutani" class="form-control">
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

// ACTION TAMBAH
if (isset($_POST['tambah'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $nik  = mysqli_real_escape_string($koneksi, $_POST['nik_pengguna']);
    $no_kartu = mysqli_real_escape_string($koneksi, $_POST['no_kartutani']);
    $jabatan = "Anggota";

    mysqli_query($koneksi, "
        INSERT INTO pengguna (nama_pengguna, nik_pengguna, no_kartutani, jabatan)
        VALUES ('$nama','$nik','$no_kartu','$jabatan')
    ");

    echo "<script>location.href='kelola_anggota.php';</script>";
}
?>

<!--MODAL EDIT -->
<?php
$edit = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE jabatan = 'Anggota'");
while ($rowEdit = mysqli_fetch_assoc($edit)) {
?>
<div class="modal fade" id="modalEdit<?= $rowEdit['id_pengguna']; ?>">
<div class="modal-dialog">
<div class="modal-content">
<form method="POST">

<div class="modal-header">
    <h5 class="modal-title">Edit Anggota</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    <input type="hidden" name="id_pengguna" value="<?= $rowEdit['id_pengguna']; ?>">

    <label>Nama</label>
    <input type="text" name="nama_pengguna" value="<?= htmlspecialchars($rowEdit['nama_pengguna']); ?>" class="form-control" required>

    <label class="mt-2">NIK</label>
    <input type="text" name="nik_pengguna" value="<?= htmlspecialchars($rowEdit['nik_pengguna']); ?>" class="form-control" required>

    <label class="mt-2">Nomor Kartu Tani</label>
    <input type="text" name="no_kartutani" value="<?= htmlspecialchars($rowEdit['no_kartutani']); ?>" class="form-control">
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

// ACTION UPDATE

if (isset($_POST['update'])) {
    $id   = mysqli_real_escape_string($koneksi, $_POST['id_pengguna']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $nik  = mysqli_real_escape_string($koneksi, $_POST['nik_pengguna']);
    $no_kartu = mysqli_real_escape_string($koneksi, $_POST['no_kartutani']);

    mysqli_query($koneksi, "
        UPDATE pengguna SET
        nama_pengguna='$nama',
        nik_pengguna='$nik',
        no_kartutani='$no_kartu'
        WHERE id_pengguna='$id'
    ");

    echo "<script>location.href='kelola_anggota.php';</script>";
}
?>

<?php

// ACTION HAPUS
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);
    mysqli_query($koneksi, "DELETE FROM pengguna WHERE id_pengguna='$id'");
    echo "<script>location.href='kelola_anggota.php';</script>";
}
?>

</div>
</main>

<script>
function autoResetSearch() {
    let q = document.getElementById("search").value;
    if (q.trim() === "") {
        window.location.href = "kelola_anggota.php";
    }
}
</script>

<?php include 'footer.php'; ?>
