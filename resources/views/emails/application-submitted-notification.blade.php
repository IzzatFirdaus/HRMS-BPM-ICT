{{-- resources/views/emails/application-submitted-notification.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tindakan Diperlukan: Permohonan Baru Dihantar</title>
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

        .notification-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #fff3cd;
            /* Light yellow background */
            border: 1px solid #ffeeba;
            /* Yellow border */
            color: #856404;
            /* Dark yellow text */
            border-radius: 5px;
        }

        .button {
            display: inline-block;
            background-color: #007bff;
            /* Primary blue button */
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
        <h1>Pemberitahuan Permohonan Baru</h1>

        {{-- Access the $application data passed from the Mailable --}}
        <p>Salam sejahtera,</p>

        <p>Terdapat permohonan baru yang memerlukan tindakan kelulusan anda dalam Sistem HRMS MOTAC.</p>

        <div class="notification-details">
            <p><strong>Butiran Permohonan:</strong></p>
            <p>Nombor Rujukan: <strong>#{{ $application->id }}</strong></p>
            <p>Jenis Permohonan:
                @if ($application instanceof \App\Models\EmailApplication)
                    Permohonan Akaun Emel ICT
                @elseif ($application instanceof \App\Models\LoanApplication)
                    Permohonan Pinjaman Peralatan ICT
                @else
                    Jenis Tidak Diketahui
                @endif
            </p>
            <p>Pemohon: {{ $application->user->name ?? 'N/A' }}</p> {{-- Assuming user relationship with 'name' --}}
            <p>Tarikh Hantar: {{ $application->created_at?->format('Y-m-d H:i') ?? 'N/A' }}</p>

            {{-- Link to the approval show page for this specific approval task --}}
            {{-- This requires finding the specific Approval record for this application and the recipient officer --}}
            {{-- You might need to pass the Approval ID or a URL directly from the Mailable --}}
            {{-- Example assuming you pass a $reviewUrl variable from the Mailable --}}
            @if (isset($reviewUrl))
                <p style="text-align: center; margin-top: 20px;">
                    <a href="{{ $reviewUrl }}" class="button">Semak Permohonan</a>
                </p>
            @else
                {{-- Fallback text if no direct review URL is provided --}}
                <p class="mt-3">Sila log masuk ke Sistem HRMS MOTAC untuk menyemak permohonan ini.</p>
            @endif

        </div>

        <p>Sila semak permohonan ini dan ambil tindakan yang sewajarnya.</p>

        <p>Terima kasih.</p>

        <p>Yang benar,</p>
        <p>Sistem HRMS MOTAC</p>

        <div class="footer">
            <p>Ini adalah e-mel automatik, sila jangan balas.</p>
            <p>&copy; {{ date('Y') }} MOTAC. Hak Cipta Terpelihara.</p>
        </div>
    </div>
</body>

</html>
