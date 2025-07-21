<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Method;
use App\Models\Transaction;
use App\Models\Banktrans;
use App\Models\PurchaseReturn;
use Brian2694\Toastr\Facades\Toastr;


class PaymentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Add a new payment method.
     */
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            // Check if the method already exists
            $custom = Method::where('name', $request->name)
                            ->where('shop_id', Auth::user()->shop_id)
                            ->first();

            if ($custom != null) {
                // Show error and stop execution
                Toastr::error('This payment method already exists', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('payment.method');
            }

            // Create a new method
            $method = new Method();
            $method->name = $request->name;
            $method->balance = $request->balance;
            $method->shop_id = Auth::user()->shop_id;

            if ($method->save()) {
                Toastr::success('Method successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            } else {
                Toastr::error('Something went wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            }
            return redirect()->route('payment.method');
        } else {
            // Load the add method form view
            return view('method.add');
        }
    }

    /**
     * Deduct an amount from a payment method.
     */
    public function deduct(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        // Find the method by ID
        $method = Method::where('shop_id', Auth::user()->shop_id)->where('id', $id)->first();

        if (!$method) {
            Toastr::error('Payment method not found', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('payment.method');
        }

        // Check if the balance is sufficient
        if ($method->balance < $request->amount) {
            Toastr::error('Insufficient balance in the payment method', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('payment.method');
        }

        // Deduct the amount from the balance
        $method->balance -= $request->amount;

        if ($method->save()) {
            Toastr::success('Amount successfully deducted from the payment method', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        } else {
            Toastr::error('Something went wrong while deducting the amount', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        }

        return redirect()->route('payment.method');
    }

    /**
     * Delete a payment method.
     */
    public function delete(Request $request, $id)
    {
        $customer = Method::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();

        if ($customer->delete()) {
            Toastr::success('Payment method deleted successfully', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('payment.method');
        } else {
            Toastr::error('Something went wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('payment.method');
        }
    }

    /**
     * List all payment methods.
     */
    public function method(Request $request)
{
    $methods = Method::select('*')->where('shop_id', Auth::user()->shop_id)->latest()->get();
    
    foreach ($methods as $method) {
        $method->has_transaction = Transaction::where('method_id', $method->id)->exists();
        
    }

    $total_balance = $methods->sum('balance');
    return view('method.list', compact('methods', 'total_balance'));
}

    public function updateReturnStatus(Request $request)
{
    // Validate request data
    $request->validate([
        'return_id' => 'required|exists:purchase_returns,id',
        'status' => 'required|in:pending,completed',
    ]);

    // Find the PurchaseReturn by ID
    $purchaseReturn = PurchaseReturn::findOrFail($request->return_id);

    // Update the status
    $purchaseReturn->status = $request->status;

    if ($request->status === 'completed') {
        // Update the Method model's cash field
        $method = Method::where('shop_id', Auth::user()->shop_id)->first();

        if ($method) {
            $method->cash += $purchaseReturn->amount;
            $method->save();
        }
    }

    $purchaseReturn->save();

    return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
}



/**
 * Update the payment method details.
 */
public function update(Request $request, $id)
{
    // Validate the request data
    $request->validate([
        'name' => 'required|string|max:255',
        'balance' => 'required|numeric|min:0',
    ]);

    // Find the payment method by ID
    $method = Method::where('shop_id', Auth::user()->shop_id)->findOrFail($id);

    // Update the method details
    $method->name = $request->name;
    $method->balance = $request->balance;

    // Save the updated method
    if ($method->save()) {
        Toastr::success('Payment method updated successfully', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
    } else {
        Toastr::error('Something went wrong while updating the method', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
    }

    return redirect()->route('payment.method');
}


public function edit($id)
{
    $method = Method::where('shop_id', Auth::user()->shop_id)->findOrFail($id);
    return view('method.edit', compact('method'));
}







}
