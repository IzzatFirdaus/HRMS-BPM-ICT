{{-- resources/views/emails/loan-application-overdue-reminder.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan: Pinjaman Peralatan ICT Lewat Dipulangkan</title>
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

        .overdue-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #fef3c7;
            /* Light yellow background */
            border: 1px solid #fde68a;
            /* Yellow border */
            color: #b45309;
            /* Dark yellow text */
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

        .text-danger {
            color: #dc3545;
            /* Bootstrap danger red */
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <h1>Peringatan Pinjaman Peralatan ICT</h1>

        {{-- Access the $loanApplication data passed from the Mailable --}}
        <p>Salam sejahtera {{ $loanApplication->user->name ?? 'Pemohon' }},</p> {{-- Assuming user relationship with 'name' --}}

        <p>Merujuk kepada permohonan Pinjaman Peralatan ICT anda dengan nombor rujukan
            <strong>#{{ $loanApplication->id }}</strong>.</p>

        <p>Rekod kami menunjukkan bahawa peralatan yang dipinjam di bawah permohonan ini telah <strong>Lewat
                Dipulangkan</strong>.</p>

        <div class="overdue-details">
            <p><strong>Butiran Pinjaman:</strong></p>
            <p>Tujuan Permohonan: {{ $loanApplication->purpose ?? 'N/A' }}</p>
            <p>Tarikh Dijangka Pulang: <span
                    class="text-danger">{{ $loanApplication->loan_end_date?->format('Y-m-d') ?? 'N/A' }}</span></p>

            {{-- Display the list of issued items that are currently overdue --}}
            {{-- Assuming the LoanApplication model has a 'transactions' relationship,
                 and you filter/load only the 'overdue' ones when sending this email --}}
            @if ($loanApplication->transactions->isNotEmpty())
                <p class="mt-3">Peralatan yang Lewat Dipulangkan:</p>
                <table>
                    <thead>
                        <tr>
                            <th>Peralatan (Tag ID)</th>
                            <th>Tarikh Dikeluarkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loanApplication->transactions as $transaction)
                            {{-- Only show transactions that are marked as 'overdue' for this application --}}
                            {{-- You might need to filter this collection in your Mailable or controller --}}
                            @if ($transaction->status === 'overdue')
                                <tr>
                                    <td>
                                        {{ $transaction->equipment->brand ?? 'N/A' }}
                                        {{ $transaction->equipment->model ?? 'N/A' }}
                                        (Tag: {{ $transaction->equipment->tag_id ?? 'N/A' }})
                                    </td>
                                    <td>{{ $transaction->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Tiada butiran peralatan lewat dipulangkan direkodkan untuk permohonan ini.</p>
            @endif

            <p class="mt-3">Sila pulangkan peralatan tersebut ke bahagian BPM ICT dengan kadar segera.</p>
        </div>

        <p>Jika anda telah memulangkan peralatan tersebut baru-baru ini, sila abaikan e-mel ini atau hubungi kami untuk
            pengesahan.</p>

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
