
<?php
session_start();

if (empty($_SESSION['admin_login'])) {
    header('Location: ../login.php');
    exit;
}

require_once '../koneksi.php';

// Token keamanan formulir
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pesan = '';
$jenisPesan = 'success';

// Proses mengubah status titik
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $aksi = $_POST['aksi'] ?? '';

    if (
        !is_string($token) ||
        !hash_equals($_SESSION['csrf_token'], $token)
    ) {
        $pesan = 'Permintaan tidak valid. Silakan muat ulang halaman.';
        $jenisPesan = 'error';
    } elseif (!$id || !in_array($aksi, ['aktifkan', 'nonaktifkan'], true)) {
        $pesan = 'Data permintaan tidak valid.';
        $jenisPesan = 'error';
    } else {
        $statusBaru = ($aksi === 'aktifkan') ? 'aktif' : 'nonaktif';

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE titik_rawan SET status = ? WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, 'si', $statusBaru, $id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) >= 0) {
            $pesan = 'Status titik rawan berhasil diperbarui.';
        } else {
            $pesan = 'Status titik rawan gagal diperbarui.';
            $jenisPesan = 'error';
        }

        mysqli_stmt_close($stmt);
    }
}

// Ambil semua data titik rawan
$query = mysqli_query(
    $conn,
    "SELECT id, nama_lokasi, jenis_bahaya, keterangan,
            latitude, longitude, status
     FROM titik_rawan
     ORDER BY id DESC"
);

if (!$query) {
    die('Data titik rawan gagal dimuat.');
}

function aman($nilai) {
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Titik Rawan - AmanJalan</title>
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .halaman-kelola {
            max-width: 1150px;
            margin: auto;
            padding: 30px 20px 45px;
        }

        .judul-halaman {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 24px;
        }

        .judul-halaman h2 {
            margin: 0 0 5px;
            color: #155b43;
        }

        .judul-halaman p {
            margin: 0;
            color: #6b7c72;
            font-size: 14px;
        }

        .tombol-link {
            display: inline-block;
            padding: 10px 15px;
            background: #217653;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
        }

        .tabel-wrapper {
            overflow-x: auto;
            background: white;
            border: 1px solid #dce7df;
            border-radius: 12px;
        }

        .tabel-titik {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .tabel-titik th,
        .tabel-titik td {
            padding: 13px 15px;
            border-bottom: 1px solid #e7eee8;
            text-align: left;
            vertical-align: top;
            font-size: 13px;
        }

        .tabel-titik th {
            background: #e7f3eb;
            color: #155b43;
        }

        .status {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-aktif {
            background: #e1f5e7;
            color: #176b3a;
        }

        .status-nonaktif {
            background: #f1f1f1;
            color: #666;
        }

        .tombol-status {
            padding: 7px 10px;
            border: none;
            border-radius: 6px;
            background: #217653;
            color: white;
            font-size: 12px;
            cursor: pointer;
        }

        .tombol-nonaktif {
            background: #a83c35;
        }

        .pesan {
            padding: 12px 15px;
            margin-bottom: 18px;
            border-radius: 8px;
            background: #e7f3eb;
            color: #155b43;
        }

        .pesan.error {
            background: #fff0ef;
            color: #a52620;
        }

        .kosong {
            padding: 30px;
            text-align: center;
            color: #6b7c72;
        }

        @media (max-width: 600px) {
            .halaman-kelola {
                padding: 22px 14px;
            }
        }
    </style>
</head>
<body>

<header>
    <h1>AMANJALAN</h1>
    <p>Pengelolaan Data Titik Rawan</p>
</header>

<main class="halaman-kelola">

    <div class="judul-halaman">
        <div>
            <h2>Daftar Titik Rawan</h2>
            <p>Lihat dan kelola lokasi yang tersimpan di database.</p>
        </div>

        <a class="tombol-link" href="tambah_titik.php">
            + Tambah Titik
        </a>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="pesan <?= $jenisPesan === 'error' ? 'error' : '' ?>">
            <?= aman($pesan) ?>
        </div>
    <?php endif; ?>

    <div class="tabel-wrapper">
        <table class="tabel-titik">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Lokasi</th>
                    <th>Jenis Bahaya</th>
                    <th>Koordinat</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
            <?php if (mysqli_num_rows($query) === 0): ?>
                <tr>
                    <td colspan="6" class="kosong">
                        Belum ada data titik rawan.
                    </td>
                </tr>
            <?php else: ?>
                <?php $nomor = 1; ?>
                <?php while ($row = mysqli_fetch_assoc($query)): ?>
                    <tr>
                        <td><?= $nomor++ ?></td>
                        <td>
                            <strong><?= aman($row['nama_lokasi']) ?></strong>
                            <br>
                            <small><?= aman($row['keterangan']) ?></small>
                        </td>
                        <td><?= aman($row['jenis_bahaya']) ?></td>
                        <td>
                            <?php if (
                                $row['latitude'] !== null &&
                                $row['longitude'] !== null
                            ): ?>
                                <?= aman($row['latitude']) ?>,
                                <?= aman($row['longitude']) ?>
                            <?php else: ?>
                                <span>Belum ada koordinat</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status status-<?= aman($row['status']) ?>">
                                <?= aman(ucfirst($row['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST"
                                  onsubmit="return confirm('Yakin ingin mengubah status titik ini?')">
                                <input type="hidden" name="csrf_token"
                                       value="<?= aman($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id"
                                       value="<?= (int) $row['id'] ?>">

                                <?php if ($row['status'] === 'aktif'): ?>
                                    <input type="hidden" name="aksi"
                                           value="nonaktifkan">
                                    <button class="tombol-status tombol-nonaktif"
                                            type="submit">
                                        Nonaktifkan
                                    </button>
                                <?php else: ?>
                                    <input type="hidden" name="aksi"
                                           value="aktifkan">
                                    <button class="tombol-status"
                                            type="submit">
                                        Aktifkan
                                    </button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top: 20px;">
        <a href="dashboard.php">← Kembali ke Dashboard Admin</a>
    </p>

</main>

</body>
</html>
