{{-- resources/views/emails/welcome.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang ke Sistem HRMS MOTAC</title>
    <style>
        /* Basic inline styles for email compatibility */
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1,
        h2 {
            color: #333;
            margin-top: 0;
        }

        p {
            margin-bottom: 15px;
        }

        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #666;
            border-top: 1px solid #eee;
            padding-top: 15px;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            /* Example blue button */
            color: #fff !important;
            /* Important to override mail client styles */
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1>Selamat Datang ke Sistem HRMS MOTAC!</h1>

        {{-- Access the $user data passed from the Mailable --}}
        <p>Salam sejahtera {{ $user->name ?? 'Pengguna Baru' }},</p> {{-- Assuming 'name' is the common attribute --}}

        <p>Kami sangat gembira anda telah menyertai sistem HRMS MOTAC.</p>

        <p>Anda kini boleh log masuk menggunakan kredensial pendaftaran anda.</p>

        {{-- Optional: Add a login button or link --}}
        {{-- @if (isset($loginUrl))
            <p style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Log Masuk Sekarang</a>
            </p>
        @endif --}}

        <p>Jika anda mempunyai sebarang pertanyaan, sila hubungi bahagian Sumber Manusia atau sokongan IT.</p>

        <p>Terima kasih,</p>
        <p>Pasukan HRMS MOTAC</p>

        <div class="footer">
            <p>Ini adalah e-mel automatik, sila jangan balas.</p>
            <p>&copy; {{ date('Y') }} MOTAC. Hak Cipta Terpelihara.</p>
        </div>
    </div>
</body>

</html>
