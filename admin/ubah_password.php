
<?php
session_start();

if (empty($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}

require_once '../koneksi.php';

$pesan = '';
$jenisPesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passwordLama = $_POST['password_lama'] ?? '';
    $passwordBaru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if ($passwordBaru === '' || $passwordLama === '' || $konfirmasi === '') {
        $pesan = 'Semua kolom wajib diisi.';
        $jenisPesan = 'danger';
    } elseif ($passwordBaru !== $konfirmasi) {
        $pesan = 'Konfirmasi password baru tidak cocok.';
        $jenisPesan = 'danger';
    } elseif (strlen($passwordBaru) < 8) {
        $pesan = 'Password baru minimal 8 karakter.';
        $jenisPesan = 'danger';
    } else {
        $adminId = $_SESSION['admin_id'] ?? 0;

        $stmt = mysqli_prepare(
            $conn,
            "SELECT password FROM admin WHERE id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, 'i', $adminId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $admin = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$admin || !password_verify($passwordLama, $admin['password'])) {
            $pesan = 'Password lama tidak sesuai.';
            $jenisPesan = 'danger';
        } else {
            $hashBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE admin SET password = ? WHERE id = ?"
            );
            mysqli_stmt_bind_param($stmt, 'si', $hashBaru, $adminId);

            if (mysqli_stmt_execute($stmt)) {
                $pesan = 'Password berhasil diubah.';
                $jenisPesan = 'success';
            } else {
                $pesan = 'Password gagal diubah. Silakan coba lagi.';
                $jenisPesan = 'danger';
            }

            mysqli_stmt_close($stmt);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ubah Password - AMANJALAN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body {
    margin: 0;
    padding: 30px 15px;
    background: #f3f6f4;
    font-family: 'Segoe UI', sans-serif;
    color: #243b32;
}
.card-password {
    max-width: 520px;
    margin: 35px auto;
    padding: 30px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 5px 20px #0000000d;
}
h2 { color: #176b4b; font-weight: 750; }
.form-label { font-weight: 600; }
.btn-green {
    background: #176b4b;
    color: white;
    border: none;
    padding: 11px 18px;
    border-radius: 8px;
}
.btn-green:hover { background: #104d36; color: white; }
.back-link { color: #176b4b; text-decoration: none; }
</style>
</head>
<body>
<div class="card-password">
    <h2>Ubah Password Admin</h2>
    <p class="text-muted">Perbarui password akun administrator AMANJALAN.</p>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-<?= htmlspecialchars($jenisPesan) ?>">
            <?= htmlspecialchars($pesan) ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label class="form-label" for="password_lama">Password Lama</label>
            <input class="form-control" type="password" id="password_lama"
                   name="password_lama" required>
        </div>

        <div class="mb-3">
            <label class="form-label" for="password_baru">Password Baru</label>
            <input class="form-control" type="password" id="password_baru"
                   name="password_baru" minlength="8" required>
        </div>

        <div class="mb-4">
            <label class="form-label" for="konfirmasi_password">Konfirmasi Password Baru</label>
            <input class="form-control" type="password" id="konfirmasi_password"
                   name="konfirmasi_password" minlength="8" required>
        </div>

        <button type="submit" class="btn btn-green w-100">
            Simpan Password Baru
        </button>
    </form>

    <p class="mt-4 mb-0">
        <a class="back-link" href="dashboard.php">← Kembali ke Dashboard</a>
    </p>
</div>
</body>
</html>
