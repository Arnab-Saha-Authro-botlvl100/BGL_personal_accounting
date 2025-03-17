<!doctype html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        clifford: '#da373d',
                    }
                }
            }
        }
    </script>
    <style>
        .hide-scroll-bar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .hide-scroll-bar::-webkit-scrollbar {
            display: none;
        }
    </style>
    <style>
        @media (max-width: 768px) {
            .table-responsive table {
                font-size: 12px;
                /* Adjust font size for small screens */
            }
        }
    </style>
</head>

<body class="flex">

    <main class="flex-1 mx-auto max-w-7xl px-10">
        <!-- <div class="buttons justify-end flex gap-3 shadow-lg p-5 ">
      <button class="text-white bg-pink-600 font-bold text-md py-2 px-4">Send</button>
      <button class="text-white bg-blue-700 font-bold text-md py-2 px-4">Print</button>
      <button class="text-white bg-green-600 font-bold text-md py-2 px-4 ">ADD NEW INVOICE</button>
      <button class="text-white bg-black font-bold text-md py-2 px-4">GO BACK</button>
   </div> -->
        <div class="container-fluid">
            <h2 class="text-center font-bold text-3xl my-2">Cash Book</h2>
            <div class="flex items-center justify-between mb-2">
                <div class="text-lg">
                    <h2 class="font-semibold">Company Name : {{ Auth::user()->name }}</h2>
                    <p><span class="font-semibold">Period Date :</span> {{ $start_date }} to {{ $end_date }} </p>
                </div>
                <div class="flex items-center">


                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Transaction Method</th>
                            <th>Receive</th>
                            <th>Payment</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-right table-warning">
                            <td colspan="5" class="text-end"><strong>Opening Balance:</strong></td>
                            <td class="text-start"><strong> {{ number_format($opening_balance, 2) }}</strong></td>
                        </tr>
                        @php
                            $currentBalance = $opening_balance; // Start with the opening balance
                        @endphp
                        @foreach ($mergedData as $transaction)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                <td>{{ $transaction->note }}</td>
                                <td>
                                    @if ($transaction->transaction_method == 'cash')
                                        Cash
                                    @else
                                        {{ $getBankName($transaction->bank_name) ?? 'Unknown Bank' }}
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction instanceof App\Models\Receive)
                                        {{ number_format($transaction->amount, 2) }}
                                        @php
                                            $currentBalance += $transaction->amount; // Add to balance
                                        @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction instanceof App\Models\Payment)
                                        {{ number_format($transaction->amount, 2) }}
                                        @php
                                            $currentBalance -= $transaction->amount; // Subtract from balance
                                        @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    {{ number_format($currentBalance, 2) }} <!-- Display current balance -->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class=" table-secondary">
                            <th colspan="3" class="text-end">Closing Balance</th>
                            <th>{{ number_format($totalDebit, 2) }}</th>
                            <th>{{ number_format($totalCredit, 2) }}</th>
                            <th>{{ number_format($currentBalance, 2) }} </th>
                        </tr>
                    </tfoot>
                </table>
            </div>



        </div>


    </main>

</body>

</html>
