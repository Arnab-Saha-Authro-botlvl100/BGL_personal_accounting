<style>
    .custom-btn {
        font-weight: bold;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    .custom-btn-primary {
        background-color: #007bff;
        color: white;
    }
    .custom-btn-primary:hover {
        background-color: #0056b3;
    }
    .custom-btn-dark {
        background-color: #343a40;
        color: white;
    }
    .custom-btn-dark:hover {
        background-color: #23272b;
    }
</style>
<div class="d-flex justify-content-between align-item-center gap-3 shadow-lg p-5">
           
    <button class="custom-btn custom-btn-primary" onclick="printDiv('printable-part')">Print</button>
    <button class="custom-btn custom-btn-dark" onclick="window.history.back();">GO BACK</button>

</div>
<div class="container-fluid shadow-lg p-5 m-4" id="printable-part"> 
    <h2 class="text-center font-weight-bold text-xl my-4 text-dark">Receive/Payment Report</h2>

    <div class="d-flex justify-content-between mb-4">
        <div class="text-lg">
            <h3 class="font-semibold text-dark">Company Name: {{ Auth::user()->name }}</h3>
            <p><span class="font-semibold">Period Date:</span> {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</p>
            <p><span class="font-semibold">Type/Customer Name:</span> {{ $type }} </p>
        </div>
        <div class="text-lg text-right">
            <h3 class="font-semibold text-dark">Email: {{ Auth::user()->email }}<br>Phone: {{ Auth::user()->phone }}</h3>
            <p><span class="font-semibold">Address:</span> {{ Auth::user()->address }}</p>
        </div>
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
                        @if ($transaction->receive_type == 'customer')
                            <strong>{{ $transaction->customer_name ?? 'N/A' }}</strong><br>
                        @endif
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
</div>
<script>
    function printDiv(divId) {
        var printContent = document.getElementById(divId).innerHTML; // Get the content of the div
        var originalContent = document.body.innerHTML; // Save the original content
    
        // Create a new window for printing
        var printWindow = window.open('', '', 'height=600,width=800');
    
        // Write the content and styles to the new window
        printWindow.document.write('<html><head><title>Print</title>');
    
        // Copy all styles from the current document (or you can include specific styles)
        var styles = '';
        var styleSheets = document.styleSheets;
        for (var i = 0; i < styleSheets.length; i++) {
            try {
                var rules = styleSheets[i].cssRules || styleSheets[i].rules;
                for (var j = 0; j < rules.length; j++) {
                    styles += rules[j].cssText;
                }
            } catch (e) {
                // Handle cross-origin stylesheets, if necessary
            }
        }
    
        // Include the styles inside the print window
        printWindow.document.write('<style>' + styles + '</style>');
    
        printWindow.document.write('</head><body>');
        printWindow.document.write(printContent); // Insert the content of the div
        printWindow.document.write('</body></html>');
    
        // Wait for the content to load and then trigger the print dialog
        printWindow.document.close(); // Close the document for further editing
        printWindow.focus(); // Focus on the window before printing
        printWindow.print(); // Trigger the print dialog
        printWindow.close(); // Close the print window after printing
    }
</script>
