<?php
require_once 'koneksi.php';

$query = mysqli_query(
    $conn,
    "SELECT * FROM titik_rawan WHERE status = 'aktif'"
);

if (!$query) {
    die("Gagal mengambil data titik rawan.");
}

$titikRawan = [];
$jumlahTikungan = 0;
$jumlahLainnya = 0;

while ($row = mysqli_fetch_assoc($query)) {
    if (
        $row['latitude'] !== null &&
        $row['longitude'] !== null &&
        is_numeric($row['latitude']) &&
        is_numeric($row['longitude']) &&
        $row['latitude'] >= -90 &&
        $row['latitude'] <= 90 &&
        $row['longitude'] >= -180 &&
        $row['longitude'] <= 180
    ) {
        $titikRawan[] = $row;

        if (stripos($row['jenis_bahaya'], 'tikungan') !== false) {
            $jumlahTikungan++;
        } else {
            $jumlahLainnya++;
        }
    }
}

$totalTitik = count($titikRawan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>AMANJALAN | Sistem Informasi Keselamatan Jalan</title>

<link rel="stylesheet"
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<link rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<style>
:root {
    --primary: #145c43;
    --secondary: #21815c;
    --light: #eef6f1;
    --dark: #173e30;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: 'Segoe UI', Arial, sans-serif;
    background: #f4f7f5;
    color: #253d32;
}

/* NAVBAR */
.navbar-custom {
    background: white;
    box-shadow: 0 2px 15px rgba(0,0,0,.06);
    padding: 15px 0;
}

.brand-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.brand-icon {
    width: 48px;
    height: 48px;
    background: var(--primary);
    color: white;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 23px;
}

.brand-title {
    font-size: 22px;
    font-weight: 800;
    color: var(--primary);
    letter-spacing: .5px;
}

.brand-subtitle {
    font-size: 11px;
    color: #71877b;
}

.nav-link {
    font-weight: 600;
    color: #52675b;
    margin: 0 9px;
}

.nav-link:hover {
    color: var(--secondary);
}

.btn-admin {
    background: var(--primary);
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
}

.btn-admin:hover {
    background: #0e4934;
    color: white;
}

/* HERO */
.hero {
    background: linear-gradient(115deg, #104a36, #21815c);
    color: white;
    padding: 75px 0 85px;
    position: relative;
    overflow: hidden;
}

.hero::after {
    content: "";
    position: absolute;
    width: 350px;
    height: 350px;
    border: 65px solid rgba(255,255,255,.04);
    border-radius: 50%;
    right: -100px;
    top: -100px;
}

.hero-badge {
    display: inline-block;
    background: rgba(255,255,255,.13);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 30px;
    padding: 8px 16px;
    font-size: 13px;
    margin-bottom: 22px;
}

.hero h1 {
    font-size: clamp(30px, 4vw, 48px);
    font-weight: 800;
    line-height: 1.25;
    max-width: 750px;
}

.hero p {
    color: #dcefe4;
    font-size: 16px;
    max-width: 650px;
    margin-top: 20px;
    line-height: 1.8;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 28px;
}

.btn-hero {
    background: white;
    color: var(--primary);
    border-radius: 10px;
    padding: 13px 23px;
    text-decoration: none;
    font-weight: 700;
}

.btn-hero:hover {
    background: #e9f4ec;
    color: var(--primary);
}

.btn-outline-hero {
    border: 1px solid #ffffff90;
    color: white;
    border-radius: 10px;
    padding: 13px 23px;
    text-decoration: none;
    font-weight: 600;
}

.btn-outline-hero:hover {
    background: #ffffff20;
    color: white;
}

/* STATISTIK */
.stat-wrapper {
    margin-top: -40px;
    position: relative;
    z-index: 2;
}

.stat-card {
    background: white;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 6px 25px #0000000b;
    border: 1px solid #e8eee9;
    height: 100%;
}

.stat-icon {
    width: 50px;
    height: 50px;
    background: #e5f4e9;
    color: var(--secondary);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    margin-bottom: 15px;
}

.stat-card h3 {
    font-size: 31px;
    font-weight: 800;
    color: var(--primary);
}

.stat-card p {
    color: #718178;
    font-size: 14px;
    margin: 0;
}

/* SECTION */
.section {
    padding: 65px 0;
}

.section-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--secondary);
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.section-title {
    color: var(--dark);
    font-weight: 800;
    font-size: 30px;
}

.section-desc {
    color: #728278;
    line-height: 1.8;
}

/* MAP */
.map-panel {
    background: white;
    border-radius: 18px;
    padding: 25px;
    border: 1px solid #e3ebe5;
    box-shadow: 0 5px 25px #00000009;
}

.map-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 22px;
}

.map-toolbar h4 {
    font-weight: 750;
    color: var(--dark);
    margin: 0;
}

.btn-gps {
    background: var(--primary);
    color: white;
    border: 0;
    border-radius: 10px;
    padding: 12px 22px;
    font-weight: 600;
    cursor: pointer;
}

.btn-gps:hover {
    background: #0c4632;
}

.btn-gps:disabled {
    opacity: .7;
    cursor: not-allowed;
}

#map {
    height: 510px;
    width: 100%;
    border-radius: 12px;
    z-index: 1;
}

.gps-status {
    background: #eef7f0;
    border: 1px solid #d8eadd;
    padding: 14px 18px;
    border-radius: 10px;
    margin-top: 20px;
    color: #256444;
    font-size: 14px;
}

.warning-box {
    background: #fff9e9;
    border-left: 5px solid #e5a927;
    padding: 20px;
    border-radius: 10px;
    margin-top: 20px;
}

.warning-box h5 {
    font-size: 16px;
    font-weight: 750;
    color: #825c12;
}

.warning-box p {
    margin: 0;
    color: #755e2c;
    line-height: 1.7;
}

/* LOKASI */
.location-card {
    background: white;
    border: 1px solid #e4ece6;
    border-radius: 14px;
    padding: 22px;
    height: 100%;
    transition: .25s;
}

.location-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px #00000010;
}

.location-card h5 {
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 12px;
}

.location-card p {
    font-size: 14px;
    color: #77857b;
}

.hazard-tag {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 7px;
    background: #fff0e4;
    color: #b45c19;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 15px;
}

.btn-location {
    border: 0;
    background: #e9f5ee;
    color: var(--primary);
    padding: 10px 15px;
    border-radius: 8px;
    font-weight: 650;
    cursor: pointer;
}

.btn-location:hover {
    background: #d9ebdf;
}

/* PANDUAN */
.guide-card {
    text-align: center;
    background: white;
    border-radius: 16px;
    padding: 30px 20px;
    border: 1px solid #e5ece7;
    height: 100%;
}

.guide-icon {
    width: 65px;
    height: 65px;
    border-radius: 50%;
    background: #e6f4ea;
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 25px;
    margin: 0 auto 20px;
}

.guide-card h5 {
    font-weight: 750;
    color: var(--dark);
}

.guide-card p {
    color: #77857d;
    font-size: 14px;
    line-height: 1.7;
}

/* FOOTER */
.footer {
    background: #103e2e;
    color: white;
    padding: 40px 0 20px;
}

.footer p {
    color: #bcd3c5;
    font-size: 14px;
    line-height: 1.8;
}

.footer-bottom {
    border-top: 1px solid #ffffff25;
    margin-top: 25px;
    padding-top: 20px;
    text-align: center;
    font-size: 13px;
    color: #a8c2b3;
}

@media(max-width:768px) {
    .hero {
        padding: 55px 0 80px;
    }

    .section {
        padding: 45px 0;
    }

    .map-panel {
        padding: 14px;
    }

    #map {
        height: 380px;
    }

    .section-title {
        font-size: 25px;
    }
}

.route-form {
    background: #f8fbf9;
    border: 1px solid #e3ebe5;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 20px;
}

.route-form h5 {
    color: #145c43;
    font-weight: 750;
    margin-bottom: 18px;
}

.route-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
}

#statusRute {
    color: #52675b;
    font-size: 14px;
}

</style>
<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css"
/>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
<div class="container">

    <a class="brand-logo" href="index.php">
        <div class="brand-icon">
            <i class="fa-solid fa-road"></i>
        </div>
        <div>
            <div class="brand-title">AMANJALAN</div>
            <div class="brand-subtitle">
                Sistem Informasi Keselamatan Jalan
            </div>
        </div>
    </a>

    <button class="navbar-toggler" type="button"
        data-bs-toggle="collapse" data-bs-target="#navbarMenu">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarMenu">
        <ul class="navbar-nav ms-auto align-items-lg-center">
            <li class="nav-item">
                <a class="nav-link" href="#beranda">Beranda</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#peta">Peta Rawan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#lokasi">Daftar Lokasi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#panduan">Panduan</a>
            </li>
            <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                <a class="btn-admin" href="login.php">
                    <i class="fa-solid fa-user-shield me-2"></i>
                    Login Admin
                </a>
            </li>
        </ul>
    </div>

</div>
</nav>

<!-- HERO -->
<section class="hero" id="beranda">
<div class="container">
    <div class="hero-badge">
        <i class="fa-solid fa-shield-halved me-2"></i>
        Sistem Informasi Keselamatan Jalan
    </div>

    <h1>
        Kenali Titik Rawan,
        Wujudkan Perjalanan yang Lebih Aman.
    </h1>

    <p>
        AMANJALAN membantu masyarakat mengenali lokasi
        tikungan tajam dan jalur berbahaya melalui peta
        interaktif, pemantauan GPS, serta peringatan
        suara otomatis saat mendekati titik rawan.
    </p>

    <div class="hero-actions">
        <a href="#peta" class="btn-hero">
            <i class="fa-solid fa-map-location-dot me-2"></i>
            Jelajahi Peta
        </a>

        <a href="#panduan" class="btn-outline-hero">
            <i class="fa-solid fa-circle-info me-2"></i>
            Cara Penggunaan
        </a>
    </div>
</div>
</section>

<!-- STATISTIK -->
<div class="container stat-wrapper">
<div class="row g-4">

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-location-dot"></i>
            </div>
            <h3><?= $totalTitik ?></h3>
            <p>Total Titik Rawan Terpetakan</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-road"></i>
            </div>
            <h3><?= $jumlahTikungan ?></h3>
            <p>Lokasi Tikungan Tajam</p>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3><?= $jumlahLainnya ?></h3>
            <p>Jenis Bahaya Jalan Lainnya</p>
        </div>
    </div>

</div>
</div>

<!-- PETA -->
<section class="section" id="peta">
<div class="container">

    <div class="section-label">PETA INTERAKTIF</div>
    <h2 class="section-title">Peta Persebaran Titik Rawan</h2>
    <p class="section-desc mb-4">
        Pantau lokasi jalan berbahaya dan aktifkan GPS
        untuk memperoleh informasi posisi serta
        peringatan ketika mendekati titik rawan.
    </p>

    <div class="map-panel">

        <div class="map-toolbar">
            <div>
                <h4>Peta Lokasi Rawan</h4>
                <small class="text-muted">
                    Data lokasi yang telah terdaftar dalam sistem
                </small>
            </div>

            <button id="gpsButton" class="btn-gps">
                <i class="fa-solid fa-location-crosshairs me-2"></i>
                Aktifkan GPS
            </button>
        </div>

        
<div class="route-form">
    <h5>Rencanakan Perjalanan</h5>

    <label for="lokasiAwal">Lokasi Awal</label>
<input
    type="text"
    id="lokasiAwal"
    class="form-control mb-3"
    placeholder="Mendeteksi lokasi GPS..."
    readonly
>

    <label for="lokasiTujuan">Tujuan</label>
    <input
        type="text"
        id="lokasiTujuan"
        class="form-control mb-3"
        placeholder="Contoh: Universitas Diponegoro, Semarang"
    >

    <button type="button" id="cariRute" class="btn-gps">
        <i class="fa-solid fa-route me-2"></i>
        Cari Rute
    </button>

    <p id="statusRute" class="mt-3 mb-0" role="status">
        Masukkan lokasi awal dan tujuan.
    </p>
</div>


        <div id="map"></div>

        <div class="gps-status" id="statusGPS" role="status">
            <i class="fa-solid fa-circle-info me-2"></i>
            GPS belum diaktifkan.
        </div>

        <div class="warning-box" role="status">
            <h5>
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                Informasi Peringatan
            </h5>
            <p id="peringatan">
                Aktifkan GPS untuk mulai memantau titik rawan
                di sekitar posisi Anda.
            </p>
        </div>

    </div>
</div>
</section>

<!-- DAFTAR LOKASI -->
<section class="section pt-0" id="lokasi">
<div class="container">

    <div class="section-label">INFORMASI LOKASI</div>
    <h2 class="section-title">Daftar Titik Jalan Rawan</h2>
    <p class="section-desc mb-4">
        Berikut lokasi berbahaya yang telah tercatat
        dan memiliki koordinat pada sistem AMANJALAN.
    </p>

    <div class="row g-4">

    <?php if (empty($titikRawan)): ?>
        <div class="col-12">
            <div class="alert alert-info">
                Belum ada titik rawan yang terpetakan.
            </div>
        </div>
    <?php endif; ?>

    <?php foreach ($titikRawan as $titik): ?>
        <div class="col-md-6 col-lg-4">
            <div class="location-card">

                <span class="hazard-tag">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <?= htmlspecialchars($titik['jenis_bahaya']) ?>
                </span>

                <h5>
                    <i class="fa-solid fa-location-dot me-2"></i>
                    <?= htmlspecialchars($titik['nama_lokasi']) ?>
                </h5>

                <p>
                    <?= nl2br(htmlspecialchars(
                        $titik['keterangan'] ?: 'Belum ada keterangan.'
                    )) ?>
                </p>

                <button class="btn-location"
                    onclick="lihatLokasi(
                        <?= json_encode((float)$titik['latitude']) ?>,
                        <?= json_encode((float)$titik['longitude']) ?>
                    )">
                    <i class="fa-solid fa-map me-2"></i>
                    Lihat di Peta
                </button>

            </div>
        </div>
    <?php endforeach; ?>

    </div>
</div>
</section>

<!-- PANDUAN -->
<section class="section" id="panduan" style="background:#eaf3ed">
<div class="container">

    <div class="text-center mb-5">
        <div class="section-label">PANDUAN PENGGUNA</div>
        <h2 class="section-title">Cara Menggunakan AMANJALAN</h2>
        <p class="section-desc">
            Tiga langkah sederhana untuk memanfaatkan
            fitur pemantauan lokasi rawan.
        </p>
    </div>

    <div class="row g-4">

        <div class="col-md-4">
            <div class="guide-card">
                <div class="guide-icon">
                    <i class="fa-solid fa-map"></i>
                </div>
                <h5>1. Buka Peta</h5>
                <p>
                    Akses peta interaktif untuk melihat
                    persebaran lokasi jalan berbahaya
                    yang telah terdaftar.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="guide-card">
                <div class="guide-icon">
                    <i class="fa-solid fa-location-crosshairs"></i>
                </div>
                <h5>2. Aktifkan GPS</h5>
                <p>
                    Izinkan akses lokasi agar sistem
                    dapat mengetahui posisi pengguna
                    secara berkala.
                </p>
            </div>
        </div>

        <div class="col-md-4">
            <div class="guide-card">
                <div class="guide-icon">
                    <i class="fa-solid fa-volume-high"></i>
                </div>
                <h5>3. Terima Peringatan</h5>
                <p>
                    Sistem memberikan peringatan suara
                    ketika posisi terdeteksi berada
                    dalam radius 100 meter dari titik rawan.
                </p>
            </div>
        </div>

    </div>

    <p class="text-center text-muted mt-4 small">
        Peringatan bergantung pada akurasi GPS, izin lokasi,
        dan data titik rawan. Tetap utamakan rambu lalu lintas
        serta kondisi jalan yang sebenarnya.
    </p>

</div>
</section>

<!-- FOOTER -->
<footer class="footer">
<div class="container">

    <div class="row">
        <div class="col-md-7">
            <h4 class="fw-bold">
                <i class="fa-solid fa-shield-halved me-2"></i>
                AMANJALAN
            </h4>
            <p>
                Sistem informasi berbasis web yang dirancang
                untuk membantu masyarakat mengenali titik
                jalan rawan dan meningkatkan kewaspadaan
                dalam berkendara.
            </p>
        </div>

        <div class="col-md-5">
            <h6 class="fw-bold">Navigasi</h6>
            <p>
                <a href="#beranda" class="text-white text-decoration-none">Beranda</a>
                <span class="mx-2">|</span>
                <a href="#peta" class="text-white text-decoration-none">Peta Rawan</a>
                <span class="mx-2">|</span>
                <a href="#panduan" class="text-white text-decoration-none">Panduan</a>
            </p>
        </div>
    </div>

    <div class="footer-bottom">
        © <?= date('Y') ?> AMANJALAN.
        Sistem Informasi Keselamatan Jalan.
    </div>

</div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
const titikRawan = <?= json_encode(
    $titikRawan,
    JSON_HEX_TAG | JSON_HEX_APOS |
    JSON_HEX_QUOT | JSON_HEX_AMP |
    JSON_INVALID_UTF8_SUBSTITUTE
) ?>;

const map = L.map('map').setView([-7.07, 110.43], 13);

L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

const kontrolRute = L.Routing.control({
    waypoints: [],
    routeWhileDragging: false,
    addWaypoints: true,
    show: true,
    router: L.Routing.osrmv1({
        serviceUrl: 'https://router.project-osrm.org/route/v1'
    }),
    createMarker: function() {
        return null;
    }
}).addTo(map);


const inputLokasiAwal = document.getElementById('lokasiAwal');
const inputLokasiTujuan = document.getElementById('lokasiTujuan');
const tombolCariRute = document.getElementById('cariRute');
const statusRute = document.getElementById('statusRute');

let posisiPengguna = null;

// Mencari koordinat dari alamat yang diketik
async function cariKoordinat(alamat) {
    const url =
        'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&countrycodes=id&q=' +
        encodeURIComponent(alamat);

    const respons = await fetch(url);
    if (!respons.ok) {
        throw new Error('Layanan pencarian lokasi sedang bermasalah.');
    }

    const data = await respons.json();

    if (!data.length) {
        throw new Error('Lokasi "' + alamat + '" tidak ditemukan.');
    }

    return L.latLng(
        parseFloat(data[0].lat),
        parseFloat(data[0].lon)
    );
}

// Membuat rute dari GPS ke tujuan
tombolCariRute.addEventListener('click', async function () {
    const tujuan = inputLokasiTujuan.value.trim();

    if (!posisiPengguna) {
        statusRute.textContent =
            'Lokasi GPS belum tersedia. Izinkan akses lokasi lalu coba lagi.';
        return;
    }

    if (!tujuan) {
        statusRute.textContent = 'Silakan isi lokasi tujuan terlebih dahulu.';
        inputLokasiTujuan.focus();
        return;
    }

    tombolCariRute.disabled = true;
    statusRute.textContent = 'Sedang mencari lokasi tujuan...';

    try {
        const koordinatTujuan = await cariKoordinat(tujuan);

        kontrolRute.setWaypoints([
            posisiPengguna,
            koordinatTujuan
        ]);

        statusRute.textContent =
            'Lokasi ditemukan. Sedang menampilkan rute perjalanan...';

    } catch (error) {
        statusRute.textContent = error.message;
    } finally {
        tombolCariRute.disabled = false;
    }
});


// Mencegah data database disisipkan sebagai HTML
function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;'
    }[char]));
}

// Marker titik rawan
titikRawan.forEach(titik => {
    const lat = Number(titik.latitude);
    const lon = Number(titik.longitude);

    L.marker([lat, lon])
        .addTo(map)
        .bindPopup(
            '<div style="min-width:180px">' +
            '<b>' + escapeHTML(titik.nama_lokasi) + '</b><br>' +
            '<span style="color:#b45309">' +
            escapeHTML(titik.jenis_bahaya) + '</span><br>' +
            escapeHTML(titik.keterangan || '') +
            '</div>'
        );
});

// Klik lihat lokasi dari daftar
function lihatLokasi(lat, lon) {
    document.getElementById('peta').scrollIntoView({
        behavior: 'smooth'
    });

    setTimeout(() => {
        map.invalidateSize();
        map.setView([lat, lon], 17);
    }, 500);
}

const statusGPS = document.getElementById('statusGPS');
const peringatan = document.getElementById('peringatan');
const gpsButton = document.getElementById('gpsButton');

const radiusPeringatan = 100;
let watchId = null;
let posisiMarker = null;
let titikYangSudahDiperingatkan = new Set();
let sudahFokusPosisi = false;

function bicara(teks) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();

        const suara = new SpeechSynthesisUtterance(teks);
        suara.lang = 'id-ID';
        suara.rate = 1;

        window.speechSynthesis.speak(suara);
    }
}

function hitungJarak(lat1, lon1, lat2, lon2) {
    const R = 6371000;
    const rad = derajat => derajat * Math.PI / 180;

    const dLat = rad(lat2 - lat1);
    const dLon = rad(lon2 - lon1);

    const a =
        Math.sin(dLat / 2) ** 2 +
        Math.cos(rad(lat1)) *
        Math.cos(rad(lat2)) *
        Math.sin(dLon / 2) ** 2;

    return 2 * R * Math.atan2(
        Math.sqrt(a),
        Math.sqrt(1 - a)
    );
}

gpsButton.addEventListener('click', () => {
    if (!navigator.geolocation) {
        statusGPS.textContent =
            'Browser tidak mendukung fitur GPS.';
        return;
    }

    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
    }

    statusGPS.textContent = 'Mencari posisi GPS...';
    peringatan.textContent = 'Menunggu posisi GPS...';
    gpsButton.textContent = 'Mengaktifkan GPS...';
    gpsButton.disabled = true;

    watchId = navigator.geolocation.watchPosition(
        posisi => {
            const lat = posisi.coords.latitude;
            const lon = posisi.coords.longitude;
            const akurasi = posisi.coords.accuracy;

            if (akurasi > 100) {
    statusGPS.textContent =
        'GPS belum akurat (' +
        Math.round(akurasi) +
        ' meter). Sedang mencoba mendapatkan posisi yang lebih baik.';

    gpsButton.innerHTML =
        '<i class="fa-solid fa-location-crosshairs me-2"></i>GPS Kurang Akurat';

    gpsButton.disabled = false;
    return;
}
            posisiPengguna = L.latLng(lat, lon);

            inputLokasiAwal.value =
                'Lokasi GPS (' +
                lat.toFixed(5) + ',' +
                lon.toFixed(5) + ')';
            
            statusRute.textContent =
                'Lokasi awal tersedia. Masukkan tujuan lalu klik Cari Rute.';

            statusGPS.textContent =
                'GPS aktif. Akurasi sekitar ' +
                Math.round(akurasi) + ' meter.';

            if (posisiMarker) {
                posisiMarker.setLatLng([lat, lon]);
            } else {
                posisiMarker = L.circleMarker([lat, lon], {
                    radius: 9,
                    color: '#ffffff',
                    weight: 3,
                    fillColor: '#1976d2',
                    fillOpacity: 1
                }).addTo(map).bindPopup('Posisi Anda');
            }

            // Fokus pada posisi pertama kali
            if (!sudahFokusPosisi) {
                map.setView([lat, lon], 16);
                sudahFokusPosisi = true;
            }

            let titikTerdekat = null;
            let jarakTerdekat = Infinity;
            let pesanBaru = null;

            titikRawan.forEach(titik => {
                const jarak = hitungJarak(
                    lat, lon,
                    Number(titik.latitude),
                    Number(titik.longitude)
                );

                if (jarak < jarakTerdekat) {
                    jarakTerdekat = jarak;
                    titikTerdekat = titik;
                }

                const idTitik = String(titik.id);

                if (jarak > 150) {
                    titikYangSudahDiperingatkan.delete(idTitik);
                }

                if (
                    jarak <= radiusPeringatan &&
                    !titikYangSudahDiperingatkan.has(idTitik)
                ) {
                    titikYangSudahDiperingatkan.add(idTitik);

                    if (pesanBaru === null) {
                        pesanBaru =
                            'Perhatian! Anda mendekati lokasi rawan ' +
                            titik.nama_lokasi +
                            '. Jenis bahaya: ' +
                            titik.jenis_bahaya + '.';
                    }
                }
            });

            if (pesanBaru !== null) {
                bicara(pesanBaru);
            }

            if (titikTerdekat === null) {
                peringatan.textContent =
                    'Belum ada titik rawan dengan koordinat.';
            } else if (jarakTerdekat <= radiusPeringatan) {
                peringatan.textContent =
                    'PERINGATAN: Anda berada dekat ' +
                    titikTerdekat.nama_lokasi +
                    '. Jenis bahaya: ' +
                    titikTerdekat.jenis_bahaya +
                    '. Jarak sekitar ' +
                    Math.round(jarakTerdekat) + ' meter.';
            } else {
                peringatan.textContent =
                    'Tidak ada titik rawan dalam radius 100 meter. ' +
                    'Titik terdekat: ' +
                    titikTerdekat.nama_lokasi +
                    ', sekitar ' +
                    Math.round(jarakTerdekat) + ' meter.';
            }
        },
        error => {
            let pesan = 'Lokasi gagal ditemukan.';

            if (error.code === 1) {
                pesan = 'Izin lokasi ditolak. Aktifkan izin lokasi browser.';
            } else if (error.code === 2) {
                pesan = 'Posisi tidak tersedia. Periksa pengaturan lokasi.';
            } else if (error.code === 3) {
                pesan = 'Pencarian lokasi terlalu lama. Coba lagi.';
            }

            statusGPS.textContent = pesan;
            gpsButton.innerHTML =
                '<i class="fa-solid fa-location-crosshairs me-2"></i>' +
                'Coba Aktifkan GPS Lagi';
            gpsButton.disabled = false;
            watchId = null;
        },
        {
            enableHighAccuracy: true,
            maximumAge: 0,
            timeout: 20000
        }
    );
});
</script>

</body>
</html>
