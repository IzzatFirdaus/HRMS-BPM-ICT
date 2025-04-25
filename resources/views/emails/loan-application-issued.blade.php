{{-- resources/views/emails/loan-application-issued.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peralatan Pinjaman ICT Telah Dikeluarkan</title>
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

        .issued-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #e2f4f8;
            /* Light blue/cyan background */
            border: 1px solid #bae6fd;
            /* Cyan border */
            color: #0e7490;
            /* Dark cyan text */
            border-radius: 5px;
        }

        .item-list {
            margin-top: 15px;
            margin-bottom: 15px;
            padding-left: 20px;
        }

        .item-list li {
            margin-bottom: 5px;
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
        <h1>Notifikasi Pengeluaran Peralatan Pinjaman ICT</h1>

        {{-- Access the $loanApplication data passed from the Mailable --}}
        <p>Salam sejahtera {{ $loanApplication->user->name ?? 'Pemohon' }},</p> {{-- Assuming user relationship with 'name' --}}

        <p>Merujuk kepada permohonan Pinjaman Peralatan ICT anda dengan nombor rujukan
            <strong>#{{ $loanApplication->id }}</strong>.</p>

        <p>Sukacita dimaklumkan bahawa peralatan yang diluluskan untuk permohonan anda telah
            <strong>Dikeluarkan</strong>.</p>

        <div class="issued-details">
            <p><strong>Butiran Peralatan yang Dikeluarkan:</strong></p>

            {{-- Display the list of issued transactions for this application --}}
            {{-- Assuming the LoanApplication model has a 'transactions' relationship,
                 and you filter/load only the 'issued' ones when sending this email --}}
            @if ($loanApplication->transactions->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th>Peralatan (Tag ID)</th>
                            <th>Aksesori Dikeluarkan</th>
                            <th>Tarikh Dikeluarkan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loanApplication->transactions as $transaction)
                            {{-- Only show transactions that are marked as 'issued' for this application --}}
                            {{-- You might need to filter this collection in your Mailable or controller --}}
                            @if ($transaction->status === 'issued')
                                <tr>
                                    <td>
                                        {{ $transaction->equipment->brand ?? 'N/A' }}
                                        {{ $transaction->equipment->model ?? 'N/A' }}
                                        (Tag: {{ $transaction->equipment->tag_id ?? 'N/A' }})
                                    </td>
                                    <td>{{ implode(', ', json_decode($transaction->accessories_checklist_on_issue, true) ?? []) }}
                                    </td>
                                    <td>{{ $transaction->issue_timestamp?->format('Y-m-d H:i') ?? 'N/A' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>Tiada butiran peralatan dikeluarkan direkodkan untuk permohonan ini.</p>
            @endif

            <p class="mt-3">Sila pastikan peralatan dipulangkan pada atau sebelum tarikh jangkaan pulangan:
                <strong>{{ $loanApplication->loan_end_date?->format('Y-m-d') ?? 'N/A' }}</strong>.</p>
        </div>


        {{-- Optional: Link to view application details --}}
        {{-- Assuming a route named 'loan-applications.show' exists and is accessible to the user --}}
        {{-- @if (isset($applicationUrl))
            <p style="text-align: center;">
                <a href="{{ $applicationUrl }}" class="button">Lihat Butiran Permohonan</a>
            </p>
        @endif --}}

        <p>Jika anda mempunyai sebarang pertanyaan mengenai peralatan yang dikeluarkan, sila hubungi bahagian BPM ICT.
        </p>

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
