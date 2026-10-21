{{-- Layout email bergaya arsip: paper, hairline, mono label. Inline styles untuk klien mail. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $subjectLine ?? config('app.name', 'Perpustakaan') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#E8E2CF; font-family: Georgia, 'Times New Roman', serif; color:#201D16;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#E8E2CF; padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#FBF9F2; border:1px solid #D6CEB8;">
                    {{-- Brass cap --}}
                    <tr>
                        <td style="height:4px; background-color:#96742A; line-height:4px; font-size:0;">&nbsp;</td>
                    </tr>
                    {{-- Masthead --}}
                    <tr>
                        <td style="padding:20px 28px; border-bottom:1px solid #D6CEB8;">
                            <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.18em; text-transform:uppercase; color:#8B8269;">
                                {{ $eyebrow ?? 'Perpustakaan · Pemberitahuan' }}
                            </div>
                            <div style="font-size:20px; font-weight:600; color:#201D16; margin-top:6px;">
                                {{ config('app.name', 'Perpustakaan') }}
                            </div>
                        </td>
                    </tr>
                    {{-- Body --}}
                    <tr>
                        <td style="padding:28px;">
                            @yield('body')
                        </td>
                    </tr>
                    {{-- Footer --}}
                    <tr>
                        <td style="padding:18px 28px; border-top:1px solid #D6CEB8; background-color:#F4F0E4;">
                            <div style="font-family:'Courier New', monospace; font-size:10px; letter-spacing:0.14em; text-transform:uppercase; color:#8B8269;">
                                Sistem Sirkulasi &middot; {{ date('Y') }}
                            </div>
                            <div style="font-family:'Courier New', monospace; font-size:10px; color:#8B8269; margin-top:4px;">
                                Email otomatis — mohon tidak dibalas.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
