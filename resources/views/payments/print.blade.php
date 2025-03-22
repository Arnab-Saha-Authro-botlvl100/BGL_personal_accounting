<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            text-align: center;
        }
        .receipt-container {
            width: 400px;
            background: #fff;
            padding: 20px;
            margin: auto;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: left;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            color: #007bff;
        }
        .company-info {
            font-size: 14px;
            color: #555;
        }
        .receipt-details {
            margin: 20px 0;
            font-size: 16px;
        }
        .receipt-details p {
            margin: 8px 0;
            display: flex;
            justify-content: space-between;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #ff2600;
        }
        .footer {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
            border-top: 2px dashed #ddd;
            padding-top: 10px;
            color: #818181;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 48%;
            text-align: center;
        }
        .signature-box p {
            margin: 5px 0;
            font-weight: 100;
        }
        .sig-line {
            display: block;
            width: 100%;
            border-top: 2px dashed #000;
            margin: 10px 0;
        }
        .btn-print {
            display: block;
            width: 100%;
            background: #4784fff4;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn-print:hover {
            background: #0056b3;
        }
        @media print {
            .btn-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h2>Payment Receipt</h2>
            <p class="company-info">
                {{ config('app.name') }} <br>
                Address: {{ Auth::user()->address }} <br>
                Email: {{ Auth::user()->email }} | Phone: {{ Auth::user()->phone }}
            </p>
        </div>

        <div class="receipt-details">
            <p><strong>Date:</strong> <span>{{ date('d-m-Y', strtotime($payment->date)) }}</span></p>
            <p><strong>Receipt No:</strong> <span>#{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</span></p>
            <p><strong>Customer Name:</strong> <span>{{ $payment->customer_name }}</span></p>
            <p><strong>Transaction Method:</strong> <span>{{ ucfirst($payment->transaction_method) }}</span></p>
            @if($payment->transaction_method === 'bank')
                @php
                    $bankName = DB::table('transactions')->where('id', $payment->bank_name)->value('bank_name');
                @endphp
                <p><strong>Bank Name:</strong> <span>{{ $bankName ?? 'N/A' }}</span></p>
                <p><strong>Account No:</strong> <span>{{ $payment->account_number ?? 'N/A' }}</span></p>
                <p><strong>Branch Name:</strong> <span>{{ $payment->branch_name ?? 'N/A' }}</span></p>
            @endif
        
            <p class="amount"><strong>Amount:</strong> <span>{{ number_format($payment->amount, 2) }} BDT</span></p>
            <p><strong>Note:</strong> <span>{{ $payment->note ?: 'N/A' }}</span></p>
        </div>

        <!-- Two Signature Sections -->
        <div class="signature-section">
            <!-- Left: Authorized Signature -->
            <div class="signature-box">
                <p>Authorized Signature</p>
                <span class="sig-line"></span>
                <p>{{ Auth::user()->name }}</p>
            </div>

            <!-- Right: paymentr's Signature -->
            <div class="signature-box">
                <p>Paymentr's Signature</p>
                <span class="sig-line"></span>
                <p></p>
            </div>
        </div>

        <div class="footer">
            <p>Thank you for your payment!</p>
            <p><strong>{{ config('app.name') }}</strong></p>
        </div>

        <button class="btn-print" onclick="window.print();">Print Receipt</button>
    </div>
</body>
</html>
