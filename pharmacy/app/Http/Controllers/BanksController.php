<?php

namespace App\Http\Controllers;
use App\Models\Banks;
use App\Models\Method;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BanksController extends Controller
{
    public function viewbank(Request $request)
{
    // Fetch all records from the Banks model
    $contact = Banks::all();
    $total_cash_in_hand = Method::sum('balance');
    // Return the view with the results
    return view('banks.banks', compact('contact','total_cash_in_hand'));
}

    
    public function viewform()
    {
        
        return view('banks.banks_add');
    }

    public function bank(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account_holder_name' => 'required',
            'account_number' => 'required',
            'account_type' => 'required',
            'ifsc_code' => 'required',
            'branch_name' => 'required',
            'branch_address' => 'nullable',
            'contact_number' => 'nullable',
            'email' => 'nullable',
            'status' => 'nullable',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator->errors(), 'formErrors');
        }

        $contact = new Banks();
        $contact->account_holder_name = $request->account_holder_name;
        $contact->account_number = $request->account_number;
        $contact->account_type = $request->account_type;
        $contact->ifsc_code = $request->ifsc_code;
        $contact->branch_name = $request->branch_name;
        $contact->branch_address = $request->branch_address;
        $contact->contact_number = $request->contact_number;
        $contact->email = $request->email;
        $contact->status = $request->status;

        $contact->save();
        session()->flash('success');
        return redirect()->route('bank.view');
    }

    public function bankEditform($id)
    {
        $contact = Banks::find($id);
        return view('banks.banks_edit', compact('contact'));
    }



  

    public function updatebank(Request $request, $id)
    {
        $contact = Banks::findOrFail($id);

        $fields = [
            'account_holder_name',
            'account_number',
            'account_type',
            'ifsc_code',
            'branch_name',
            'branch_address',
            'contact_number',
            'email',
            'status',
        ];

        foreach ($fields as $field) {
            if ($request->has($field) && $request->filled($field)) {
                $contact->$field = $request->$field;
            }
        }

        $contact->save();

        session()->flash('success', 'Data Updated Successfully.');
        return redirect()->route('bank.view');
    }



    // product Delete >>>>>>>>>>>>>>>>>>>>>>>>

    public function deletebank($id)
    {

        $contact = Banks::find($id);
        $contact->delete();
        session()->flash('success', 'Data Deleted Successfully.');
        return redirect()->back();
    }
}
