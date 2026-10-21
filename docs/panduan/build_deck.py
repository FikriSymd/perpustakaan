#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Generator SVG untuk Buku Panduan Perpustakaan (PPT Master quick-generate).

Menghasilkan SVG halaman sesuai kontrak PPT Master, lalu di-export ke .pptx
oleh scripts/finalize_svg.py + scripts/svg_to_pptx.py.
"""
import os
import sys

W, H = 1280, 720
M = 72
CW = W - 2 * M  # 1136

PAPER = "#F7F4EC"
SURF = "#FFFFFF"
SURF2 = "#EFEADD"
INK = "#23201B"
INK2 = "#5C5548"
MOSS = "#3F5D3A"
MOSS2 = "#6B8A63"
RUST = "#9E4B2E"
BRASS = "#B08A3E"
LINE = "#D9D2C2"
WHITE = "#FFFFFF"

SERIF = "Georgia"
SANS = "Segoe UI"
MONO = "Consolas"

USER_TOTAL = 12
ADMIN_TOTAL = 14


def esc(s):
    return s.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")


def rect(x, y, w, h, fill, stroke=None, sw=None, role=None, eid=None):
    a = ['x="%s"' % x, 'y="%s"' % y, 'width="%s"' % w, 'height="%s"' % h, 'fill="%s"' % fill]
    if stroke:
        a.append('stroke="%s"' % stroke)
    if sw is not None:
        a.append('stroke-width="%s"' % sw)
    if role:
        a.append('data-pptx-role="%s"' % role)
    if eid:
        a.append('id="%s"' % eid)
    return "<rect " + " ".join(a) + "/>"


def ln(x1, y1, x2, y2, stroke=LINE, sw=1):
    return ('<line x1="%s" y1="%s" x2="%s" y2="%s" stroke="%s" stroke-width="%s"/>'
            % (x1, y1, x2, y2, stroke, sw))


def txt(x, y, s, size=20, font=None, weight=None, fill=INK, anchor=None, ls=None):
    a = ['x="%s"' % x, 'y="%s"' % y, 'font-size="%s"' % size]
    if font and font != SANS:
        a.append('font-family="%s"' % font)
    if weight:
        a.append('font-weight="%s"' % weight)
    if fill:
        a.append('fill="%s"' % fill)
    if anchor:
        a.append('text-anchor="%s"' % anchor)
    if ls is not None:
        a.append('letter-spacing="%s"' % ls)
    return "<text " + " ".join(a) + ">" + esc(s) + "</text>"


def icon(name, x, y, size=28, color=MOSS, sw=None):
    a = ['data-icon="tabler-outline/%s"' % name, 'x="%s"' % x, 'y="%s"' % y,
         'width="%s"' % size, 'height="%s"' % size, 'fill="%s"' % color]
    if sw is not None:
        a.append('stroke-width="%s"' % sw)
    return "<use " + " ".join(a) + "/>"


def para(x, y, lines, size=16, fill=INK2, lh=25):
    s = '<text x="%s" y="%s" font-size="%s" fill="%s">' % (x, y, size, fill)
    s += esc(lines[0])
    for l in lines[1:]:
        s += '<tspan x="%s" dy="%s">%s</tspan>' % (x, lh, esc(l))
    s += "</text>"
    return s


def grp(gid, x, y, w, h, inner):
    return ('<g id="%s" data-pptx-bounds="%d %d %d %d">%s</g>'
            % (gid, x, y, w, h, inner))


def svg_page(role, groups, decorations=None, lang="id-ID"):
    head = ('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %d %d" '
            'font-family="%s" font-size="20" fill="%s" lang="%s" data-pptx-page-role="%s">'
            % (W, H, SANS, INK, lang, role))
    bg = rect(0, 0, W, H, PAPER, role="background", eid="page-bg")
    deco = "".join(decorations or [])
    return head + bg + deco + "".join(groups) + "</svg>"


# ---------------------------------------------------------------- components

def header(kicker, title, right):
    inner = txt(M, 88, kicker, size=15, font=MONO, fill=MOSS, ls=3)
    inner += txt(M, 136, title, size=36, font=SERIF, weight="bold", fill=INK)
    inner += txt(W - M, 88, right, size=13, font=MONO, fill=INK2, anchor="end", ls=2)
    inner += ln(M, 166, W - M, 166, LINE)
    return grp("hdr", M, 56, CW, 118, inner)


def footer(left, page, total):
    inner = ln(M, 668, W - M, 668, LINE)
    inner += txt(M, 694, left, size=13, font=MONO, fill=INK2, ls=1)
    inner += txt(W - M, 694, "%02d / %02d" % (page, total), size=13, font=MONO,
                 fill=INK2, anchor="end", ls=1)
    return grp("ftr", M, 660, CW, 44, inner)


def card_icon(x, y, w, h, ic, title, lines, accent=MOSS, label=None):
    inner = rect(x, y, w, h, SURF, stroke=LINE, sw=1)
    inner += rect(x, y, 5, h, accent)
    inner += icon(ic, x + 26, y + 24, 30, accent)
    tx = x + 26 + 30 + 16
    if label:
        inner += txt(tx, y + 34, label, size=12, font=MONO, fill=INK2, ls=2)
        inner += txt(tx, y + 66, title, size=21, weight="bold", fill=INK)
        ty = y + 98
    else:
        inner += txt(tx, y + 50, title, size=21, weight="bold", fill=INK)
        ty = y + 82
    inner += para(x + 26, ty, lines)
    return inner


def step_row(x, y, w, h, num, title, desc, accent=MOSS):
    inner = rect(x, y, w, h, SURF, stroke=LINE, sw=1)
    bs = 46
    by = y + (h - bs) // 2
    inner += rect(x + 26, by, bs, bs, accent)
    inner += txt(x + 26 + bs / 2, by + bs / 2 + 8, str(num), size=22, font=SERIF,
                 weight="bold", fill=WHITE, anchor="middle")
    inner += txt(x + 26 + bs + 22, y + h / 2 - 6, title, size=20, weight="bold", fill=INK)
    inner += txt(x + 26 + bs + 22, y + h / 2 + 20, desc, size=16, fill=INK2)
    return inner


def bullets_card(x, y, w, h, ic, title, items, accent=MOSS):
    inner = rect(x, y, w, h, SURF, stroke=LINE, sw=1)
    inner += rect(x, y, w, 5, accent)
    inner += icon(ic, x + 26, y + 30, 28, accent)
    inner += txt(x + 66, y + 52, title, size=21, weight="bold", fill=INK)
    ty = y + 102
    for it in items:
        inner += rect(x + 26, ty - 7, 7, 7, accent)
        inner += txt(x + 46, ty, it, size=16, fill=INK2)
        ty += 28
    return inner


def note(x, y, w, h, text, accent=BRASS, ic="info-circle"):
    inner = rect(x, y, w, h, SURF2, stroke=LINE, sw=1)
    inner += rect(x, y, 5, h, accent)
    inner += icon(ic, x + 24, y + (h - 26) // 2, 26, accent)
    inner += txt(x + 62, y + h / 2 + 7, text, size=16, fill=INK)
    return inner


def stat(x, y, w, h, big, small, accent=MOSS):
    inner = rect(x, y, w, h, SURF, stroke=LINE, sw=1)
    inner += rect(x, y, w, 5, accent)
    inner += txt(x + 26, y + 56, big, size=30, font=SERIF, weight="bold", fill=accent)
    inner += txt(x + 26, y + 82, small, size=13, font=MONO, fill=INK2, ls=2)
    return inner


def table(x, y, w, cols, rows):
    hh = 46
    rh = 44
    inner = rect(x, y, w, hh + rh * len(rows), SURF, stroke=LINE, sw=1)
    inner += rect(x, y, w, hh, SURF2)
    cx = x
    for (lab, fr) in cols:
        inner += txt(cx + 18, y + 30, lab, size=13, font=MONO, fill=MOSS, ls=1)
        cx += w * fr
    ry = y + hh
    for r in rows:
        inner += ln(x, ry, x + w, ry, LINE)
        cx = x
        for i, cell in enumerate(r):
            inner += txt(cx + 18, ry + 28, cell, size=15, fill=INK2)
            cx += w * cols[i][1]
        ry += rh
    return inner


def cover(kicker, l1, l2, sub1, sub2, meta1, meta2, badge_label, badge_sub, ic,
          role="cover"):
    groups = []
    t = txt(88, 200, kicker, size=16, font=MONO, fill=MOSS, ls=4)
    t += txt(88, 296, l1, size=62, font=SERIF, weight="bold", fill=INK)
    t += txt(88, 366, l2, size=62, font=SERIF, weight="bold", fill=MOSS)
    t += para(88, 428, [sub1, sub2], size=19, fill=INK2, lh=30)
    groups.append(grp("cover-title", 88, 150, 730, 330, t))

    m = ln(88, 600, 818, 600, LINE)
    m += para(88, 636, [meta1, meta2], size=14, fill=INK2, lh=26)
    groups.append(grp("cover-meta", 88, 580, 730, 100, m))

    p = icon(ic, 950, 250, 120, WHITE, sw=1.1)
    p += txt(950, 448, badge_label, size=15, font=MONO, fill=WHITE, ls=3)
    p += txt(950, 488, badge_sub, size=24, font=SERIF, fill=WHITE)
    groups.append(grp("cover-panel", 918, 0, 342, 720, p))

    deco = [
        rect(0, 0, 14, H, MOSS, role="decoration", eid="deco-spine"),
        rect(880, 0, 400, H, MOSS, role="decoration", eid="deco-panel"),
        rect(0, 0, W, 6, BRASS, role="decoration", eid="deco-top"),
    ]
    return svg_page(role, groups, deco)


def toc(title, items, right, page, total, deck_label):
    """items: list of (num, title, pg)."""
    groups = [header("DAFTAR ISI", title, right)]
    col_w = 552
    for ci in range(2):
        cx = M + ci * (col_w + 32)
        chunk = items[ci * 5: ci * 5 + 5]
        inner = ""
        ty = 250
        for (num, t, pg) in chunk:
            inner += txt(cx, ty, num, size=15, font=MONO, fill=MOSS, ls=1)
            inner += txt(cx + 54, ty, t, size=19, fill=INK)
            inner += ln(cx + 54, ty + 12, cx + col_w - 60, ty + 12, LINE)
            inner += txt(cx + col_w, ty, pg, size=15, font=MONO, fill=INK2, anchor="end")
            ty += 56
        groups.append(grp("toc-%d" % ci, cx, 220, col_w, 300, inner))
    groups.append(grp("toc-note", M, 560, CW, 88,
                      note(M, 560, CW, 88, "Setiap bagian berdiri sendiri. Buka halaman "
                           "yang Anda butuhkan tanpa harus membaca berurutan.")))
    groups.append(footer(deck_label, page, total))
    return svg_page("toc", groups)


# ---------------------------------------------------------------- USER DECK

def user_pages():
    pages = []

    # 01 cover
    pages.append(("01_cover", cover(
        "PANDUAN PENGGUNA  ·  VERSI 1.0",
        "Buku Panduan", "Perpustakaan",
        "Langkah demi langkah menelusuri katalog,",
        "mengajukan reservasi, dan memantau peminjaman.",
        "SISTEM INFORMASI PERPUSTAKAAN", "EDISI 2026  ·  UNTUK ANGGOTA",
        "UNTUK ANGGOTA", "Panduan lengkap", "books")))

    # 02 daftar isi
    pages.append(("02_daftar_isi", toc(
        "Daftar Isi",
        [("01", "Mengenal Sistem", "03"),
         ("02", "Membuat Akun", "04"),
         ("03", "Masuk & Keluar", "05"),
         ("04", "Menelusuri Katalog", "06"),
         ("05", "Detail Buku & Nomor Panggil", "07"),
         ("06", "Mengajukan Reservasi", "08"),
         ("07", "Memantau Reservasi & Email", "09"),
         ("08", "Dasbor & Riwayat Pinjam", "10"),
         ("09", "Profil & Keamanan Akun", "11"),
         ("10", "Aturan Penting & Tips", "12")],
        "PANDUAN PENGGUNA", 2, USER_TOTAL, "Perpustakaan · Panduan Pengguna")))

    # 03 mengenal sistem
    g = [header("BAGIAN 01", "Mengenal Sistem", "PANDUAN PENGGUNA")]
    cw = 360
    g.append(grp("c1", M, 200, cw, 200, card_icon(
        M, 200, cw, 200, "book", "Satu pintu",
        ["Telusuri koleksi, ajukan", "reservasi, dan pantau pinjaman", "dalam satu akun."])))
    g.append(grp("c2", M + cw + 28, 200, cw, 200, card_icon(
        M + cw + 28, 200, cw, 200, "users", "Untuk siapa",
        ["Anggota memakai katalog dan", "reservasi. Petugas mengelola", "buku dan laporan."])))
    g.append(grp("c3", M + 2 * (cw + 28), 200, cw, 200, card_icon(
        M + 2 * (cw + 28), 200, cw, 200, "clipboard", "Aturan dasar",
        ["Maksimal 3 buku aktif,", "masa pinjam 14 hari,", "denda Rp1.000 per hari."])))
    g.append(grp("n1", M, 440, CW, 86, note(
        M, 440, CW, 86, "Panduan ini untuk anggota perpustakaan. Panduan khusus "
        "petugas tersedia pada dokumen terpisah.")))
    sw2 = (CW - 2 * 28) // 3
    g.append(grp("s1", M, 558, sw2, 86, stat(M, 558, sw2, 86, "14 hari", "MASA PINJAM")))
    g.append(grp("s2", M + sw2 + 28, 558, sw2, 86, stat(
        M + sw2 + 28, 558, sw2, 86, "3 buku", "MAKSIMUM AKTIF", MOSS2)))
    g.append(grp("s3", M + 2 * (sw2 + 28), 558, sw2, 86, stat(
        M + 2 * (sw2 + 28), 558, sw2, 86, "Rp1.000", "DENDA PER HARI", BRASS)))
    g.append(footer("Perpustakaan · Panduan Pengguna", 3, USER_TOTAL))
    pages.append(("03_mengenal_sistem", svg_page("content", g)))

    # 04 membuat akun
    g = [header("BAGIAN 02", "Membuat Akun", "PANDUAN PENGGUNA")]
    steps = [
        (1, "Buka halaman Daftar", "Klik tombol Daftar di kanan atas halaman beranda."),
        (2, "Isi formulir pendaftaran", "Masukkan nama lengkap, alamat email, dan kata sandi."),
        (3, "Kirim formulir", "Tekan tombol Daftar untuk membuat akun Anda."),
        (4, "Akun siap dipakai", "Anda otomatis masuk dan bisa menelusuri katalog."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Gunakan email aktif — email dipakai untuk notifikasi "
        "reservasi dan pengingat.")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 4, USER_TOTAL))
    pages.append(("04_membuat_akun", svg_page("content", g)))

    # 05 masuk & keluar
    g = [header("BAGIAN 03", "Masuk & Keluar", "PANDUAN PENGGUNA")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 300, bullets_card(
        M, 200, cw, 300, "login", "Masuk ke akun",
        ["Buka halaman Masuk.", "Isi email dan kata sandi.",
         "Tekan tombol Masuk.", "Anda diarahkan ke dasbor."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 300, bullets_card(
        M + cw + 32, 200, cw, 300, "logout", "Keluar dengan aman",
        ["Klik nama Anda di kanan atas.", "Pilih Keluar pada menu.",
         "Sesi berakhir di perangkat ini.", "Selalu keluar di komputer bersama."])))
    g.append(grp("n1", M, 540, CW, 80, note(
        M, 540, CW, 80, "Lupa kata sandi? Hubungi petugas perpustakaan untuk "
        "mengatur ulang akun Anda.", BRASS, "key")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 5, USER_TOTAL))
    pages.append(("05_masuk_keluar", svg_page("content", g)))

    # 06 menelusuri katalog
    g = [header("BAGIAN 04", "Menelusuri Katalog", "PANDUAN PENGGUNA")]
    cw = 360
    for i, (ic, t, ls) in enumerate([
        ("search", "Pencarian", ["Ketik judul, penulis, atau", "ISBN pada kolom pencarian", "di halaman Katalog."]),
        ("filter", "Indeks kategori", ["Pilih kategori pada indeks", "untuk mempersempit hasil.", "Pilih Semua untuk melihat semuanya."]),
        ("list", "Urutkan hasil", ["Urutkan berdasarkan judul,", "penulis, atau tahun terbit", "sesuai kebutuhan."]),
    ]):
        x = M + i * (cw + 28)
        g.append(grp("c%d" % i, x, 200, cw, 200, card_icon(x, 200, cw, 200, ic, t, ls)))
    cw2 = 552
    g.append(grp("d1", M, 440, cw2, 200, bullets_card(
        M, 440, cw2, 200, "file-text", "Hasil pencarian",
        ["Kartu menampilkan judul, penulis,", "kategori, dan ketersediaan stok.",
         "Klik kartu untuk membuka detail."])))
    g.append(grp("d2", M + cw2 + 32, 440, cw2, 200, bullets_card(
        M + cw2 + 32, 440, cw2, 200, "circle-check", "Status ketersediaan",
        ["Tersedia: siap dipinjam.", "Habis: ajukan reservasi",
         "untuk masuk antrean."])))
    g.append(footer("Perpustakaan · Panduan Pengguna", 6, USER_TOTAL))
    pages.append(("06_menelusuri_katalog", svg_page("content", g)))

    # 07 detail buku & nomor panggil
    g = [header("BAGIAN 05", "Detail Buku & Nomor Panggil", "PANDUAN PENGGUNA")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 300, bullets_card(
        M, 200, cw, 300, "book", "Informasi pada halaman buku",
        ["Judul dan penulis.", "Kategori dan tahun terbit.", "ISBN buku.",
         "Nomor panggil.", "Jumlah stok tersedia."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 300, bullets_card(
        M + cw + 32, 200, cw, 300, "bookmark", "Membaca nomor panggil",
        ["Terdiri dari 3 huruf kategori.", "Diikuti titik pemisah.",
         "Lalu nomor urut buku, contoh FIK.014.", "Dipakai menemukan buku di rak."])))
    g.append(grp("n1", M, 540, CW, 80, note(
        M, 540, CW, 80, "Nomor panggil tampil pada halaman detail buku dan "
        "membantu Anda menemukan buku di rak.", MOSS, "bookmark")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 7, USER_TOTAL))
    pages.append(("07_detail_buku", svg_page("content", g)))

    # 08 mengajukan reservasi
    g = [header("BAGIAN 06", "Mengajukan Reservasi", "PANDUAN PENGGUNA")]
    steps = [
        (1, "Pilih buku", "Buka halaman detail buku yang ingin Anda pinjam."),
        (2, "Klik Reservasi Buku Ini", "Tombol tersedia pada halaman detail buku."),
        (3, "Reservasi tercatat", "Bila stok habis, Anda masuk antrean reservasi."),
        (4, "Tunggu konfirmasi", "Anda menerima email saat buku siap diambil."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Bila stok habis, reservasi masuk antrean. Buku ditahan "
        "3 hari setelah tersedia.", BRASS, "hourglass")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 8, USER_TOTAL))
    pages.append(("08_mengajukan_reservasi", svg_page("content", g)))

    # 09 memantau reservasi & email
    g = [header("BAGIAN 07", "Memantau Reservasi & Email", "PANDUAN PENGGUNA")]
    cw = 552
    items = [
        ("clock", "Menunggu", ["Buku sedang dipinjam anggota lain.", "Anda berada dalam antrean."], MOSS),
        ("circle-check", "Siap Diambil", ["Buku siap diambil.", "Ambil sebelum batas penahanan 3 hari."], MOSS2),
        ("check", "Sudah Diambil", ["Buku sudah Anda pinjam.", "Reservasi ditutup otomatis."], MOSS),
        ("x", "Dibatalkan", ["Dibatalkan petugas atau kedaluwarsa.", "Anda bisa mengajukan lagi."], RUST),
    ]
    pos = [(M, 200), (M + cw + 32, 200), (M, 400), (M + cw + 32, 400)]
    for i, (ic, t, ls, ac) in enumerate(items):
        x, y = pos[i]
        g.append(grp("r%d" % i, x, y, cw, 170, card_icon(x, y, cw, 170, ic, t, ls, ac)))
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Email pemberitahuan dikirim otomatis setiap kali "
        "status reservasi berubah.", MOSS, "mail")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 9, USER_TOTAL))
    pages.append(("09_memantau_reservasi", svg_page("content", g)))

    # 10 dasbor & riwayat
    g = [header("BAGIAN 08", "Peminjaman Saya", "PANDUAN PENGGUNA")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 300, bullets_card(
        M, 200, cw, 300, "chart-bar", "Ringkasan pinjaman",
        ["Buku yang sedang dipinjam.", "Tanggal jatuh tempo tiap buku.",
         "Peringatan buku terlambat.", "Status reservasi aktif."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 300, bullets_card(
        M + cw + 32, 200, cw, 300, "clock", "Riwayat peminjaman",
        ["Daftar pinjaman lampau.", "Tanggal pinjam dan kembali.",
         "Denda yang tercatat, bila ada."])))
    g.append(grp("n1", M, 540, CW, 80, note(
        M, 540, CW, 80, "Kembalikan buku sebelum jatuh tempo untuk menghindari "
        "denda Rp1.000 per hari.", RUST, "alert-triangle")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 10, USER_TOTAL))
    pages.append(("10_dasbor_riwayat", svg_page("content", g)))

    # 11 profil & keamanan
    g = [header("BAGIAN 09", "Profil & Keamanan Akun", "PANDUAN PENGGUNA")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 300, bullets_card(
        M, 200, cw, 300, "user", "Perbarui profil",
        ["Ubah nama lengkap.", "Perbarui alamat email.",
         "Simpan perubahan."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 300, bullets_card(
        M + cw + 32, 200, cw, 300, "lock", "Ganti kata sandi",
        ["Masukkan kata sandi lama.", "Tulis kata sandi baru yang kuat.",
         "Konfirmasi lalu simpan."])))
    g.append(grp("n1", M, 540, CW, 80, note(
        M, 540, CW, 80, "Gunakan kata sandi unik dan jangan bagikan kepada "
        "siapa pun, termasuk petugas.", MOSS, "shield-lock")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 11, USER_TOTAL))
    pages.append(("11_profil_keamanan", svg_page("content", g)))

    # 12 aturan & tips (ending)
    g = [header("BAGIAN 10", "Aturan Penting & Tips", "PANDUAN PENGGUNA")]
    sw2 = (CW - 2 * 28) // 3
    g.append(grp("s1", M, 200, sw2, 110, stat(M, 200, sw2, 110, "14 hari", "MASA PINJAM")))
    g.append(grp("s2", M + sw2 + 28, 200, sw2, 110, stat(
        M + sw2 + 28, 200, sw2, 110, "3 buku", "MAKSIMUM AKTIF", MOSS2)))
    g.append(grp("s3", M + 2 * (sw2 + 28), 200, sw2, 110, stat(
        M + 2 * (sw2 + 28), 200, sw2, 110, "Rp1.000", "DENDA PER HARI", BRASS)))
    cw = 552
    g.append(grp("c1", M, 350, cw, 200, bullets_card(
        M, 350, cw, 200, "circle-check", "Kebiasaan baik",
        ["Catat tanggal jatuh tempo.", "Kembalikan tepat waktu.",
         "Ajukan reservasi bila stok habis."])))
    g.append(grp("c2", M + cw + 32, 350, cw, 200, bullets_card(
        M + cw + 32, 350, cw, 200, "help", "Butuh bantuan?",
        ["Hubungi meja sirkulasi perpustakaan.",
         "Sebutkan judul atau nomor panggil buku.",
         "Petugas akan membantu Anda."])))
    g.append(grp("n1", M, 570, CW, 74, note(
        M, 570, CW, 74, "Terima kasih telah memakai perpustakaan digital kami. "
        "Selamat membaca!", MOSS, "book")))
    g.append(footer("Perpustakaan · Panduan Pengguna", 12, USER_TOTAL))
    pages.append(("12_aturan_tips", svg_page("ending", g)))

    return pages


# --------------------------------------------------------------- ADMIN DECK

def admin_pages():
    pages = []

    # 01 cover
    pages.append(("01_cover", cover(
        "PANDUAN PETUGAS  ·  VERSI 1.0",
        "Buku Panduan", "Perpustakaan",
        "Panduan lengkap mengelola koleksi, peminjaman,",
        "reservasi, dan laporan sirkulasi.",
        "SISTEM INFORMASI PERPUSTAKAAN", "EDISI 2026  ·  UNTUK PETUGAS",
        "UNTUK PETUGAS", "Panduan operasional", "settings")))

    # 02 daftar isi
    pages.append(("02_daftar_isi", toc(
        "Daftar Isi",
        [("01", "Masuk & Dasbor Petugas", "03"),
         ("02", "Mengelola Kategori", "04"),
         ("03", "Menambah Buku", "05"),
         ("04", "Mengubah & Menghapus Buku", "06"),
         ("05", "Mencatat Peminjaman", "07"),
         ("06", "Memproses Pengembalian", "08"),
         ("07", "Mengelola Antrean Reservasi", "09"),
         ("08", "Laporan Sirkulasi", "10"),
         ("09", "Ekspor Data CSV", "11"),
         ("10", "Notifikasi Keterlambatan", "12")],
        "PANDUAN PETUGAS", 2, ADMIN_TOTAL, "Perpustakaan · Panduan Petugas")))

    # 03 masuk & dasbor
    g = [header("BAGIAN 01", "Masuk & Dasbor Petugas", "PANDUAN PETUGAS")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 260, bullets_card(
        M, 200, cw, 260, "login", "Masuk sebagai petugas",
        ["Buka halaman Masuk.", "Isi email dan kata sandi petugas.",
         "Tekan Masuk.", "Menu Admin muncul di navigasi."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 260, bullets_card(
        M + cw + 32, 200, cw, 260, "chart-bar", "Isi dasbor",
        ["Jumlah buku dan anggota.", "Peminjaman aktif hari ini.",
         "Buku terlambat.", "Reservasi menunggu."])))
    sw2 = (CW - 2 * 28) // 3
    g.append(grp("s1", M, 500, sw2, 110, stat(M, 500, sw2, 110, "Buku", "KELOLA KOLEKSI")))
    g.append(grp("s2", M + sw2 + 28, 500, sw2, 110, stat(
        M + sw2 + 28, 500, sw2, 110, "Pinjam", "CATAT TRANSAKSI", MOSS2)))
    g.append(grp("s3", M + 2 * (sw2 + 28), 500, sw2, 110, stat(
        M + 2 * (sw2 + 28), 500, sw2, 110, "Laporan", "EKSPOR DATA", BRASS)))
    g.append(footer("Perpustakaan · Panduan Petugas", 3, ADMIN_TOTAL))
    pages.append(("03_masuk_dasbor", svg_page("content", g)))

    # 04 mengelola kategori
    g = [header("BAGIAN 02", "Mengelola Kategori", "PANDUAN PETUGAS")]
    steps = [
        (1, "Buka menu Kategori", "Tersedia di bilah navigasi area Admin."),
        (2, "Tambah kategori", "Isi nama kategori, lalu simpan."),
        (3, "Ubah kategori", "Klik Ubah pada baris kategori."),
        (4, "Hapus kategori", "Hapus hanya bila tidak dipakai buku."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Tiga huruf pertama nama kategori menjadi awalan nomor "
        "panggil buku.", BRASS, "tag")))
    g.append(footer("Perpustakaan · Panduan Petugas", 4, ADMIN_TOTAL))
    pages.append(("04_mengelola_kategori", svg_page("content", g)))

    # 05 menambah buku
    g = [header("BAGIAN 03", "Menambah Buku", "PANDUAN PETUGAS")]
    steps = [
        (1, "Buka menu Buku", "Pilih Tambah Buku pada halaman daftar buku."),
        (2, "Isi data buku", "Judul, penulis, kategori, tahun, ISBN, dan stok."),
        (3, "Simpan buku", "Buku muncul di katalog dan siap dipinjam."),
        (4, "Periksa nomor panggil", "Sistem membentuk nomor panggil otomatis."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "ISBN harus unik. Sistem menolak ISBN yang sudah "
        "terdaftar pada buku lain.", RUST, "alert-triangle")))
    g.append(footer("Perpustakaan · Panduan Petugas", 5, ADMIN_TOTAL))
    pages.append(("05_menambah_buku", svg_page("content", g)))

    # 06 mengubah & menghapus buku
    g = [header("BAGIAN 04", "Mengubah & Menghapus Buku", "PANDUAN PETUGAS")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 300, bullets_card(
        M, 200, cw, 300, "edit", "Mengubah data buku",
        ["Buka detail buku di menu Buku.", "Klik Ubah, lalu sunting data.",
         "Simpan perubahan.", "Katalog ikut diperbarui."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 300, bullets_card(
        M + cw + 32, 200, cw, 300, "trash", "Menghapus buku",
        ["Klik Hapus pada baris buku.", "Konfirmasi penghapusan.",
         "Riwayat pinjaman lama tetap tersimpan."], RUST)))
    g.append(grp("n1", M, 540, CW, 80, note(
        M, 540, CW, 80, "Untuk menyesuaikan koleksi tanpa menghapus, cukup ubah "
        "jumlah stok buku.", MOSS, "adjustments")))
    g.append(footer("Perpustakaan · Panduan Petugas", 6, ADMIN_TOTAL))
    pages.append(("06_ubah_hapus_buku", svg_page("content", g)))

    # 07 mencatat peminjaman
    g = [header("BAGIAN 05", "Mencatat Peminjaman", "PANDUAN PETUGAS")]
    steps = [
        (1, "Buka menu Peminjaman", "Pilih Tambah Peminjaman."),
        (2, "Pilih anggota dan buku", "Cari anggota, lalu pilih buku yang tersedia."),
        (3, "Tetapkan tanggal", "Jatuh tempo otomatis 14 hari dari tanggal pinjam."),
        (4, "Klik Catat Peminjaman", "Stok berkurang dan tercatat pada anggota."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Satu anggota maksimal 3 buku aktif. Sistem menolak "
        "peminjaman bila melebihi.", RUST, "alert-triangle")))
    g.append(footer("Perpustakaan · Panduan Petugas", 7, ADMIN_TOTAL))
    pages.append(("07_mencatat_peminjaman", svg_page("content", g)))

    # 08 memproses pengembalian
    g = [header("BAGIAN 06", "Memproses Pengembalian", "PANDUAN PETUGAS")]
    steps = [
        (1, "Buka menu Peminjaman", "Cari pinjaman yang akan dikembalikan."),
        (2, "Klik Kembalikan", "Tombol Kembalikan ada pada baris pinjaman."),
        (3, "Periksa denda", "Denda Rp1.000 per hari bila lewat jatuh tempo."),
        (4, "Simpan", "Stok bertambah dan antrean reservasi diproses."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Bila ada antrean reservasi, buku otomatis dialihkan ke "
        "pemesan berikutnya.", MOSS, "refresh")))
    g.append(footer("Perpustakaan · Panduan Petugas", 8, ADMIN_TOTAL))
    pages.append(("08_pengembalian", svg_page("content", g)))

    # 09 mengelola reservasi & antrean
    g = [header("BAGIAN 07", "Mengelola Antrean Reservasi", "PANDUAN PETUGAS")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 300, bullets_card(
        M, 200, cw, 300, "circle-check", "Proses pengambilan",
        ["Buka menu Antrean.", "Pilih reservasi berstatus Siap Diambil.",
         "Klik Proses Ambil untuk membuat peminjaman.", "Buku berpindah ke anggota."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 300, bullets_card(
        M + cw + 32, 200, cw, 300, "x", "Batalkan reservasi",
        ["Pilih reservasi yang dibatalkan.", "Klik Batal dan konfirmasi.",
         "Antrean berikutnya otomatis naik."], RUST)))
    g.append(grp("n1", M, 540, CW, 80, note(
        M, 540, CW, 80, "Saat buku dikembalikan, sistem otomatis mempromosikan "
        "pemesan berikutnya ke status Tersedia.", MOSS, "refresh")))
    g.append(footer("Perpustakaan · Panduan Petugas", 9, ADMIN_TOTAL))
    pages.append(("09_reservasi_antrean", svg_page("content", g)))

    # 10 laporan sirkulasi
    g = [header("BAGIAN 08", "Laporan Sirkulasi", "PANDUAN PETUGAS")]
    cw = 552
    g.append(grp("c1", M, 200, cw, 160, bullets_card(
        M, 200, cw, 160, "filter", "Filter laporan",
        ["Pilih jenis laporan.", "Isi tanggal awal dan akhir."])))
    g.append(grp("c2", M + cw + 32, 200, cw, 160, bullets_card(
        M + cw + 32, 200, cw, 160, "list", "Jenis laporan",
        ["Peminjaman, buku, dan reservasi.",
         "Angka ringkas tampil di atas tabel."])))
    cols = [("ANGGOTA", 0.34), ("BUKU", 0.34), ("PINJAM", 0.16), ("JATUH TEMPO", 0.16)]
    rows = [
        ["Budi Santoso", "Filsafat Ilmu", "01 Feb 2026", "15 Feb 2026"],
        ["Siti Aminah", "Sejarah Nusantara", "03 Feb 2026", "17 Feb 2026"],
        ["Rangga Putra", "Dasar Pemrograman", "05 Feb 2026", "19 Feb 2026"],
    ]
    g.append(grp("tbl", M, 400, CW, 226, table(M, 400, CW, cols, rows)))
    g.append(footer("Perpustakaan · Panduan Petugas", 10, ADMIN_TOTAL))
    pages.append(("10_laporan_sirkulasi", svg_page("content", g)))

    # 11 ekspor CSV
    g = [header("BAGIAN 09", "Ekspor Data CSV", "PANDUAN PETUGAS")]
    steps = [
        (1, "Buka menu Laporan", "Pilih jenis laporan yang ingin diekspor."),
        (2, "Atur rentang tanggal", "Isi tanggal awal dan tanggal akhir."),
        (3, "Klik Ekspor CSV", "Berkas langsung diunduh ke perangkat Anda."),
        (4, "Buka di pengolah angka", "Berkas CSV bisa dibuka di Excel atau Sheets."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Tersedia tiga jenis ekspor: peminjaman, buku, dan "
        "reservasi.", MOSS, "file-download")))
    g.append(footer("Perpustakaan · Panduan Petugas", 11, ADMIN_TOTAL))
    pages.append(("11_ekspor_csv", svg_page("content", g)))

    # 12 notifikasi keterlambatan
    g = [header("BAGIAN 10", "Notifikasi Keterlambatan", "PANDUAN PETUGAS")]
    steps = [
        (1, "Notifikasi otomatis", "Sistem memeriksa pinjaman terlambat tiap hari pukul 07.00."),
        (2, "Email terkirim", "Anggota menerima email pengingat keterlambatan."),
        (3, "Sekali kirim", "Setiap pinjaman hanya dinotifikasi satu kali."),
        (4, "Pantau di dasbor", "Buku terlambat tampil menonjol di dasbor."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 86, step_row(M, y, CW, 86, n, t, d)))
        y += 98
    g.append(grp("n1", M, 596, CW, 52, note(
        M, 596, CW, 52, "Pastikan pengaturan email server benar agar notifikasi "
        "terkirim tepat waktu.", BRASS, "bell")))
    g.append(footer("Perpustakaan · Panduan Petugas", 12, ADMIN_TOTAL))
    pages.append(("12_notifikasi_keterlambatan", svg_page("content", g)))

    # 13 rutinitas harian
    g = [header("BAGIAN 11", "Rutinitas Harian Petugas", "PANDUAN PETUGAS")]
    steps = [
        (1, "Pagi: periksa dasbor", "Lihat pinjaman jatuh tempo dan buku terlambat hari ini."),
        (2, "Proses pengembalian", "Tandai buku yang dikembalikan hari ini."),
        (3, "Penuhi reservasi", "Alihkan buku tersedia ke pemesan berikutnya."),
        (4, "Layani peminjaman baru", "Catat peminjaman untuk anggota."),
        (5, "Akhir hari: cek laporan", "Pastikan seluruh transaksi hari ini tercatat."),
    ]
    y = 190
    for i, (n, t, d) in enumerate(steps):
        g.append(grp("st%d" % i, M, y, CW, 76, step_row(M, y, CW, 76, n, t, d)))
        y += 86
    g.append(footer("Perpustakaan · Panduan Petugas", 13, ADMIN_TOTAL))
    pages.append(("13_rutinitas_harian", svg_page("content", g)))

    # 14 penutup
    g = [header("BAGIAN 12", "Catatan Penting & Penutup", "PANDUAN PETUGAS")]
    sw2 = (CW - 2 * 28) // 3
    g.append(grp("s1", M, 200, sw2, 110, stat(M, 200, sw2, 110, "3 buku", "MAKSIMUM AKTIF")))
    g.append(grp("s2", M + sw2 + 28, 200, sw2, 110, stat(
        M + sw2 + 28, 200, sw2, 110, "14 hari", "MASA PINJAM", MOSS2)))
    g.append(grp("s3", M + 2 * (sw2 + 28), 200, sw2, 110, stat(
        M + 2 * (sw2 + 28), 200, sw2, 110, "07.00", "NOTIFIKASI HARIAN", BRASS)))
    cw = 552
    g.append(grp("c1", M, 350, cw, 200, bullets_card(
        M, 350, cw, 200, "clipboard", "Ingat selalu",
        ["Jaga akurasi data buku dan anggota.",
         "Periksa antrean reservasi setiap hari.",
         "Ekspor laporan berkala untuk arsip."])))
    g.append(grp("c2", M + cw + 32, 350, cw, 200, bullets_card(
        M + cw + 32, 350, cw, 200, "help", "Bantuan teknis",
        ["Catat pesan galat bila muncul.",
         "Hubungi administrator sistem.",
         "Sertakan langkah yang Anda lakukan."])))
    g.append(grp("n1", M, 570, CW, 74, note(
        M, 570, CW, 74, "Terima kasih atas kerja Anda menjaga koleksi tetap "
        "tertata dan mudah diakses.", MOSS, "book")))
    g.append(footer("Perpustakaan · Panduan Petugas", 14, ADMIN_TOTAL))
    pages.append(("14_penutup", svg_page("ending", g)))

    return pages


def write_deck(outdir, pages):
    os.makedirs(outdir, exist_ok=True)
    for name, svg in pages:
        with open(os.path.join(outdir, name + ".svg"), "w", encoding="utf-8") as f:
            f.write(svg)
    print("wrote %d pages -> %s" % (len(pages), outdir))


if __name__ == "__main__":
    base = "/home/agentuser/workspace/ppt-master/projects"
    write_deck(os.path.join(base, "panduan_user_20261011", "svg_output"), user_pages())
    write_deck(os.path.join(base, "panduan_admin_20261011", "svg_output"), admin_pages())
