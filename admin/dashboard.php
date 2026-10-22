<?php
session_start();

if (empty($_SESSION['admin_login'])) {
    header("Location: ../login.php");
    exit;
}
?>

<?php
require_once '../koneksi.php';

// Total titik bahaya yang aktif
$queryTotal = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM titik_rawan
     WHERE status = 'aktif'
     AND latitude IS NOT NULL
     AND longitude IS NOT NULL"
);
$totalTitik = mysqli_fetch_assoc($queryTotal)['total'];

// Titik tikungan tajam yang aktif
$queryTikungan = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM titik_rawan
     WHERE status = 'aktif'
     AND jenis_bahaya = 'Tikungan tajam'
     AND latitude IS NOT NULL
     AND longitude IS NOT NULL"
);
$totalTikungan = mysqli_fetch_assoc($queryTikungan)['total'];

// Titik aktif selain tikungan tajam
$queryLainnya = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM titik_rawan
     WHERE status = 'aktif'
     AND jenis_bahaya != 'Tikungan tajam'
     AND latitude IS NOT NULL
     AND longitude IS NOT NULL"
);
$totalLainnya = mysqli_fetch_assoc($queryLainnya)['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - AMANJALAN</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f3f6f4;
    font-family: 'Segoe UI', sans-serif;
    color: #243b32;
}

.sidebar {
    width: 260px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background: linear-gradient(180deg, #104b37, #176b4b);
    color: white;
    padding: 28px 18px;
    overflow-y: auto;
}

.logo {
    text-align: center;
    padding-bottom: 28px;
    border-bottom: 1px solid #ffffff30;
}

.logo h2 {
    font-weight: 800;
    letter-spacing: 1px;
    margin-top: 12px;
}

.logo p {
    font-size: 12px;
    color: #d3e8dc;
}

.menu-title {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #b5d3c3;
    margin: 28px 14px 12px;
}

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #e7f4ec;
    text-decoration: none;
    padding: 14px 16px;
    margin-bottom: 8px;
    border-radius: 10px;
    transition: .3s;
    font-size: 14px;
}

.sidebar a:hover,
.sidebar a.active {
    background: #ffffff24;
    color: white;
}

.sidebar a i {
    width: 20px;
}

.sidebar .logout {
    color: #ffdddd;
    margin-top: 25px;
}

.main {
    margin-left: 260px;
    padding: 32px 38px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    gap: 15px;
}

.topbar h3 {
    font-weight: 750;
    color: #164d38;
}

.admin-profile {
    background: white;
    padding: 12px 18px;
    border-radius: 12px;
    box-shadow: 0 3px 15px #00000008;
}

.welcome {
    background: linear-gradient(110deg, #15583f, #21815c);
    color: white;
    border-radius: 18px;
    padding: 35px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}

.welcome h2 {
    font-weight: 800;
}

.welcome p {
    color: #e0f3e7;
    max-width: 600px;
    margin-bottom: 0;
}

.stats-card {
    background: white;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 4px 18px #00000008;
    height: 100%;
    border: 1px solid #e8eeea;
}

.stats-card .icon-box {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 22px;
    background: #e5f4ea;
    color: #176b4b;
    margin-bottom: 15px;
}

.stats-card h3 {
    font-weight: 800;
    color: #174e38;
}

.stats-card p {
    color: #78867f;
    font-size: 14px;
    margin-bottom: 0;
}

.section-title {
    margin: 35px 0 20px;
    font-weight: 750;
    color: #164d38;
}

.feature-card {
    background: white;
    border-radius: 16px;
    padding: 28px;
    height: 100%;
    border: 1px solid #e7eee9;
    box-shadow: 0 4px 18px #00000008;
    transition: .3s;
}

.feature-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px #00000012;
}

.feature-card i {
    font-size: 30px;
    color: #18835b;
    margin-bottom: 20px;
}

.feature-card h5 {
    font-weight: 700;
}

.feature-card p {
    color: #718078;
    font-size: 14px;
    min-height: 45px;
}

.btn-green {
    display: inline-block;
    background: #176b4b;
    color: white;
    border-radius: 9px;
    padding: 10px 20px;
    text-decoration: none;
    font-size: 14px;
}

.btn-green:hover {
    background: #104d36;
    color: white;
}

.footer {
    text-align: center;
    margin-top: 50px;
    color: #87948c;
    font-size: 13px;
}

@media (max-width: 768px) {
    .sidebar {
        position: relative;
        width: 100%;
        height: auto;
    }

    .main {
        margin-left: 0;
        padding: 20px;
    }

    .topbar {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="logo">
        <i class="fa-solid fa-shield-halved fa-3x"></i>
        <h2>AMANJALAN</h2>
        <p>Sistem Informasi Jalan Rawan</p>
    </div>

    <div class="menu-title">MENU UTAMA</div>

    <a href="dashboard.php" class="active">
        <i class="fa-solid fa-house"></i>
        Dashboard
    </a>

    <a href="../index.php">
        <i class="fa-solid fa-map-location-dot"></i>
        Lihat Peta
    </a>

    <div class="menu-title">PENGELOLAAN DATA</div>

    <a href="tambah_titik.php">
        <i class="fa-solid fa-location-dot"></i>
        Tambah Titik Bahaya
    </a>

    <a href="kelola_titik.php">
        <i class="fa-solid fa-list-check"></i>
        Kelola Titik Bahaya
    </a>

    <div class="menu-title">PENGATURAN</div>

    <a href="ubah_password.php">
        <i class="fa-solid fa-key"></i>
        Ubah Password
    </a>

    <a href="../logout.php" class="logout"
       onclick="return confirm('Yakin ingin logout?')">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
    </a>

</div>

<!-- MAIN CONTENT -->
<div class="main">

    <div class="topbar">
        <div>
            <h3>Dashboard Admin</h3>
            <p class="text-muted mb-0">
                Sistem Peringatan Jalan Berbahaya
            </p>
        </div>

        <div class="admin-profile">
            <i class="fa-solid fa-circle-user me-2"></i>
            Administrator
        </div>
    </div>

    <!-- WELCOME -->
    <div class="welcome">
        <h2>Selamat Datang, Admin!</h2>
        <p>
            Kelola informasi titik jalan berbahaya,
            pantau lokasi rawan kecelakaan, dan bantu
            meningkatkan keselamatan masyarakat
            melalui sistem AMANJALAN.
        </p>
    </div>

    <!-- STATISTIK -->
    <div class="row g-4">

        <div class="col-md-4">
            <div class="stats-card">
                <div class="icon-box">
                    <i class="fa-solid fa-map-pin"></i>
                </div>
                <h3><?= $totalTitik ?></h3>
                <p>Total Titik Bahaya</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card">
                <div class="icon-box">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h3><?= $totalTikungan ?></h3>
                <p>Lokasi Tikungan Tajam</p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stats-card">
                <div class="icon-box">
                    <i class="fa-solid fa-road"></i>
                </div>
                <h3><?= $totalLainnya ?></h3>
                <p>Lokasi Jalan Berbahaya</p>
            </div>
        </div>

    </div>

    <!-- MENU CEPAT -->
    <h4 class="section-title">Menu Pengelolaan</h4>

    <div class="row g-4">

        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <i class="fa-solid fa-location-dot"></i>
                <h5>Tambah Titik Bahaya</h5>
                <p>
                    Tambahkan lokasi jalan berbahaya
                    beserta koordinat dan keterangannya.
                </p>
                <a href="tambah_titik.php" class="btn-green">
                    Tambah Lokasi
                    <i class="fa-solid fa-arrow-right ms-2"
                       style="font-size:12px;margin:0"></i>
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <i class="fa-solid fa-list-check"></i>
                <h5>Kelola Titik Bahaya</h5>
                <p>
                    Lihat dan kelola informasi lokasi
                    rawan yang tersimpan dalam sistem.
                </p>
                <a href="kelola_titik.php" class="btn-green">
                    Kelola Data
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <i class="fa-solid fa-map-location-dot"></i>
                <h5>Peta Persebaran</h5>
                <p>
                    Tampilkan persebaran lokasi
                    berbahaya melalui peta interaktif.
                </p>
                <a href="../index.php" class="btn-green">
                    Lihat Peta
                </a>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="feature-card">
                <i class="fa-solid fa-key"></i>
                <h5>Ubah Password</h5>
                <p>
                    Perbarui password administrator
                    untuk menjaga keamanan sistem.
                </p>
                <a href="ubah_password.php" class="btn-green">
                    Pengaturan Akun
                </a>
            </div>
        </div>

    </div>

    <div class="footer">
        © <?php echo date('Y'); ?> AMANJALAN
        | Sistem Informasi Keselamatan Jalan
    </div>

</div>

</body>
</html>
