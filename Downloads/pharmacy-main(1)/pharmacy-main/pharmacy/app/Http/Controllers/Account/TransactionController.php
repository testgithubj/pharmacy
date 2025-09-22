<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Account\Account;
use App\Models\Account\Transaction;
use App\Models\PharmacyExpense;
use App\Models\Method;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
{
    $collection = Transaction::with('debitAccount', 'creditAccount')
        ->latest()
        ->paginate(10);

    // Get the account IDs from PharmacyExpense for matching
    $expenseAccounts = PharmacyExpense::pluck('amount')->toArray();
    $total_cash_in_hand = Method::sum('balance');

    return view('accounts.transaction.index', compact('collection', 'expenseAccounts','total_cash_in_hand'));
}


    public function create()
    {
        $accounts = Account::select('id','name')->where('status', 'active')->get();
        return view('accounts.transaction.create', compact('accounts'));
    }


    public function store(Request $request)
    {
        $this->validateInput($request);
        try {
            $data = $request->except('_token');
            $data['tran_id'] = uniqid();
            Transaction::create($data);
            successAlert('Created successfully');
            return redirect()->route('transactions.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }


    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);
        $accounts = Account::select('id','name')->where('status', 'active')->get();
        return view('accounts.transaction.edit', compact('accounts','transaction'));
    }

    public function update(Request $request, $id)
    {
        $this->validateInput($request, $id);
        try {
            $transaction = Transaction::findOrFail($id);
            $data = $request->except('_token','_method');
            $transaction->update($data);
            successAlert('Updated successfully');
            return redirect()->route('transactions.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();
            successAlert('Deleted successfully');
            return redirect()->route('transactions.index');
        }catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }


    protected function validateInput($request, $id = null)
    {
        $request->validate([
            'date' => 'required|date',
            'debit_account_id' => 'required|different:credit_account_id',
            'credit_account_id' => 'required|different:debit_account_id',
            'amount' => 'required|numeric',
            'particular' => 'required|string|max:255',
        ], [
            'debit_account_id.different' => 'The debit account and credit account must be different.',
            'credit_account_id.different' => 'The credit account and debit account must be different.',
        ]);
    }

}
