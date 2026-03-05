<?php
include 'koneksi.php';
include 'header_pengurus.php';
?>

<main class="main">
<div class="container">

<h2 class="main-title">Kelola Data Pengurus Kelompok Tani Makmur</h2>

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalTambah">
    + Tambah Pengurus
</button>
<div class="table-wrapper">
<table class="table table-bordered table-striped">
    <thead class="table-hijau">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Username</th>
            <th>Password</th>
            <th>Jabatan</th>
            <th>Nomor Kartu Tani</th>
            <th width="180px">Aksi</th>
        </tr>
    </thead>

    <tbody>
    <?php 
    $no = 1;
    // --- START LOGIKA SEARCH ---
$search_where = "WHERE p.jabatan != 'Anggota'";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($koneksi, $_GET['search']);

    $search_where .= " AND (
        p.nama_pengguna LIKE '%$searchTerm%' OR 
        p.nik_pengguna LIKE '%$searchTerm%' OR 
        p.no_kartutani LIKE '%$searchTerm%' OR
        pg.username LIKE '%$searchTerm%'
    )";
}
// --- END LOGIKA SEARCH ---


// QUERY UTAMA
$query = "
    SELECT 
        pg.id_pengurus, pg.username, pg.password,
        p.id_pengguna, p.nama_pengguna, 
        p.nik_pengguna, p.no_kartutani, p.jabatan
    FROM pengurus pg
    INNER JOIN pengguna p ON pg.id_pengguna = p.id_pengguna
    $search_where
    ORDER BY p.nama_pengguna ASC
";

$result = mysqli_query($koneksi, $query);

    while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['nama_pengguna']); ?></td>
            <td><?= htmlspecialchars($row['nik_pengguna']); ?></td>
            <td><?= htmlspecialchars($row['username']); ?></td>

            <td>
                <div class="pw-wrapper">
                    <span id="pwText<?= $row['id_pengurus']; ?>" style="letter-spacing:3px;">
                        ••••••
                    </span>

                    <span id="pwValue<?= $row['id_pengurus']; ?>" style="display:none;">
                        <?= htmlspecialchars($row['password']); ?>
                    </span>

                    <i class="bi bi-eye-slash" 
                    id="icon-table<?= $row['id_pengurus']; ?>" 
                    onclick="toggleTablePassword('<?= $row['id_pengurus']; ?>')"></i>
                </div>
            </td>

            <td><?= htmlspecialchars($row['jabatan']); ?></td>
            <td><?= $row['no_kartutani'] ? htmlspecialchars($row['no_kartutani']) : 'N/A'; ?></td>

            <td>
                <button class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#modalEdit<?= $row['id_pengurus']; ?>">
                    Edit
                </button>

                <a href="kelola_pengurus.php?hapus=<?= $row['id_pengurus']; ?>" 
                onclick="return confirm('Hapus pengurus ini? Data pengguna juga akan dihapus!')"
                class="btn btn-danger btn-sm">
                    Hapus
                </a>
            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>
</div>

<!-- form tambah -->
<div class="modal fade" id="modalTambah">
<div class="modal-dialog">
<div class="modal-content">
    <form method="POST">
        <div class="modal-header">
            <h5 class="modal-title">Tambah Pengurus</h5>
        </div>

        <div class="modal-body">

            <label>Nama</label>
            <input type="text" name="nama_pengguna" class="form-control" required>

            <label class="mt-2">NIK</label>
            <input type="text" name="nik_pengguna" class="form-control" required>

            <label class="mt-2">Username</label>
            <input type="text" name="username" class="form-control" required>

            <label class="mt-2">Password</label>
            <div class="input-group">
                <input type="password" name="password" id="passwordTambah" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('passwordTambah')">
                    <i class="bi bi-eye-slash" id="icon-passwordTambah"></i>
                </span>
            </div>
           <label class="mt-2">Jabatan</label>
            <select name="jabatan" class="form-select" required>
                <option value="">- Pilih Jabatan -</option>
                <option value="ketua">Ketua</option>
                <option value="wakil ketua">Wakil Ketua</option>
                <option value="sekretaris">Sekretaris</option>
                <option value="bendahara">Bendahara</option>
            </select>


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
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik_pengguna']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $no_kartu = mysqli_real_escape_string($koneksi, $_POST['no_kartutani']);

    mysqli_query($koneksi, "INSERT INTO pengguna (nama_pengguna, nik_pengguna, no_kartutani, jabatan)
                            VALUES ('$nama','$nik','$no_kartu','$jabatan')");

    $id_pengguna_baru = mysqli_insert_id($koneksi);

    mysqli_query($koneksi, "INSERT INTO pengurus (id_pengguna, username, password)
                            VALUES ('$id_pengguna_baru', '$username', '$password')");

    echo "<script>location.href='kelola_pengurus.php';</script>";
}
?>

<!-- form edit -->
<?php 
$edit = mysqli_query($koneksi, 
    "SELECT pg.id_pengurus, pg.username, pg.password,
            p.id_pengguna, p.nama_pengguna, p.nik_pengguna, p.no_kartutani, p.jabatan
     FROM pengurus pg
     INNER JOIN pengguna p ON pg.id_pengguna = p.id_pengguna
     WHERE p.jabatan != 'Anggota'"
);

while ($rowEdit = mysqli_fetch_assoc($edit)) { ?>

<div class="modal fade" id="modalEdit<?= $rowEdit['id_pengurus']; ?>">
<div class="modal-dialog">
<div class="modal-content">

    <form method="POST">
        <div class="modal-header">
            <h5 class="modal-title">Edit Pengurus</h5>
        </div>

        <div class="modal-body">

            <input type="hidden" name="id_pengurus" value="<?= $rowEdit['id_pengurus']; ?>">
            <input type="hidden" name="id_pengguna" value="<?= $rowEdit['id_pengguna']; ?>">

            <label>Nama</label>
            <input type="text" name="nama_pengguna" value="<?= htmlspecialchars($rowEdit['nama_pengguna']); ?>" class="form-control" required>

            <label class="mt-2">NIK</label>
            <input type="text" name="nik_pengguna" value="<?= htmlspecialchars($rowEdit['nik_pengguna']); ?>" class="form-control" required>

            <label class="mt-2">Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($rowEdit['username']); ?>" class="form-control" required>

            <label class="mt-2">Password</label>
            <div class="input-group">
                <input type="password" name="password" 
                       id="passwordEdit<?= $rowEdit['id_pengurus']; ?>" 
                       value="<?= htmlspecialchars($rowEdit['password']); ?>" 
                       class="form-control" required>

                <span class="input-group-text"
                    onclick="togglePassword('passwordEdit<?= $rowEdit['id_pengurus']; ?>')">
                    <i class="bi bi-eye-slash" id="icon-passwordEdit<?= $rowEdit['id_pengurus']; ?>"></i>
                </span>
            </div>

            <label class="mt-2">Jabatan</label>
            <select name="jabatan" class="form-select" required>
                <option value="ketua" <?= $rowEdit['jabatan']=='ketua'?'selected':''; ?>>Ketua</option>
                <option value="wakil ketua" <?= $rowEdit['jabatan']=='wakil ketua'?'selected':''; ?>>Wakil Ketua</option>
                <option value="sekretaris" <?= $rowEdit['jabatan']=='sekretaris'?'selected':''; ?>>Sekretaris</option>
                <option value="bendahara" <?= $rowEdit['jabatan']=='bendahara'?'selected':''; ?>>Bendahara</option>
            </select>


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

    $id_pengurus  = mysqli_real_escape_string($koneksi, $_POST['id_pengurus']);
    $id_pengguna = mysqli_real_escape_string($koneksi, $_POST['id_pengguna']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pengguna']);
    $nik = mysqli_real_escape_string($koneksi, $_POST['nik_pengguna']);
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);
    $jabatan = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $no_kartu = mysqli_real_escape_string($koneksi, $_POST['no_kartutani']);

    mysqli_query($koneksi, "UPDATE pengguna SET 
                    nama_pengguna='$nama',
                    nik_pengguna='$nik',
                    no_kartutani='$no_kartu',
                    jabatan='$jabatan'
                    WHERE id_pengguna='$id_pengguna'");

    mysqli_query($koneksi, "UPDATE pengurus SET 
                    username='$username',
                    password='$password'
                    WHERE id_pengurus='$id_pengurus'");

    echo "<script>location.href='kelola_pengurus.php';</script>";
}
?>

<?php
// ACTION HAPUS
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($koneksi, $_GET['hapus']);

    $q = mysqli_query($koneksi, "SELECT id_pengguna FROM pengurus WHERE id_pengurus='$id'");
    $d = mysqli_fetch_assoc($q);
    $id_pengguna = $d['id_pengguna'];

    mysqli_query($koneksi, "DELETE FROM pengurus WHERE id_pengurus='$id'");
    mysqli_query($koneksi, "DELETE FROM pengguna WHERE id_pengguna='$id_pengguna'");

    echo "<script>location.href='kelola_pengurus.php';</script>";
}
?>

</div>
</main>

<script>
    function toggleTablePassword(id) {
        let pwText = document.getElementById("pwText" + id);      // titik-titik
        let pwReal = document.getElementById("pwValue" + id);     // isi password
        let icon = document.getElementById("icon-table" + id);

        if (pwReal.style.display === "none") {
            pwReal.style.display = "inline";
            pwText.style.display = "none";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        } else {
            pwReal.style.display = "none";
            pwText.style.display = "inline";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        }
    }

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById("icon-" + inputId);

        if (!input || !icon) return;

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("bi-eye-slash");
            icon.classList.add("bi-eye");
        } else {
            input.type = "password";
            icon.classList.remove("bi-eye");
            icon.classList.add("bi-eye-slash");
        }
    }

    function autoResetSearch() {
        let q = document.getElementById("search").value;
        if (q.trim() === "") {
            window.location.href = "kelola_pengurus.php";
        }
    }
</script>

<?php include 'footer.php'; ?>
