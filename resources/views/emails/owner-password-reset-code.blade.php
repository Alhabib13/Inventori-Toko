<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kode Reset Kata Sandi Owner</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.6;">
    <p>Halo {{ $ownerName }},</p>

    <p>
        Kami menerima permintaan reset kata sandi untuk akun owner
        @if (filled($storeName))
            toko <strong>{{ $storeName }}</strong>
        @endif.
    </p>

    <p>Kode verifikasi kamu:</p>

    <p style="font-size: 28px; font-weight: bold; letter-spacing: 0.2em; margin: 12px 0;">
        {{ $code }}
    </p>

    <p>Kode ini berlaku selama {{ $expiresInMinutes }} menit.</p>

    <p>Kalau kamu tidak merasa meminta reset kata sandi, abaikan email ini.</p>
</body>
</html>
