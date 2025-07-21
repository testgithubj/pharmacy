<?php

namespace App\Http\Controllers;
use App\Models\Method;
use App\Models\Banks;
use App\Models\Banktrans;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class BanktransController extends Controller
{
    public function create()
{
    $methods = Method::all(); // Fetch all payment methods
    $banks = Banks::all(); // Fetch all banks
    return view('banktrans.create', compact('methods', 'banks'));
}


public function storeTransaction(Request $request)
{
    // Custom validation rule for checking if the amount is within the balance
    $validator = Validator::make($request->all(), [
        'date' => 'required|date',
        'paymentmethord_id' => [
            'required',
            'exists:methods,id',
        ],
        'bank_id' => 'required|exists:banks,id',
        'amount' => [
            'required',
            'numeric',
            'min:0',
            function ($attribute, $value, $fail) use ($request) {
                $method = Method::find($request->paymentmethord_id);
                if (!$method) {
                    $fail('The selected payment method does not exist.');
                } elseif ($method && $value > $method->balance) {
                    $fail('The entered amount exceeds the balance of the selected payment method.');
                }
            },
        ],
        'serialnumber' => 'required|unique:banktrans,serialnumber',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Deduct the amount from the method's balance
    $method = Method::find($request->paymentmethord_id);
    $method->balance -= $request->amount;
    $method->save();

    // Store data in the banktrans table
    $banktrans = new BankTrans();
    $banktrans->date = $request->date;
    $banktrans->paymentmethord_id = $request->paymentmethord_id;
    $banktrans->bank_id = $request->bank_id;
    $banktrans->amount = $request->amount;
    $banktrans->serialnumber = $request->serialnumber;
    $banktrans->save();

    // Redirect with success message
    session()->flash('success', 'Transaction successfully added, and balance updated.');
    return redirect()->route('banktrans.index'); // Adjust the route as needed
}






public function index(Request $request)
{
    // Retrieve the search keyword or set to an empty string
    $keyword = $request->keyword ?? '';
    $from_date = $request->from ?? null;
    $to_date = $request->to ?? null;

    // Initialize the query with the BankTrans model and relationships
    $query = Banktrans::with('method', 'bank')
        ->when($keyword, function ($query, $keyword) {
            // Apply the keyword search (adjust 'name' to the searchable column)
            $query->where('name', 'like', "%$keyword%");
        })
        ->when($from_date && $to_date, function ($query) use ($from_date, $to_date) {
            // Apply the date range filter if both dates are provided
            $query->whereBetween('date', [$from_date, $to_date]);
        });

    // If no filters are applied, retrieve all BankTrans entries
    if (empty($keyword) && empty($from_date) && empty($to_date)) {
        $query->latest();
    }

    // Retrieve the filtered or default data
    $banktrans = $query->get();
    $total_cash_in_hand = Method::sum('balance');

    // Default date range for the date picker
    $default_from_date = date('Y-m-d', strtotime("-7 day"));
    $default_to_date = date('Y-m-d');
    
    // Render the view with the filtered data and variables
    return view('banktrans.index', [
        'banktrans' => $banktrans,
        'from_date' => $from_date ?? $default_from_date,
        'to_date' => $to_date ?? $default_to_date,
        'keyword' => $keyword,
        'total_cash_in_hand' => $total_cash_in_hand // Correct way to pass this variable
    ]);
}




public function updateTransaction(Request $request, $id)
{
    // Validate the form inputs
    $validator = Validator::make($request->all(), [
        'date' => 'required|date',
        'paymentmethord_id' => 'required|exists:methods,id', // Ensure valid method ID
        'bank_id' => 'required|exists:banks,id', // Ensure valid bank ID
        'amount' => 'required|numeric|min:0',
        'serialnumber' => 'required|unique:banktrans,serialnumber,' . $id, // Ensure unique serialnumber excluding current record
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    // Retrieve the existing transaction
    $banktrans = Banktrans::find($id);

    if (!$banktrans) {
        session()->flash('error', 'Transaction not found.');
        return back();
    }

    // Retrieve the original payment method
    $originalMethod = Method::find($banktrans->paymentmethord_id);

    if (!$originalMethod) {
        session()->flash('error', 'Original payment method not found.');
        return back();
    }

    // Restore the original method's balance
    $originalMethod->balance += $banktrans->amount;
    $originalMethod->save();

    // Retrieve the updated payment method
    $newMethod = Method::find($request->paymentmethord_id);

    if (!$newMethod) {
        session()->flash('error', 'Selected payment method does not exist.');
        return back()->withInput();
    }

    // Check if the balance in the new method is sufficient
    if ($newMethod->balance < $request->amount) {
        session()->flash('error', 'Insufficient balance in the selected payment method.');
        return back()->withInput();
    }

    // Deduct the new amount from the updated method's balance
    $newMethod->balance -= $request->amount;
    $newMethod->save();

    // Update the transaction data
    $banktrans->date = $request->date;
    $banktrans->paymentmethord_id = $request->paymentmethord_id;
    $banktrans->bank_id = $request->bank_id;
    $banktrans->amount = $request->amount;
    $banktrans->serialnumber = $request->serialnumber; // Update serialnumber
    $banktrans->save();

    // Redirect with success message
    session()->flash('success', 'Transaction successfully updated, and balances adjusted.');
    return redirect()->route('banktrans.index'); // Adjust the route as needed
}

    public function edit($id)
    {
        // Retrieve the bank transaction by ID
        $banktrans = Banktrans::find($id);

        // Retrieve payment methods and banks
        $methods = Method::all(); // Assuming `PaymentMethod` is the model for payment methods
        $banks = Banks::all(); // Assuming `Bank` is the model for banks

        // Pass the data to the Blade view
        return view('banktrans.edit', compact('banktrans', 'methods', 'banks'));
    }

    public function deleteTransaction($id)
{
    // Find the transaction by ID
    $banktrans = Banktrans::find($id);

    if (!$banktrans) {
        session()->flash('error', 'Transaction not found.');
        return redirect()->route('banktrans.index');
    }

    // Retrieve the method associated with the transaction
    $method = Method::find($banktrans->paymentmethord_id);

    if (!$method) {
        session()->flash('error', 'Associated payment method not found.');
        return redirect()->route('banktrans.index');
    }

    // Restore the method balance if needed (add back the deducted amount)
    $method->balance += $banktrans->amount;
    $method->save();

    // Delete the transaction
    $banktrans->delete();

    // Redirect with success message
    session()->flash('success', 'Transaction successfully deleted, and balance updated.');
    return redirect()->route('banktrans.index');
}





}
