<?php

namespace App\Http\Controllers;

use App\Mail\SendCustomerMail;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoicePay;
use App\Models\Method;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CustomerController extends Controller
{

    public function index(Request $request)
    {
        $data['collection'] = Customer::select('id', 'name', 'address', 'phone', 'due')
            ->latest('id')
            ->paginate($request->input('limit', 10));
        $data['total_cash_in_hand'] = Method::sum('balance');
        return view('customer.index')->with($data);
    }


    public function create()
    {
        $data['total_cash_in_hand'] = Method::sum('balance');
        return view('customer.create')->with($data);
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z\s]+$/'], 
            'phone' => 'required|numeric|unique:customers,phone|digits_between:10,15',// Ensure the phone is unique in the customers table
            'email' => 'required|email|unique:customers,email',  // Ensure the email is unique in the customers table
            'address' => 'nullable|string|max:255',
            'due' => 'nullable|numeric|min:0',  // Ensure due is a positive number or zero
        ]);
    
        try {
            // Create new customer and save to the database
            $customer = new Customer();
            $customer->name = $request->name;
            $customer->phone = $request->phone;
            $customer->email = $request->email;
            $customer->address = $request->address;
            $customer->due = $request->input('due', 0);
            $customer->shop_id = Auth::user()->shop_id; 
            $customer->save();
    
            // Success message and redirect
            successAlert('Created successfully');
            return redirect()->route('customer.index');
        } catch (\Exception $e) {
            // Handle any errors
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }
    


    public function edit(Customer $customer)
    {
        return view('customer.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $this->validation($request, $customer->id);

        try {
            $customer->name = $request->name;
            $customer->phone = $request->phone;
            $customer->email = $request->email;
            $customer->address = $request->address;
            $customer->due = $request->input('due', 0);
            $customer->save();
            successAlert('Updated successfully');
            return redirect()->route('customer.index');
        } catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function show(Customer $customer)
    {
        $data['customer'] = $customer;
        $data['invoice'] = Invoice::where('customer_id', $customer->id)->get();
        $data['transaction'] = InvoicePay::where('customer_id', $customer->id)->get();
        $data['methods'] = Method::all();
        return view('customer.view')->with($data);
    }


    public function delete(Customer $customer)
    {
        try {
            $customer->delete();
            successAlert('Deleted successfully');
            return redirect()->route('customer.index');
        } catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }




    public function duePayment(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'invoice_id' => 'required',
            'method_id' => 'required',
        ]);
        try {
            $amount = $request->input('amount');
            DB::beginTransaction();
            $customer = Customer::findOrFail($request->customer_id);
            $invoice = Invoice::findOrFail($request->invoice_id);
            $customer->due -= $amount;
            $customer->save();

            $invoice->due_price -= $amount;
            $invoice->paid_amount += $amount;
            $invoice->save();

            $transaction = new InvoicePay();
            $transaction->amount = $amount;
            $transaction->invoice_id = $request->input('invoice_id');
            $transaction->customer_id = $request->input('customer_id');
            $transaction->method_id = $request->input('method_id');
            $transaction->date = now();
            $transaction->save();

            $paymentMethod = Method::findOrFail($request->input('method_id'));
            $paymentMethod->balance += $amount;
            $paymentMethod->save();

            DB::commit();
            \successAlert('Payment successfully processed');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            \errorAlert($e->getMessage());
            return redirect()->back();
        }
    }


    public function due(Request $request)
    {
        if ($request->ajax()) {
            $data = Customer::select('id', 'name', 'address', 'phone', 'due')->where('shop_id', Auth::user()->shop_id)->where('due', '>', 0);
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    return '<a href="' . route('customer.edit', $row->id) . '" class="badge bg-primary"><i class="fas fa-edit"></i></a> <a href="' . route('customer.view', $row->id) . '" class="badge bg-info"><i class="fas fa-eye"></i></a> <a onclick="return confirm(\'Are you sure?\')" href="' . route('customer.delete', $row->id) . '" class="badge bg-danger"><i class="fas fa-trash"></i></a>';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
        $customer = Customer::where('shop_id', Auth::user()->shop_id)->get();

        return view('customer.due', compact('customer'));
    }


    public function sendEmail(Request $request)
    {
        $customers = Customer::select('id', 'name', 'address', 'phone', 'email')->where('shop_id', Auth::user()->shop_id)->latest()->get();
        return view('customer.email_sender', compact('customers'));
    }

    public function sendEmailProcess(Request $request)
    {
        $request->validate([
            'customers' => 'required',
            'email_subject' => 'required',
            'email_body' => 'required',
        ], [
            'customers.required' => 'Customers is required',
            'email_subject.required' => 'Subject field is required',
            'email_body.required' => 'Email body field is required',
        ]);
        try {
            $company = Auth::user()->shop;
            $user = Auth::user();
            $data = [
                'subject' => $request->email_subject . ' | ' . env('MAIL_FROM_NAME'),
                'company_owner' => $user->name,
                'company_name' => $company->name,
                'from_email' => env('MAIL_FROM_ADDRESS'),
                'body' => $request->email_body,
                'logo' => asset('storage/images/admin/site_logo/' . $company->site_logo),
                'site_name' => $company->site_title,
            ];
            foreach ($request->customers as $customer) {
                $cmr = Customer::where('email', $customer)->first();
                $data['customer_name'] = $cmr->name;
                Mail::to($customer)->send(new SendCustomerMail($data));
            }
            Toastr::success('Mail has been sent successfuly!', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return back();
        } catch (\Exception $exception) {
            Toastr::error($exception->getMessage(), '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
    }

    private function validation($request, $id = null)
    {
        return $request->validate([
            'name' => 'required',
            'email' => 'required|unique:customers,email,' . $id,
            'phone' => 'required|unique:customers,phone,' . $id,
        ]);
    }
}