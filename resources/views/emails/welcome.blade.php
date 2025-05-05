{{-- resources/views/emails/welcome.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Selamat Datang ke MOTAC ICT - Akaun E-mel Anda Disediakan</title> {{-- Updated title --}}
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

        .credentials {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
            /* Highlight with a color bar */
        }

        .credentials p {
            margin-bottom: 8px;
        }

        .credentials strong {
            display: inline-block;
            /* Ensure bold text doesn't break line before value */
            min-width: 120px;
            /* Align labels */
        }
    </style>
</head>

<body>
    <div class="email-container">
        {{-- Updated Title to reflect email provisioning --}}
        <h1>Selamat Datang ke MOTAC ICT!</h1>

        {{-- Access the $user data passed from the Mailable --}}
        {{-- Use full_name if available, fallback to name --}}
        <p>Salam sejahtera {{ $user->full_name ?? ($user->name ?? 'Pengguna') }},</p>

        {{-- Updated content to inform about email provisioning --}}
        <p>Akaun e-mel rasmi MOTAC ICT anda telah berjaya disediakan.</p>

        {{-- Display the provisioned email address and initial password --}}
        <div class="credentials">
            <p><strong>Alamat E-mel:</strong> {{ $motacEmail }}</p>
            <p><strong>Kata Laluan Awal:</strong> {{ $password }}</p>
        </div>

        <p>Sila log masuk ke webmail MOTAC di [Alamat Webmail Anda] dan tukar kata laluan anda dengan segera untuk
            keselamatan akaun anda.</p>

        {{-- Optional: Add a login button or link to webmail --}}
        {{-- You might pass the webmail URL via the Mailable if it's dynamic --}}
        <p style="text-align: center;">
            <a href="[Alamat Webmail Anda]" class="button">Log Masuk Webmail MOTAC</a>
        </p>


        <p>Jika anda mempunyai sebarang pertanyaan atau menghadapi masalah log masuk, sila hubungi Unit Sokongan
            Teknikal ICT MOTAC.</p>

        <p>Terima kasih,</p>
        <p>Unit ICT MOTAC</p> {{-- Changed sender to reflect ICT team --}}

        <div class="footer">
            <p>Ini adalah e-mel automatik dari Sistem HRMS MOTAC, sila jangan balas.</p> {{-- Mention HRMS system origin --}}
            <p>&copy; {{ date('Y') }} MOTAC. Hak Cipta Terpelihara.</p>
        </div>
    </div>
</body>

</html>
