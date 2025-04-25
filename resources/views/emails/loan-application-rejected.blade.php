{{-- resources/views/emails/loan-application-rejected.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Pinjaman Peralatan ICT Ditolak</title>
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

        .rejection-reason {
            margin-top: 20px;
            padding: 15px;
            background-color: #f8d7da;
            /* Light red background */
            border: 1px solid #f5c6cb;
            /* Red border */
            color: #721c24;
            /* Dark red text */
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1>Notifikasi Permohonan Pinjaman Peralatan ICT</h1>

        {{-- Access the $loanApplication data passed from the Mailable --}}
        <p>Salam sejahtera {{ $loanApplication->user->name ?? 'Pemohon' }},</p> {{-- Assuming user relationship with 'name' --}}

        <p>Merujuk kepada permohonan Pinjaman Peralatan ICT anda dengan nombor rujukan
            <strong>#{{ $loanApplication->id }}</strong>.</p>

        <p>Dukacita dimaklumkan bahawa permohonan anda telah <strong>Ditolak</strong>.</p>

        {{-- Display the rejection reason if available --}}
        @if ($loanApplication->rejection_reason)
            <div class="rejection-reason">
                <p><strong>Sebab Penolakan:</strong></p>
                <p>{{ $loanApplication->rejection_reason }}</p>
            </div>
        @endif

        <p>Untuk maklumat lanjut, sila hubungi bahagian BPM ICT.</p>

        <p>Terima kasih atas kerjasama anda.</p>

        <p>Yang benar,</p>
        <p>Pasukan BPM ICT MOTAC</p>

        <div class="footer">
            <p>Ini adalah e-mel automatik, sila jangan balas.</p>
            <p>&copy; {{ date('Y') }} MOTAC. Hak Cipta Terpelihara.</p>
        </div>
    </div>
</body>

</html>
