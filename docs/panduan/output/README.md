# Panduan Perpustakaan — Berkas Deck (.pptx)

Dua deck PowerPoint native (16:9), dibuat dengan pipeline PPT Master:
author SVG (kontrak semantic-svg) → `finalize_svg.py` → `svg_to_pptx.py`.

## Berkas

| Berkas | Isi | Halaman |
|---|---|---|
| `Panduan Pengguna Perpustakaan.pptx` | Panduan anggota (end-user) | 12 |
| `Panduan Petugas Perpustakaan.pptx` | Panduan petugas/admin | 14 |

## Struktur

**Panduan Pengguna (12 halaman)**
1. Cover
2. Daftar Isi
3. Mengenal Sistem
4. Membuat Akun
5. Masuk & Keluar
6. Menelusuri Katalog
7. Detail Buku & Nomor Panggil
8. Mengajukan Reservasi
9. Memantau Reservasi
10. Peminjaman Saya
11. Profil & Keamanan
12. Aturan & Tips

**Panduan Petugas (14 halaman)**
1. Cover
2. Daftar Isi
3. Peran Petugas
4. Menambah Buku
5. Mengubah & Menghapus Buku
6. Mengelola Kategori
7. Mencatat Peminjaman
8. Memproses Pengembalian
9. Mengelola Antrean Reservasi
10. Laporan Sirkulasi
11. Ekspor Data CSV
12. Notifikasi Keterlambatan
13. Rutinitas Harian
14. Penutup

## Aturan bisnis yang dirujuk (sesuai kode aplikasi)

- Masa pinjam **14 hari**; maksimum **3 buku aktif** per anggota.
- Denda keterlambatan **Rp1.000 per hari**.
- Penahanan reservasi **3 hari** setelah buku tersedia.
- Notifikasi keterlambatan otomatis dikirim **harian pukul 07:00**.
- Ekspor laporan CSV: peminjaman / koleksi buku / reservasi, filter rentang tanggal.

## Kualitas

- Kedua deck **100% lulus** `svg_quality_checker.py --stage final` (0 error, 0 warning).
- `.pptx` hasil ekspor lolos postflight, `quality_gate=passed`.
- Ikon vector (set `tabler-outline`), tanpa emoji.
- Desain mengikuti design system "arsip perpustakaan": paper/ink/moss, sudut tegas,
  label mono uppercase, tipografi Fraunces/Archivo/IBM Plex Mono.
- Isi telah disinkronkan dengan label antarmuka asli (mis. tombol "Proses Ambil",
  "Kembalikan", "Catat Peminjaman", "Reservasi Buku Ini").

## Regenerasi

```bash
cd /home/agentuser/workspace/ppt-master
. .venv/bin/activate
python3 /home/agentuser/workspace/perpustakaan/docs/panduan/build_deck.py
python3 skills/ppt-master/scripts/svg_quality_checker.py projects/panduan_user_20261011  --quick-generate --canonical-authoring --stage final --json
python3 skills/ppt-master/scripts/svg_quality_checker.py projects/panduan_admin_20261011 --quick-generate --canonical-authoring --stage final --json
python3 skills/ppt-master/scripts/finalize_svg.py projects/panduan_user_20261011
python3 skills/ppt-master/scripts/finalize_svg.py projects/panduan_admin_20261011
python3 skills/ppt-master/scripts/svg_to_pptx.py projects/panduan_user_20261011  --quick-generate
python3 skills/ppt-master/scripts/svg_to_pptx.py projects/panduan_admin_20261011 --quick-generate
```
