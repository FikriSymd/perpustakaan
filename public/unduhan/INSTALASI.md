# Aplikasi Perpustakaan — Panduan Instalasi Cepat

Aplikasi manajemen perpustakaan berbasis **Laravel 13** (butuh **PHP 8.3+**).
Fitur: katalog, peminjaman, reservasi berantre, notifikasi email keterlambatan,
dan ekspor laporan CSV.

> **Panduan lengkap langkah-demi-langkah ada di PDF:**
> `Panduan Setup Lokal Windows.pdf` (untuk menjalankan di komputer sendiri)
> dan `Panduan Hosting Aplikasi Perpustakaan.pdf` (untuk online/VPS/shared hosting).

---

## Cara tercepat (kalau PHP + Composer sudah terpasang)

```bash
composer run setup     # install dependensi, siapkan .env, migrate, build aset
php artisan serve      # jalankan di http://127.0.0.1:8000
```

Atau langkah manual:

```bash
composer install
php artisan key:generate
php artisan migrate --seed     # atau: php artisan migrate:fresh --seed
php artisan storage:link
npm install && npm run build   # hanya bila ingin mengubah CSS/JS
php artisan serve
```

---

## 1. Kebutuhan

- PHP **8.3+** dengan ekstensi: `bcmath ctype curl fileinfo gd json libxml
  mbstring openssl pdo_sqlite sqlite3 tokenizer xml xmlreader xmlwriter zip`
- Composer 2.x
- Node.js 18+ *(hanya bila ingin mengubah CSS/JS)*
- Database: SQLite (bawaan) atau MySQL/MariaDB

> Paket ini sudah menyertakan aset ter-build (`public/build/`), jadi kamu
> **tidak wajib** memasang Node.js hanya untuk menjalankannya.

---

## 2. Akun bawaan (dari seeder)

| Peran   | Email                     | Password   |
|---------|---------------------------|------------|
| Admin   | `admin@perpustakaan.test` | `password` |
| Anggota | `budi@perpustakaan.test`  | `password` |

> **Penting:** ganti atau hapus akun contoh ini sebelum dipakai di produksi.

---

## 3. Untuk produksi — yang WAJIB diubah

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domainmu.com

# Email sungguhan (bawaan hanya menulis ke log)
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="perpustakaan@domainmu.com"
```

Lalu optimalkan:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Penjadwal (cron) — WAJIB untuk notifikasi otomatis

Notifikasi keterlambatan berjalan **setiap hari pukul 07:00**:

```
* * * * * cd /path/ke/perpustakaan && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Menjalankan pengujian

```bash
php artisan test
# 80 tes
```

---

## 6. Struktur singkat

```
app/                 Model, Controller, Notification, Service
  Http/Controllers/  Admin/, Auth/, CatalogController, ReservationController, ...
  Models/            Book, Category, Loan, Reservation, User
  Notifications/     OverdueLoanNotification, ReservationReadyNotification
database/
  migrations/        Skema tabel
  seeders/           Data contoh
  database.sqlite    Database bawaan
docs/panduan/output/ Buku panduan (.pptx) pengguna & petugas
public/              Document root web server
resources/
  css/app.css        Design system "arsip perpustakaan"
  views/             Blade (admin/, member/, catalog/, mail/, ...)
routes/              web.php, auth.php, console.php
tests/               Feature & Unit (80 tes)
```

---

## 7. Design system

Tampilan memakai estetika "arsip perpustakaan": kertas hangat, tinta pekat,
garis rambut, aksen moss + brass, sudut tegas, label mono uppercase, dan
ikon SVG (`<x-icon>`) — tanpa emoji. Tipografi: Fraunces / Archivo / IBM Plex Mono.

---

## Lisensi

Proyek aplikasi ini dibangun di atas Laravel (lisensi MIT).
