@extends('mail.layout')

@section('body')
    <p style="margin:0 0 14px; font-size:16px; color:#201D16;">Halo {{ $name }},</p>

    <p style="margin:0 0 20px; font-size:14px; line-height:1.6; color:#4E483A;">
        Kabar baik — buku yang Anda reservasi kini <strong style="color:#2C4A39;">tersedia</strong>
        dan sedang ditahan untuk Anda.
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #D6CEB8; background-color:#F4F0E4; margin-bottom:20px;">
        <tr>
            <td style="padding:16px 18px; border-bottom:1px solid #D6CEB8;">
                <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:#8B8269;">Judul</div>
                <div style="font-size:16px; font-weight:600; color:#201D16; margin-top:4px;">{{ $bookTitle }}</div>
                <div style="font-size:13px; color:#4E483A; margin-top:2px;">{{ $bookAuthor }}</div>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 18px; background-color:#DDE6DC;">
                <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:#1B3125;">Ambil Sebelum</div>
                <div style="font-size:18px; font-weight:600; color:#1B3125; margin-top:4px;">
                    {{ $expiresAt ? $expiresAt->translatedFormat('d F Y') : '—' }}
                </div>
                <div style="font-size:11px; color:#1B3125; margin-top:2px;">
                    Ditahan {{ $holdDays }} hari sejak pemberitahuan ini.
                </div>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:14px; line-height:1.6; color:#4E483A;">
        Silakan datang ke meja sirkulasi perpustakaan untuk mengambil buku. Bila lewat batas waktu,
        reservasi akan otomatis dibatalkan dan buku ditawarkan ke anggota berikutnya.
        Nomor anggota Anda: <strong style="color:#201D16;">{{ $memberCode }}</strong>.
    </p>
@endsection
