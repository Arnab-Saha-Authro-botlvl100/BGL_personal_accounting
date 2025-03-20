<div class="mb-3">
    {{-- <h5 class="fw-bold">Opening Balance:</h5> --}}
    {{-- <p class="mb-0">
        @if ($type === 'agent')
            <span class="text-success">Debit: {{ number_format($opening_balance, 2) }}</span>
        @elseif ($type === 'supplier')
            <span class="text-danger">Credit: {{ number_format($opening_balance, 2) }}</span>
        @else
            <span>N/A</span>
        @endif
    </p> --}}
</div>

<table class="table table-bordered">
    <thead class="table-dark text-center">
        <tr>
            <th>SL</th>
            <th>Date</th>
            <th>Description</th>
            <th>Receive (Cash In)</th>
            <th>Payment (Cash Out)</th>
            <th>Balance</th>
        </tr>
    </thead>
    <tbody>
        @php
            $sl = 1;
            $totalDebit = 0;
            $totalCredit = 0;
            $balance = 0;
        @endphp

     
        @foreach ($transactions as $transaction)
            <tr>
                <td>{{ $sl++ }}</td>
                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                <td>
                    <strong>{{ $transaction->customer_name ?? 'N/A' }}</strong><br>
                    Type: {{ $transaction->receive_type ?? 'N/A' }}<br>
                    Method: {{ $transaction->transaction_method == 'bank' ? 'Bank' : 'Cash' }}<br>
                    @if ($transaction->transaction_method == 'bank')
                        @php
                            $bank = $banks->where('id', $transaction->bank_name)->first();
                        @endphp
                        Bank: {{ $bank->bank_name ?? 'N/A' }}<br>
                        Account: {{ $bank->account_number ?? 'N/A' }}<br>
                        Branch: {{ $bank->branch_name ?? 'N/A' }}
                    @endif
                
                </td>

                @if ($transaction instanceof \App\Models\Receive) {{-- Checking if it's a Receive entry --}}
                    <td class="text-success">
                        {{ number_format($transaction->amount, 2) }}
                        @php $totalDebit += $transaction->amount; $balance += $transaction->amount @endphp
                    </td>
                    <td>-</td>
                @elseif ($transaction instanceof \App\Models\Payment) {{-- Otherwise, it's a Payment entry --}}
                    <td>-</td>
                    <td class="text-danger">
                        {{ number_format($transaction->amount, 2) }}
                        @php $totalCredit += $transaction->amount; $balance -= $transaction->amount @endphp
                    </td>
                @endif
                    <td>{{ $balance }}</td>
            </tr>
        @endforeach


        <!-- Totals Row -->
        <tr class="table-secondary text-center fw-bold">
            <td colspan="3">Total</td>
            <td class="text-success">{{ number_format($totalDebit, 2) }}</td>
            <td class="text-danger">{{ number_format($totalCredit, 2) }}</td>
            <td class="text-dark"><strong>{{ number_format($balance, 2) }}</strong></td>
        </tr>
    </tbody>
</table>