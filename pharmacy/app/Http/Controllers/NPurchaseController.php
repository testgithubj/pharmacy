<?php

namespace App\Http\Controllers;

use App\CPU\Helpers;
use App\Models\Balance;
use App\Models\Batch;
use App\Models\Medicine;
use App\Models\Method;
use App\Models\Purchase;
use App\Models\PurchasePay;
use App\Models\PurchaseReturn;
use App\Models\Transaction;
use App\Models\Returns;
use App\Models\Supplier;
use App\Service\TransactionService;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NPurchaseController extends Controller
{
    public function index(Request $request)
    {
        // Initialize the query with the Purchase model
        $query = Purchase::with(['supplier', 'batch.medicine', 'method'])
            ->select('id', 'total_price', 'due_price', 'date', 'supplier_id', 'qty', 'inv_id', 'subtotal', 'discount', 'method_id', 'created_at');
    
        // Check if there's a search query for 'invoice_id'
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where('inv_id', 'LIKE', "%{$search}%");
        }
    
        // Fetch paginated results
        $paginate = $request->paginate ?? 10;
        $purchases = $query->latest()->paginate($paginate);

        if ($request->has('paid') && !empty($request->paid)) {
            // Create a new record in PurchasePay model (assuming PurchasePay has 'purchase_id' and 'amount' fields)
            $invpay = new PurchasePay();
            $invpay->purchase_id = $request->purchase_id; // Assuming you pass the purchase_id with the request
            $invpay->amount = $request->paid; // Store the paid amount
            $invpay->save();
        }

        $total_cash_in_hand = Method::sum('balance');
    
        // Return the view with data
        return view('npurchase.index', compact('purchases','total_cash_in_hand'));
    }
    



    

    public function create()
    {
        // Get the last invoice ID (if exists)
        $lastInvoice = Purchase::latest('id')->first(); // Assuming you have a 'Purchase' model for storing purchases
    
        // If there's a previous invoice, increment the number part
        if ($lastInvoice && $lastInvoice->inv_id) {
            // Extract the numeric part of the invoice ID (e.g., from 'INV000001' to '1')
            $lastInvoiceId = $lastInvoice->inv_id;
            $lastNumber = (int) substr($lastInvoiceId, 3); // Remove 'INV' and convert to integer
            $newInvoiceNumber = 'INV' . str_pad($lastNumber + 1, 6, '0', STR_PAD_LEFT); // Increment and format
        } else {
            // If no previous invoices, start from 'INV000001'
            $newInvoiceNumber = 'INV000001';
        }
    
        // Fetch the search products and suppliers for the view
        $query = Medicine::with('supplier')
            ->select('id', 'name', 'generic_name', 'price', 'image', 'supplier_id', 'strength', 'product_type');
        $search_products = $query->latest()->take(25)->get();
        $purchase_cart = session('purchase_cart', []);
        $suppliers = Supplier::select('id', 'name')->get();
        $total_cash_in_hand = Method::sum('balance');
    
        // Return the view with the new invoice number
        return view('npurchase.create', compact(
            'search_products',
            'purchase_cart',
            'suppliers',
            'newInvoiceNumber', // Pass the new invoice number to the view
            'total_cash_in_hand'
        ));
    }
    
    



public function addToCart(Request $request)
{
    $productId = $request->product_id;
    $product = Medicine::findOrFail($productId);

    $purchase_cart = session('purchase_cart', []);
    $new_supplier_id = $product->supplier_id; // Assuming `supplier_id` is a field in the Medicine model

    // Check if the cart already contains items from a different supplier
    if (!empty($purchase_cart)) {
        $existing_supplier_id = null;

        // Loop through the cart and check the supplier_id of the first item
        foreach ($purchase_cart as $item) {
            $existing_supplier_id = $item['supplier_id'];
            break; // We only need to check the supplier of the first item in the cart
        }

        // If the suppliers don't match, clear the cart and set the message
        if ($existing_supplier_id && $existing_supplier_id != $new_supplier_id) {
            $purchase_cart = [];  // Reset cart if suppliers are different
            session()->flash('toast_message', 'You can only add products from one supplier at a time. The previous items have been removed.');
        }
    }

    // Create the data to add to the cart
    $cardData = [
        'id' => $product->id,
        'name' => $product->name,
        'image' => $product->image,
        'price' => $product->price,
        'leaf_id' => $product->leaf_id,
        'vat' => $product->vat,
        'buy_price' => $product->buy_price,
        'quantity' => 1,
        'batch_name' => null,
        'expire_date' => null,
        'discount' => 0,
        'discount_value_type' => 'percent',
        'sub_total' => 0,
        'total' => 0,
        'supplier_id' => $new_supplier_id,  // Add supplier_id to the cart data
    ];

    // If the product is already in the cart, increase the quantity
    if (array_key_exists($productId, $purchase_cart)) {
        $purchase_cart[$productId]['quantity'] += 1;
    } else {
        // Add the new product to the cart
        $purchase_cart[$productId] = $cardData;
    }

    // Update the session with the new cart data
    session(['purchase_cart' => $purchase_cart]);

    // Return the updated cart view
    return response()->json([
        'already_has' => 0,
        'added' => 1,
        'view' => view('npurchase.cart_table', compact('purchase_cart'))->render()
    ]);
}




    public function removeFromCart(Request $request)
    {
        $productId = $request->product_id;
        $purchase_cart = session('purchase_cart');
        if (array_key_exists($productId, $purchase_cart)) {
            $productId = $request->product_id;
            $purchase_cart = collect(session('purchase_cart')); // convert array to collection
            $purchase_cart->forget($productId); // use forget() method on the collection
            session(['purchase_cart' => $purchase_cart->toArray()]);
            return response()->json([
                'not_exsits' => 0,
                'removed' => 1,
                'view' => view('npurchase.cart_table', compact('purchase_cart'))->render()
            ]);
        } else {
            return response()->json([
                'not_exsits' => 1,
                'removed' => 0,
                'view' => view('npurchase.cart_table', compact('purchase_cart'))->render()
            ]);
        }
    }


    public function updateCart(Request $request)
    {
        $cartId = $request->cart_id;
        $field = $request->field;
        $value = $request->value;
        $purchase_cart = session('purchase_cart', []);
        if (array_key_exists($cartId, $purchase_cart)) {
            $purchase_cart[$cartId][$field] = $value;
        }
        session(['purchase_cart' => $purchase_cart]);
        $response = [
            'success' => 1,
            'error' => 0,
            'message' => 'Save changes!',
            'view' => view('npurchase.cart_table', compact('purchase_cart'))->render()
        ];
        return response()->json($response);
    }


    public function store(Request $request)
{
    $this->inputValidate($request);

    try {
        DB::beginTransaction();

        // Validate the balance
        $balance = Method::find($request->input('payment_method_id'));
        if ($request->input('paid') > 0 && $balance->balance < $request->input('paid')) {
            throw new \Exception("Insufficient balance! Your balance on $balance->name: $balance->balance");
        }

        // Validate the purchase cart
        $purchase_medicines = session('purchase_cart') ?? [];
        if (empty($purchase_medicines)) {
            throw new \Exception('Cart cannot be empty');
        }

        // Prepare data for the Purchase
        $data = [
            'date' => $request->purchase_date,
            'total_price' => $request->total,
            'due_price' => $request->due_amount,
            'supplier_id' => $request->supplier_id,
            'qty' => $request->total_quantity,
            'inv_id' => $request->invoice_id,
            'subtotal' => $request->sub_total,
            'discount' => $request->invoice_discount_amount + $request->medicine_discount,
            'method_id' => $request->payment_method_id,
            'shop_id' => Auth::user()->shop_id,
            'district_id' => Auth::user()->shop->district_id,
            'thana_id' => Auth::user()->shop->thana_id,
        ];

        $purchase = Purchase::create($data);

        if ($purchase) {
            // Update supplier's due amount
            if ($request->due_amount > 0) {
                $previus_due = Supplier::findOrFail($request->supplier_id);
                $previus_due->due += $request->due_amount;
                $previus_due->save();
            }

            // Save to PurchasePay
            $invpay = new PurchasePay();
            $invpay->shop_id = Auth::user()->shop_id;
            $invpay->purchase_id = $purchase->id;
            $invpay->date = $request->purchase_date;
            $invpay->amount = $request->paid;
            $invpay->supplier_id = $request->supplier_id;
            $invpay->method_id = $request->payment_method_id;
            $invpay->save();

            // Deduct balance from the payment method
            if ($request->input('paid') > 0) {
                $method = Method::find($request->payment_method_id);
                $method->balance -= $request->paid;
                $method->save();
            }

            // Save each medicine in Batch
            foreach ($purchase_medicines as $key => $medicine) {
                $pmd = [
                    'medicine_id' => $key,
                    'purchase_id' => $purchase->id,
                    'qty' => $medicine['quantity'],
                    'purchase_qty' => $medicine['quantity'],
                    'name' => $medicine['batch_name'],
                    'price' => $medicine['price'],
                    'buy_price' => $medicine['buy_price'],
                    'expire' => $medicine['expire_date'],
                    'leaf_id' => $medicine['leaf_id'],
                ];
                Batch::create($pmd);
            }

            // Save Transaction
            $transactionData = [
                'tran_id' => uniqid(),
                'date' => $request->purchase_date,
                'amount' => $request->paid,
                'invoice_id' => $request->invoice_id,
                'invoice_type' => 'purchase',
                'method_id' => $request->payment_method_id,
                'debit_account_id' => 1, // Adjust based on your logic
                'credit_account_id' => 3, // Adjust based on your logic
                'particular' => "Purchase Payment for Invoice #{$request->invoice_id}",
                'shop_id' => Auth::user()->shop_id,
            ];
            Transaction::create($transactionData);

            // // Additional transaction for the purchase total
            // TransactionService::purchaseTransaction($purchase->total_price, $purchase->inv_id);

            DB::commit();
            session()->forget('purchase_cart');
            successAlert('Purchase created successfully!');
            return redirect()->route('purchase.index');
        }
    } catch (\Exception $e) {
        DB::rollBack();
        errorAlert($e->getMessage());
        return redirect()->back();
    }
}



    public function show($id)
    {
        $data['invoice'] = Purchase::where('id', $id)->first();
        $data['purchase_details'] = Batch::with('leaf')->where('purchase_id', $id)->get();
        return view('npurchase.view')->with($data);
    }


    public function destroy($id)
    {
        try {
            $purchase = Purchase::find($id);
            if ($purchase->delete()) {
                Batch::where('purchase_id', $id)->delete();
                Toastr::success('Purchase deleted successfully!', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('purchase.index');
            }
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
    }

    public function returnHistory(Request $request)
    {
        // Fetch returns with their related batch and medicine
        $return_data = PurchaseReturn::with(['batch', 'batch.medicine'])
            ->latest('id')
            ->paginate($request->paginate ?? 10);
    
        // Group the returns by 'purchae_id'
        $grouped_data = $return_data->groupBy('purchae_id');
    
        $total_cash_in_hand = Method::sum('balance');
    
        return view('npurchase.return_list', compact('grouped_data', 'return_data', 'total_cash_in_hand'));
    }
    
    
    
    public function showReturnForm($id)
{
    $inv = Purchase::findOrFail($id);
    $medicines = Batch::where('purchase_id', $id)->get();
    return view('npurchase.return_form', compact('inv', 'medicines'));
}

public function returnProcess(Request $request, $id)
{
    $request->validate([
        'medicines' => 'required|array',
        'medicines.*.medicine' => 'required|exists:medicines,id',
        'medicines.*.qty' => 'required|integer|min:0', // Ensure quantity is positive
    ]);

    // Ensure that at least one medicine has a quantity greater than 0
    $validQuantity = false;
    foreach ($request->medicines as $medicine) {
        if ($medicine['qty'] > 0) {
            $validQuantity = true;
            break; // Exit loop if a valid quantity is found
        }
    }

    if (!$validQuantity) {
        Toastr::warning('Please return at least one medicine with a quantity greater than 0.', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->back();
    }

    $inv = Purchase::findOrFail($id);
    $totalReturnAmount = 0;

    foreach ($request->medicines as $medicine) {
        // Find the batch for the selected medicine
        $batch = Batch::where('medicine_id', $medicine['medicine'])->where('purchase_id', $id)->first();

        // Ensure the requested quantity is less than or equal to the available quantity
        if ($medicine['qty'] > $batch->qty) {
            Toastr::warning('Invalid quantity applied! Total quantity: ' . $batch->qty, '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }

        // Process the return for each medicine
        $this->itemReturnMarge($batch, $inv, $medicine, $id);
        $this->invoiceReturnMarge($batch, $inv, $medicine, $id);

        // Calculate the total return amount
        $totalReturnAmount += $batch->buy_price * $medicine['qty'];
    }

    // Update the cash payment method balance
    $this->updateCashBalance($totalReturnAmount);

    Toastr::success('Returns Accepted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
    return redirect()->route('purchase.return');
}





protected function itemReturnMarge($item, $invoice, $request, $id)
{
    // Here, $request is an array containing the medicine data
    $qty = $request['qty']; // Accessing 'qty' from the array
    $item->qty -= $qty; // Update the batch quantity
    $item->save();

    // Calculate the amount for the return
    $amt = ($item->buy_price * $qty);

    // Adjust the supplier's due amount if applicable
    if ($invoice->supplier_id != 0) {
        $customer = Supplier::find($invoice->supplier_id);
        if ($customer->due >= $amt) {
            $customer->due -= $amt;
        }
        $customer->save();
    }

    // Save the return entry
    $return = new PurchaseReturn();
    $return->date = date('Y-m-d');
    $return->purchae_id = $id;
    $return->batch_id = $item->id;
    $return->amount = $amt;
    $return->quantity = $qty;
    $return->shop_id = Auth::user()->shop_id;
    return $return->save();
}

protected function invoiceReturnMarge($batch, $inv, $request, $id)
{
    // Here, $request is an array containing the medicine data
    $qty = $request['qty']; // Accessing 'qty' from the array

    // Update the invoice's quantity and price based on the return
    $inv->qty -= $qty;
    $inv->total_price -= $batch->buy_price * $qty;
    $inv->subtotal -= $batch->buy_price * $qty;
    return $inv->save();
}



  
    public function returnInvoice($id)
{
    $return = PurchaseReturn::findOrFail($id);
    $invoice = Purchase::find($return->purchae_id);  // Use the correct spelling for `purchase_id`
    $purchase_details = Batch::where('purchase_id', $invoice->id)->get();
    $returns = PurchaseReturn::where('purchae_id', $return->purchae_id)->get();  // Fetch all returns for the same purchase ID
    
    return view('npurchase.return_invoice', compact('return', 'invoice', 'purchase_details', 'returns'));
}



public function getMedicines(Request $request)
{
    // Start the query to get medicines with their suppliers
    $query = Medicine::with('supplier')
        ->select('id', 'name', 'generic_name', 'price', 'image', 'supplier_id', 'strength', 'product_type')
        ->where('status', 1); // Only get medicines with status = 1

    // Filter by keywords for name or generic name
    if (!empty($request->keywords)) {
        $keywords = '%' . $request->keywords . '%';
        $query->where(function ($query) use ($keywords) {
            $query->where('name', 'LIKE', $keywords)
                  ->orWhere('generic_name', 'LIKE', $keywords);
        });
    }

    // Filter by supplier_id if provided
    if (!empty($request->supplier_id)) {
        $query->where('supplier_id', $request->supplier_id);
    }

    // Execute the query to get the filtered medicines
    $search_products = $query->get();

    // Return the rendered view with the search results
    return response([
        'results' => view('npurchase.search_result', compact('search_products'))->render(),
    ]);
}


    


    public function calculateCharge($value, $amount, $type)
    {
        if ($value && $type == 'percent') {
            return ($value / 100) * $amount;
        }
        if ($value && $type == 'fixed') {
            return $value;
        }
        return 0;
    }


    protected function inputValidate($request)
    {
        $rules = [
            'purchase_date' => 'required',
            'invoice_id' => 'required',
            'supplier_id' => 'required',
            'sub_total' => 'required',
            'total' => 'required',
        ];
        if ($request->input('paid') > 1) {
            $rules['payment_method_id'] = 'required';
        }
        return $request->validate($rules);
    }



    public function updateStatus(Request $request, $id)
    {
        // Validate that the status is either 'pending' or 'completed'
        $validated = $request->validate([
            'status' => 'required|in:pending,completed',
        ]);

        // Find the return by its ID
        $return = PurchaseReturn::findOrFail($id);

        // Update the status
        $return->update([
            'status' => $validated['status'],
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('status', 'Return status updated successfully.');
    }
    protected function updateCashBalance($amount)
{
    $method = Method::where('name', 'cash')->where('shop_id', Auth::user()->shop_id)->first();

    if ($method) {
        $method->balance += $amount;
        $method->save();
    } else {
        // Handle case where the method does not exist
        Toastr::error('Cash method not found', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
    }
}

    

}