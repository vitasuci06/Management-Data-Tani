<?php
session_start();
include 'koneksi.php';

// Jika sudah login, arahkan ke dashboard pengurus
if (isset($_SESSION['login'])) {
    header("Location: index_pengurus.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    $query = mysqli_query($koneksi, "
        SELECT p.*, pg.jabatan, pg.nama_pengguna
        FROM pengurus p
        JOIN pengguna pg ON p.id_pengguna = pg.id_pengguna
        WHERE p.username = '$username'
          AND p.password = '$password'
          AND pg.jabatan != 'Anggota'
        LIMIT 1
    ");

    if (mysqli_num_rows($query) === 1) {
        $data = mysqli_fetch_assoc($query);

        $_SESSION['login']         = true;
        $_SESSION['id_pengurus']   = $data['id_pengurus'];
        $_SESSION['username']      = $data['username'];
        $_SESSION['nama_pengguna'] = $data['nama_pengguna'];
        $_SESSION['jabatan']       = $data['jabatan'];

        header("Location: index_pengurus.php");
        exit;
    } else {
        $error = "Username atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | Kelompok Tani Makmur</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        .input-group-text {
            cursor: pointer;
        }
    </style>
</head>

<body class="bg-light d-flex align-items-center" style="min-height:100vh">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-3">Login Pengurus</h4>

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control"
                                       required>

                                <span class="input-group-text" onclick="togglePassword()">
                                    <i id="eyeIcon" class="bi bi-eye-slash"></i>
                                </span>
                            </div>
                        </div>

                        <button type="submit" name="login" class="btn btn-success w-100">
                            Login
                        </button>

                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="history.back()">
                            Kembali
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center mt-3 text-muted">© Kelompok Tani Makmur</p>
        </div>
    </div>
</div>

<!-- SCRIPT TOGGLE PASSWORD -->
<script>
function togglePassword() {
    const password = document.getElementById("password");
    const icon = document.getElementById("eyeIcon");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("bi-eye-slash");
        icon.classList.add("bi-eye");
    } else {
        password.type = "password";
        icon.classList.remove("bi-eye");
        icon.classList.add("bi-eye-slash");
    }
}
</script>

</body>
</html>
