# AMANJALAN — Sistem Informasi Keselamatan Jalan

AmanJalan adalah aplikasi web berbasis PHP dan MySQL yang dirancang untuk membantu pengguna mengenali titik rawan jalan melalui peta interaktif, pemantauan GPS, dan peringatan suara.

## Fitur Utama

- **Peta Interaktif:** Menampilkan lokasi titik rawan jalan.
- **Informasi Titik Rawan:** Menampilkan nama lokasi, jenis bahaya, dan keterangan.
- **Pemantauan GPS:** Mendeteksi posisi pengguna.
- **Peringatan Suara:** Memberikan peringatan ketika pengguna mendekati titik rawan dalam radius 100 meter.
- **Pencarian Rute:** Membantu pengguna mencari rute menuju lokasi tujuan.
- **Dashboard Admin:** Mengelola informasi titik rawan jalan.
- **Pengelolaan Akun Admin:** Login dan penggantian password.

## Teknologi

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- Bootstrap
- Leaflet
- OpenStreetMap
- Leaflet Routing Machine

## Menjalankan Proyek

1. Instal XAMPP dan aktifkan Apache serta MySQL.
2. Siapkan database MySQL sesuai struktur tabel yang digunakan aplikasi.
3. Atur koneksi database melalui file konfigurasi lokal `koneksi.php`.
4. Letakkan folder proyek di direktori `htdocs`.
5. Buka aplikasi melalui `http://localhost/amanjalan/`.

**Catatan:** File konfigurasi database dan kredensial admin tidak disertakan dalam repository publik.

## Status Proyek

Proyek dikembangkan sebagai aplikasi web pemetaan titik rawan jalan untuk membantu meningkatkan kewaspadaan pengguna saat berkendara.
