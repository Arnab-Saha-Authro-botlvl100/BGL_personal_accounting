<?php

namespace App\Http\Controllers;

use App\Models\Receive;
use App\Models\Transaction;
use App\Models\Customer; // Assuming you have a Customer model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReceiveController extends Controller
{
    // Show the form for creating a new receive transaction
    public function create()
    {
        if(Auth::user()){
            $customers = Customer::all(); // Fetch all customers
            return view('receives.create', compact('customers')); // Adjust the view path as needed
        }
        else{
            return redirect()->route('register');
        }
      
    }

    public function store(Request $request)
    {
        if(Auth::user()){
            // dd($request->all());
        // Validate the request
        $request->validate([
            'date' => 'required|date_format:d/m/Y', // Accepts dd/mm/yyyy format
            'receive_type' => 'required|in:customer,others',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'contract_invoice' => 'nullable|string|max:255',
            'receive_amount' => 'nullable|string|max:255',
            'transaction_method' => 'required|in:cash,bank',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:20',
            'branch_name' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:500',
        ]);
    
        // Initialize customer variable
        $customer = null;
    
        // Get customer ID and new payment amount
        $customerId = $request->customer_id;
        $newReceiveAmount = $request->amount;
        $agentContact = 0;
        $totalPaid = $newTotalPaid = 0;
    
        $formattedDate = \Carbon\Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d');
        // dd($formattedDate);
        if ($customerId) {
            // Get total amount paid by this customer
            $totalPaid = Receive::where('customer_id', $customerId)->sum('amount');
    
            // Get customer's supplier contract amount
            $customer = Customer::find($customerId);
            if (!$customer) {
                return response()->json(['error' => 'Customer not found.'], 400);
            }
    
            $agentContact = $customer->agent_contract;
            $newTotalPaid = $totalPaid + $newReceiveAmount;
        } else {
            $newTotalPaid = $newReceiveAmount;
        }
    
        // Add authenticated user's ID
        $request->merge(['user' => Auth::id()]);
        // $request->date = $formattedDate;
        try {
            // Create the receive transaction
            // $receive = Receive::create($request->all());
            $receive = Receive::create(array_merge($request->except('date'), ['date' => $formattedDate]));

            // Prepare clipboard text safely
            $clipboardText = "Total Received Amount: $newTotalPaid\nCustomer: " . optional($customer)->name;
    
            // Store clipboard text in session
            session()->flash('clipboard_text', $clipboardText);
    
            // Prepare the response
            $response = [
                'success' => 'Transaction received successfully.',
            ];
    
            // If the receive_type is 'others', redirect to the index route
            if ($request->receive_type === 'others') {
                return response()->json([
                    'success' => $response['success'],
                    'redirect_url' => route('receives.index'),
                ]);
            }
    
            // For 'customer', redirect to receipt page
            $response['redirect_url'] = route('receives.index');
    
            // Return the response as JSON
            return response()->json($response);
    
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to receive. Please try again. ' . $e->getMessage()], 500);
        }
        }
        else{
            return redirect()->route('register');
        }
    }
    
    

    // Display a listing of the receives
    public function index()
    {
        if(Auth::user()){
              // $receives = Receive::all(); // Fetch all receive transactions
            $receives = Receive::where('user', Auth::id())
            ->orderBy('created_at', 'DESC') // Correct case
            ->get();
            
            $customers = Customer::where('customers.user', Auth::id())
                        ->where('customers.is_delete', 0)
                        ->where('customers.is_active', 1)
                        ->join('contracts', 'customers.contract_id', '=', 'contracts.id')
                        ->leftjoin('agents', 'customers.agent', '=', 'agents.id')
                        ->select('customers.name', 'customers.customer_id','customers.id', 'contracts.invoice_no', 'customers.agent_contract', 'agents.name as agent_name')
                        ->get();
            $banks = Transaction::where([
                            ['is_delete', 0],
                            ['transaction_type', 'bank'],
                            ['user', Auth::id()]
                        ])->get();
            // dd($receives);
            return view('receives.index', compact('receives', 'customers', 'banks')); // Adjust the view path as needed
        }
        else{
            return redirect()->route('register');
        }
      
    }

    // Show the form for editing the specified receive transaction
    public function edit(Receive $receive)
    {
        if(Auth::user()){
            $customers = Customer::all(); // Fetch all customers
            return view('receives.edit', compact('receive', 'customers')); // Adjust the view path as needed
        }
       else{
          return redirect()->route('register');
       }
    }

    // Update the specified receive transaction in storage
    public function update(Request $request, $id)
    {
        if(Auth::user()){
            // dd($request->all(), $id);
            // Validate the request
            $request->validate([
                'date' => 'required|date',
                
                'amount' => 'required|numeric|min:0',
                'note' => 'nullable|string|max:500',
            ]);

            $receive = Receive::where('id', $id)->first();

            // Update the receive transaction
            $receive->update($request->all());

            return redirect()->route('receives.index')->with('success', 'Receive updated successfully.');
        }
        else{
            return redirect()->route('register');
        }
        
    }

    // Remove the specified receive transaction from storage
    public function destroy($id, Request $request)
    {
        if(Auth::user()){
             // dd($id, $request->all());
            $receive = Receive::where('id', $id)->first();
            $receive->delete();
            return redirect()->route('receives.index')->with('success', 'Transaction deleted successfully.');
        }
        else{
            return redirect()->route('register');
        }
       
    }

    
    public function getDueAmount($customer_id)
    {
        // Fetch the total amount paid by the customer
        $totalPaid = Receive::where('customer_id', $customer_id)->sum('amount');
    
        // Fetch the customer details
        $customer = Customer::find($customer_id);
    
        // Check if the customer exists
        if (!$customer) {
            return response()->json([
                'error' => 'Customer not found',
            ], 404);
        }
    
        // Fetch the supplier contract amount
        $agentContact = $customer->agent_contract;
    
        // Calculate the due amount
        $dueAmount = $agentContact - $totalPaid;
    
        // Format the due amount to 2 decimal places
        $formattedDueAmount = number_format($dueAmount, 2);
    
        // Return the due amount as a JSON response
        return response()->json([
            'due_amount' => $formattedDueAmount,
        ]);
    }

    public function receipt($customer_id, $receive_id){
        
        // Fetch customer details
        $customer = Customer::findOrFail($customer_id);

        // Get the latest payment for the customer
        $latestReceive = Receive::where('customer_id', $customer_id)->latest()->first();

        // Get the total paid amount by the customer
        $totalPaid = Receive::where('customer_id', $customer_id)->sum('amount');

        // Calculate the remaining amount
        $remaining = (int) $customer->agent_contract - $totalPaid;

        return view('receives.receipt', compact('customer', 'latestReceive', 'totalPaid', 'remaining'));
    }

    public function print($id)
    {
        $receive = Receive::findOrFail($id);
        return view('receives.print', compact('receive'));
    }

}