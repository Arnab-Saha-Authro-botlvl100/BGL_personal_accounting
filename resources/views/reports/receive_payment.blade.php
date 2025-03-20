<x-app-layout>
    <style type="text/css">
        select option {
            font-weight: normal;
        }

        select option::after {
            content: " " attr(data-id)!important;
            font-weight: bold!important;
        }

    </style>
    @include('layouts.links')
    <div class="container-fluid" id="main-content" style="transition: 0.3s;">
        <div class="mt-4 mx-auto px-2" style="width: 100%;">
            <div class="container" id="initial-div">
                <form id="cashbookForm">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label for="type" class="form-label">Select Customer <span
                                    class="text-danger">*</span></label>
                            <select class="form-control" id="agent_supplier" name="type" required>
                                <option value="">-- Select Customer --</option>
                                <option value="others">Others</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}" data-id="{{ $customer->customer_id }}">
                                        {{ $customer->name }}
                                    </option>
                                @endforeach


                            </select>
                        </div>

                        <div class="col-md-5" id="dynamicSelectContainer">
                            <!-- Dynamic select options will be loaded here -->
                        </div>
                    </div>

                    <div class="row g-3 align-items-end mt-2">
                        <div class="col-md-5">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date">
                        </div>

                        <div class="col-md-5">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date">
                        </div>

                        <div class="col-md-2 text-end">
                            <button type="submit" class="btn btn-primary w-100">Submit</button>
                        </div>
                    </div>
                </form>
                <div id="reportdiv">
                    <div class="table-div mt-4 container">
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
                        
                    </div>
                </div>
            </div>
        </div>

       
    </div>

    <!-- jQuery (Ensure jQuery is included) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('agent_supplier');
            const dynamicSelectContainer = document.getElementById('dynamicSelectContainer');

            // Sample data for agents and suppliers (replace with your compacted data)
            const agents = {!! json_encode($agents) !!}; // Ensure $agents is passed from the backend
            const suppliers = {!! json_encode($suppliers) !!}; // Ensure $suppliers is passed from the backend

            typeSelect.addEventListener('change', function() {
                const selectedType = typeSelect.value;
            

                // Clear the dynamic select container
                dynamicSelectContainer.innerHTML = '';

                if (selectedType === 'agent') {
                    // Create a select dropdown for agents
                    const agentSelect = document.createElement('select');
                    agentSelect.className = 'form-control';
                    agentSelect.name = 'agent_id';
                    agentSelect.innerHTML = '<option value="">-- Select Agent --</option>';

                    agents.forEach(agent => {
                        agentSelect.innerHTML +=
                            `<option value="${agent.id}">${agent.name}</option>`;
                    });

                    dynamicSelectContainer.appendChild(agentSelect);
                } else if (selectedType === 'supplier') {
                    // Create a select dropdown for suppliers
                    const supplierSelect = document.createElement('select');
                    supplierSelect.className = 'form-control';
                    supplierSelect.name = 'supplier_id';
                    supplierSelect.innerHTML = '<option value="">-- Select Supplier --</option>';

                    suppliers.forEach(supplier => {
                        supplierSelect.innerHTML +=
                            `<option value="${supplier.id}">${supplier.name}</option>`;
                    });

                    dynamicSelectContainer.appendChild(supplierSelect);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $("#cashbookForm").on("submit", function(e) {
                e.preventDefault(); // Prevent default form submission
                $.ajax({
                    url: "{{ route('report.receive_payment.report') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        $("#reportdiv").empty().html(response.html);
                    },
                    error: function(xhr) {
                        alert("Something went wrong. Please try again.");
                    }
                });
            });

        });
    </script>
</x-app-layout>
