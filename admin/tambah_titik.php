<?php
session_start();

if (empty($_SESSION['admin_login'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../koneksi.php';

$pesan = '';
$jenisPesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_lokasi'] ?? '');
    $jenis = trim($_POST['jenis_bahaya'] ?? '');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $latitude = trim($_POST['latitude'] ?? '');
    $longitude = trim($_POST['longitude'] ?? '');

    if ($nama === '' || $jenis === '') {
        $pesan = 'Nama lokasi dan jenis bahaya wajib diisi.';
        $jenisPesan = 'error';
    } elseif (
        ($latitude !== '' &&
            (!is_numeric($latitude) || $latitude < -90 || $latitude > 90)) ||
        ($longitude !== '' &&
            (!is_numeric($longitude) || $longitude < -180 || $longitude > 180))
    ) {
        $pesan = 'Koordinat latitude atau longitude tidak valid.';
        $jenisPesan = 'error';
    } elseif (($latitude === '') !== ($longitude === '')) {
        $pesan = 'Isi latitude dan longitude sekaligus, atau kosongkan keduanya.';
        $jenisPesan = 'error';
    } else {
        $lat = $latitude === '' ? null : (float) $latitude;
        $lon = $longitude === '' ? null : (float) $longitude;

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO titik_rawan
            (nama_lokasi, jenis_bahaya, keterangan, latitude, longitude)
            VALUES (?, ?, ?, ?, ?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            'sssdd',
            $nama,
            $jenis,
            $keterangan,
            $lat,
            $lon
        );

        if (mysqli_stmt_execute($stmt)) {
            $pesan = 'Titik rawan berhasil disimpan!';
            $jenisPesan = 'sukses';
        } else {
            $pesan = 'Data gagal disimpan. Silakan coba lagi.';
            $jenisPesan = 'error';
        }

        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Titik Rawan - AmanJalan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .form-titik {
            display: grid;
            gap: 14px;
        }
        .form-titik label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .form-titik input,
        .form-titik select,
        .form-titik textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font: inherit;
        }
        .form-titik button {
            background: #176b48;
            color: white;
            padding: 12px;
            border: 0;
            border-radius: 6px;
            cursor: pointer;
        }
        .pesan {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 16px;
        }
        .sukses {
            background: #e2f5e9;
            color: #176b48;
        }
        .error {
            background: #fde8e8;
            color: #a12626;
        }
    </style>
</head>
<body>
<header>
    <h1>AMANJALAN</h1>
    <p>Form Input Titik Rawan</p>
</header>

<main class="container">
    <section class="info">
        <h2>Tambah Lokasi Rawan</h2>
        <p>Masukkan informasi lokasi jalan yang berbahaya.</p>

        <?php if ($pesan !== ''): ?>
            <div class="pesan <?= htmlspecialchars($jenisPesan) ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="form-titik">
            <div>
                <label for="nama_lokasi">Nama lokasi *</label>
                <input
                    type="text"
                    id="nama_lokasi"
                    name="nama_lokasi"
                    maxlength="150"
                    required
                    value="<?= htmlspecialchars($_POST['nama_lokasi'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="jenis_bahaya">Jenis bahaya *</label>
                <select id="jenis_bahaya" name="jenis_bahaya" required>
                    <option value="">-- Pilih jenis bahaya --</option>
                    <?php
                    $jenisPilihan = [
                        'Tikungan tajam',
                        'Jalan berlubang',
                        'Jalan licin',
                        'Tanjakan atau turunan curam',
                        'Jalan sempit',
                        'Rawan kecelakaan',
                        'Lainnya'
                    ];
                    foreach ($jenisPilihan as $pilihan):
                    ?>
                        <option
                            value="<?= htmlspecialchars($pilihan) ?>"
                            <?= (($_POST['jenis_bahaya'] ?? '') === $pilihan) ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($pilihan) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label for="keterangan">Keterangan</label>
                <textarea
                    id="keterangan"
                    name="keterangan"
                    rows="3"
                ><?= htmlspecialchars($_POST['keterangan'] ?? '') ?></textarea>
            </div>

            <div>
                <label for="latitude">Latitude</label>
                <input
                    type="number"
                    step="any"
                    id="latitude"
                    name="latitude"
                    placeholder="Contoh: -7.0500000"
                    value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>"
                >
            </div>

            <div>
                <label for="longitude">Longitude</label>
                <input
                    type="number"
                    step="any"
                    id="longitude"
                    name="longitude"
                    placeholder="Contoh: 110.4200000"
                    value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>"
                >
            </div>

            <p>Koordinat boleh dikosongkan sementara. Tanpa koordinat, titik belum tampil di peta.</p>

            <button type="submit">Simpan Titik Rawan</button>
            <a href="../index.php">Kembali ke Peta AmanJalan</a>
        </form>
    </section>
</main>
</body>
</html>
