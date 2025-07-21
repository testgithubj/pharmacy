<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePay;
use App\Models\Medicine;
use App\Models\Method;
use App\Models\Purchase;
use App\Models\PurchasePay;
use App\Models\Supplier;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SupplierController extends Controller
{

    public function index(Request $request)
    {
        $data['collection'] = Supplier::select('id', 'name', 'address', 'phone', 'due', 'global')
            ->orderBy('id', 'desc')
            ->latest()
            ->paginate($request->input('limit', 10));
            $data['total_cash_in_hand'] = Method::sum('balance');
        return view('supplier.index')->with($data);
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            // Validate the input data
            $request->validate([
                'name' => 'required|string|regex:/^[a-zA-Z\s\+\-_]+$/|max:255|unique:suppliers,name,NULL,id,shop_id,' . Auth::user()->shop_id,
                'phone' => 'required|digits_between:10,15|unique:suppliers,phone,NULL,id,shop_id,' . Auth::user()->shop_id,
                'address' => 'nullable|string|max:255',
            ], [
                'name.required' => 'The name is required.',
                'name.regex' => 'The name must only contain letters and spaces.',
                'name.max' => 'The name must not exceed 255 characters.',
                'name.unique' => 'A supplier with this name already exists.',
                'phone.required' => 'The phone number is required.',
                'phone.digits_between' => 'The phone number must be between 10 and 15 digits.',
                'phone.unique' => 'A supplier with this phone number already exists.',
            ]);
    
            // Save the supplier
            $customer = new Supplier();
            $customer->name = $request->name;
            $customer->phone = $request->phone;
            $customer->address = $request->address;
            if ($request->filled('due')) {
                $customer->due = $request->due;
            }
            $customer->shop_id = Auth::user()->shop_id;
            $customer->upazilla_id = Auth::user()->shop->upazilla_id;
            $customer->thana_id = Auth::user()->shop->thana_id;
    
            if ($customer->save()) {
                Toastr::success('Supplier successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('supplier.list');
            } else {
                Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('supplier.list');
            }
        } else {
            return view('supplier.add');
        }
    }
    



    public function edit(Request $request, $id)
    {
        $customer = Supplier::where('id', $id)->firstOrFail();
        return view('supplier.edit', compact('customer'));
    }


    public function update(Request $request, $id)
    {
        $request->validate([
           'name' => 'required|string|max:255',
            'phone' => 'required|digits_between:10,15',
            'due' => 'nullable|numeric|min:0', // Add validation for 'due'
        ], 
        [
            'name.required' => 'The name is required.',
            'name.regex' => 'The name must only contain letters and spaces.',
            'name.max' => 'The name must not exceed 255 characters.',
            'name.unique' => 'A supplier with this name already exists.',
            'phone.required' => 'The phone number is required.',
            'phone.digits_between' => 'The phone number must be between 10 and 15 digits.',
            'phone.unique' => 'A supplier with this phone number already exists.',
        ]);
    
        try {
            DB::beginTransaction();
            $supplier = Supplier::where('id', $id)->firstOrFail();
            $supplier->name = $request->name;
            $supplier->phone = $request->phone;
            $supplier->address = $request->address;
            $supplier->due = $request->due; // Save the due amount
            $supplier->save();
    
            DB::commit();
            \successAlert('Successfully updated');
            return redirect()->route('supplier.list');
        } catch (\Exception $e) {
            DB::rollBack();
            \errorAlert($e->getMessage());
            return redirect()->back();
        }
    }
    

    public function delete(Request $request, $id)
    {
        $customer = Supplier::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();

        if ($customer->delete()) {
            Toastr::success('Supplier successfully Deleted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('supplier.list');
        } else {
            Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('supplier.list');
        }

    }


    public function view(Request $request, $id)
    {
        $data['supplier']       = Supplier::where('id', $id)->firstOrFail();
        $data['invoice']        = Purchase::where('supplier_id', $id)
                                    ->paginate($request->input('limit', 8));
        $data['transaction']    = PurchasePay::where('supplier_id', $id)->get();
        $data['methods']        = Method::all();
        return view('supplier.view')->with($data);
    }

    public function duePayment(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'invoice_id' => 'required',
            'method_id' => 'required',
        ]);
        try {
            $amount  = $request->input('amount');
            DB::beginTransaction();

            $paymentMethod = Method::findOrFail($request->input('method_id'));

            if($paymentMethod->balance < $amount){
                throw new \Exception("Insufficient balance! your balance on $paymentMethod->name : $paymentMethod->balance");
            }

            $supplier = Supplier::findOrFail($request->supplier_id);
            $invoice = Purchase::findOrFail($request->invoice_id);

            $supplier->due -= $amount;
            $supplier->save();

            $invoice->due_price -= $amount;
            $invoice->save();

            $transaction = new PurchasePay();
            $transaction->amount = $amount;
            $transaction->invoice_id = $request->input('invoice_id');
            $transaction->supplier_id = $request->input('supplier_id');
            $transaction->method_id = $request->input('method_id');
            $transaction->purchase_id = $request->input('invoice_id');
            $transaction->date = now();
            $transaction->save();

            $paymentMethod->balance -= $amount;
            $paymentMethod->save();

            DB::commit();
            \successAlert('Payment successfully processed');
            return redirect()->back();
        }catch (\Exception $e) {
            DB::rollBack();
            \errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function due(Request $request)
    {

        if ($request->ajax()) {
            $data = Purchase::where('shop_id', Auth::user()->shop_id)->groupBy('supplier_id')->selectRaw('sum(due_price) as sum, supplier_id, id')->having('sum', '>', 0);

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {

                    return $row->supplier->name;

                })
                ->addColumn('phone', function ($row) {

                    return $row->supplier->phone;

                })
                ->addColumn('dues', function ($row) {
                    $data = Balance::where('shop_id', Auth::user()->shop_id)->where('supplier_id', $row->id)->sum('due');
                    return $data;
                })
                ->addColumn('address', function ($row) {
                    return $row->supplier->address;
                })
                ->addColumn('action', function ($row) {

                    return ' <a href="' . route('supplier.view', $row->supplier->id) . '" class="badge bg-info"><i class="fas fa-eye"></i></a>';

                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $customer = Supplier::where('shop_id', Auth::user()->shop_id)->get();

        return view('supplier.due', compact('customer'));
    }


    // get city by state
    public function medicine($id = 0)
    {
        $medicine = Medicine::where('shop_id', Auth::user()->shop_id)->where('supplier_id', $id)->orderBy('name', 'asc')->where('status', 1)->get();
        $output = $allmedicine = '';
        if (count($medicine) > 0) {
            $allmedicine .= '<option value="">Select Medicine</option>';
            foreach ($medicine as $medicine) {
                $allmedicine .= '<option value="' . $medicine->id . '">' . $medicine->name . '</option>';
            }
        }
        $output = array('status' => true, 'allmedicine' => $allmedicine);
        return response()->json($output);
    }
}