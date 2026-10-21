@extends('mail.layout')

@section('body')
    <p style="margin:0 0 14px; font-size:16px; color:#201D16;">Halo {{ $name }},</p>

    <p style="margin:0 0 20px; font-size:14px; line-height:1.6; color:#4E483A;">
        Catatan peminjaman Anda menunjukkan buku berikut <strong style="color:#9C3D2A;">belum dikembalikan</strong>
        dan sudah melewati tanggal jatuh tempo.
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
            <td style="padding:0;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                        <td width="50%" style="padding:14px 18px; border-right:1px solid #D6CEB8;">
                            <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:#8B8269;">Jatuh Tempo</div>
                            <div style="font-size:14px; color:#201D16; margin-top:4px;">{{ $dueAt->translatedFormat('d F Y') }}</div>
                        </td>
                        <td width="50%" style="padding:14px 18px;">
                            <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:#8B8269;">Terlambat</div>
                            <div style="font-size:14px; color:#9C3D2A; font-weight:600; margin-top:4px;">{{ $daysLate }} hari</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="padding:14px 18px; border-top:1px solid #D6CEB8; background-color:#F0DAD3;">
                <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.16em; text-transform:uppercase; color:#7A2E1F;">Denda Berjalan</div>
                <div style="font-size:18px; font-weight:600; color:#7A2E1F; margin-top:4px;">Rp {{ number_format($fine, 0, ',', '.') }}</div>
                <div style="font-size:11px; color:#7A2E1F; margin-top:2px;">Rp 1.000 per hari keterlambatan</div>
            </td>
        </tr>
    </table>

    <p style="margin:0; font-size:14px; line-height:1.6; color:#4E483A;">
        Mohon segera mengembalikan buku ke meja sirkulasi perpustakaan agar denda tidak terus bertambah.
        Nomor anggota Anda: <strong style="color:#201D16;">{{ $memberCode }}</strong>.
    </p>
@endsection
