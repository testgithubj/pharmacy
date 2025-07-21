<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\Account;
use App\Models\Account\AccountType;
use App\Models\Method;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $collection = Account::with('accountType')->latest()->paginate(10);
        $total_cash_in_hand = Method::sum('balance');
        return view('accounts.account.index', compact('collection','total_cash_in_hand'));
    }

    public function create()
    {
        $serial = Account::count() + 1;
        $accountTypes = AccountType::select('id','name')->where('status', 'active')->get();
        return view('accounts.account.create', compact('accountTypes','serial'));
    }


    public function store(Request $request)
    {
        $this->validateInput($request);
        try {
            $data = $request->only('name','account_type_id','status','serial');
            Account::create($data);
            successAlert('Created successfully');
            return redirect()->route('accounts.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        $account = Account::findOrFail($id);
        $accountTypes = AccountType::select('id','name')->where('status', 'active')->get();
        return view('accounts.account.edit', compact('accountTypes','account'));
    }

    public function update(Request $request, $id)
    {
        $this->validateInput($request, $id);
        try {
            $accountType = Account::findOrFail($id);
            $data = $request->only('name','account_type_id','status','serial');
            $accountType->update($data);
            successAlert('Updated successfully');
            return redirect()->route('accounts.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $accountType = Account::findOrFail($id);
            $accountType->delete();
            successAlert('Deleted successfully');
            return redirect()->route('accounts.index');
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
                Rule::unique('accounts')->ignore($id),
            ],
        ]);
    }
}
