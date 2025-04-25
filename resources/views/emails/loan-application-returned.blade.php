{{-- resources/views/emails/loan-application-returned.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peralatan Pinjaman ICT Telah Dipulangkan</title>
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

        .return-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #e9d8fd;
            /* Light purple background */
            border: 1px solid #d6bcfa;
            /* Purple border */
            color: #6b46c1;
            /* Dark purple text */
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1>Notifikasi Pulangan Peralatan Pinjaman ICT</h1>

        {{-- Access the $loanApplication and $loanTransaction data passed from the Mailable --}}
        <p>Salam sejahtera {{ $loanApplication->user->name ?? 'Pemohon' }},</p> {{-- Assuming user relationship with 'name' --}}

        <p>Merujuk kepada permohonan Pinjaman Peralatan ICT anda dengan nombor rujukan
            <strong>#{{ $loanApplication->id }}</strong>.</p>

        <p>Sukacita dimaklumkan bahawa peralatan berikut telah berjaya <strong>Dipulangkan</strong> dan direkodkan.</p>

        <div class="return-details">
            <p><strong>Butiran Pulangan Peralatan:</strong></p>

            <table>
                <thead>
                    <tr>
                        <th>Peralatan (Tag ID)</th>
                        <th>Status Pulangan</th>
                        <th>Tarikh Dipulangkan</th>
                        <th>Diterima Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Display details of the specific transaction that was just returned --}}
                    <tr>
                        <td>
                            {{ $loanTransaction->equipment->brand ?? 'N/A' }}
                            {{ $loanTransaction->equipment->model ?? 'N/A' }}
                            (Tag: {{ $loanTransaction->equipment->tag_id ?? 'N/A' }})
                        </td>
                        <td>{{ ucfirst(str_replace('_', ' ', $loanTransaction->status)) }}</td> {{-- Display the transaction status --}}
                        <td>{{ $loanTransaction->return_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                        <td>{{ $loanTransaction->returnAcceptingOfficer->name ?? 'N/A' }}</td> {{-- Assuming returnAcceptingOfficer relationship --}}
                    </tr>
                </tbody>
            </table>

            <p class="mt-3">Aksesori yang Dipulangkan:
                {{ implode(', ', json_decode($loanTransaction->accessories_checklist_on_return, true) ?? []) }}</p>
            <p>Catatan Pulangan: {{ $loanTransaction->return_notes ?? '-' }}</p>

            {{-- Optional: Add a note if the equipment was marked as damaged or lost --}}
            @if ($loanTransaction->status === 'damaged')
                <p class="mt-3 font-semibold text-red-700">Peralatan ini direkodkan sebagai ROSAK semasa pulangan.</p>
            @elseif ($loanTransaction->status === 'lost')
                <p class="mt-3 font-semibold text-red-700">Peralatan ini direkodkan sebagai HILANG semasa pulangan.</p>
            @endif

        </div>

        <p>Jika anda mempunyai sebarang pertanyaan mengenai pulangan peralatan ini, sila hubungi bahagian BPM ICT.</p>

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
