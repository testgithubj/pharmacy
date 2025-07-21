<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\AccountType;
use App\Models\Method;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountTypeController extends Controller
{

    public function index(Request $request)
    {
        $collection = AccountType::latest()->paginate(10);
        $total_cash_in_hand = Method::sum('balance');
        return view('accounts.account-type.index', compact('collection','total_cash_in_hand'));
    }

    public function create()
    {
        $serial = AccountType::count() + 1;
        $total_cash_in_hand = Method::sum('balance');
        return view('accounts.account-type.create', compact('serial','total_cash_in_hand'));
    }


    public function store(Request $request)
    {
        $this->validateInput($request);
        try {
            $data = $request->only('name','status','serial');
            AccountType::create($data);
            successAlert('Created successfully');
            return redirect()->route('account-types.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        $accountType = AccountType::findOrFail($id);
        return view('accounts.account-type.edit', compact('accountType'));
    }

    public function update(Request $request, $id)
    {
        $this->validateInput($request, $id);
        try {
            $accountType = AccountType::findOrFail($id);
            $data = $request->only('name','status','serial');
            $accountType->update($data);
            successAlert('Updated successfully');
            return redirect()->route('account-types.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $accountType = AccountType::findOrFail($id);
            $accountType->delete($id);
            successAlert('Deleted successfully');
            return redirect()->route('account-types.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }


    protected function validateInput($request, $id = null)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('account_types')->ignore($id),
            ],
        ]);
    }
}
