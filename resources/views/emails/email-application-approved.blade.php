{{-- resources/views/emails/email-application-approved.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Akaun Emel ICT Diluluskan</title>
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

        .approval-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #d4edda;
            /* Light green background */
            border: 1px solid #c3e6cb;
            /* Green border */
            color: #155724;
            /* Dark green text */
            border-radius: 5px;
        }

        .button {
            display: inline-block;
            background-color: #28a745;
            /* Success green button */
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
        <h1>Notifikasi Permohonan Akaun Emel ICT</h1>

        {{-- Access the $emailApplication data passed from the Mailable --}}
        <p>Salam sejahtera {{ $emailApplication->user->name ?? 'Pemohon' }},</p> {{-- Assuming user relationship with 'name' --}}

        <p>Merujuk kepada permohonan Akaun Emel / ID Pengguna ICT MOTAC anda dengan nombor rujukan
            <strong>#{{ $emailApplication->id }}</strong>.</p>

        <p>Sukacita dimaklumkan bahawa permohonan anda telah <strong>Diluluskan</strong>.</p>

        {{-- Display details if the email has been provisioned --}}
        @if ($emailApplication->status === 'completed' && $emailApplication->final_assigned_email)
            <div class="approval-details">
                <p><strong>Maklumat Akaun E-mel Anda:</strong></p>
                <p>E-mel Rasmi MOTAC: <strong>{{ $emailApplication->final_assigned_email }}</strong></p>
                <p>ID Pengguna:
                    <strong>{{ $emailApplication->final_assigned_user_id ?? 'Sila rujuk e-mel berasingan atau hubungi BPM ICT' }}</strong>
                </p>
                <p>Kata Laluan Awal: <strong>Sila rujuk e-mel berasingan atau hubungi BPM ICT</strong></p>
                <p class="mt-3">Anda kini boleh log masuk ke akaun e-mel rasmi MOTAC anda.</p>
            </div>
        @elseif ($emailApplication->status === 'approved')
            {{-- Status is approved, but provisioning might happen later --}}
            <div class="approval-details">
                <p>Permohonan anda telah diluluskan dan sedang dalam proses penyediaan akaun e-mel.</p>
                <p>Anda akan dimaklumkan semula setelah akaun e-mel anda berjaya disediakan.</p>
            </div>
        @endif


        {{-- Optional: Link to view application details --}}
        {{-- Assuming a route named 'email-applications.show' exists and is accessible to the user --}}
        {{-- @if (isset($applicationUrl))
            <p style="text-align: center;">
                <a href="{{ $applicationUrl }}" class="button">Lihat Butiran Permohonan</a>
            </p>
        @endif --}}

        <p>Jika anda mempunyai sebarang pertanyaan, sila hubungi bahagian BPM ICT.</p>

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
