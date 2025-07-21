<?php

namespace App\Http\Controllers\Account;

use App\Enum\AccountTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\Account\Account;
use App\Models\Account\AccountType;
use App\Models\Method;
use App\Models\Account\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function trailBalance(Request $request)
    {
        $collection = Account::select('accounts.id', 'accounts.name',
            DB::raw('SUM(CASE WHEN transactions.debit_account_id = accounts.id THEN transactions.amount ELSE 0 END) as total_debits'),
            DB::raw('SUM(CASE WHEN transactions.credit_account_id = accounts.id THEN transactions.amount ELSE 0 END) as total_credits')
        )
            ->leftJoin('transactions', function($join) {
                $join->on('accounts.id', '=', 'transactions.debit_account_id')
                    ->orOn('accounts.id', '=', 'transactions.credit_account_id');
            })
            ->groupBy('accounts.id', 'accounts.name')
            ->orderBy('accounts.serial', 'asc')
            ->get();

        $total_cash_in_hand = Method::sum('balance');

        return view('accounts.reports.trail-balance', compact('collection','total_cash_in_hand'));
    }



    public function balanceSheet(Request $request)
    {
        // Get total debits per account
        $debits = DB::table('transactions')
            ->select('debit_account_id as account_id', DB::raw('SUM(amount) as total_debit'))
            ->groupBy('debit_account_id');

        // Get total credits per account
        $credits = DB::table('transactions')
            ->select('credit_account_id as account_id', DB::raw('SUM(amount) as total_credit'))
            ->groupBy('credit_account_id');

        // Combine the debits and credits
        $balances = DB::table('accounts')
            ->leftJoinSub($debits, 'debits', 'accounts.id', '=', 'debits.account_id')
            ->leftJoinSub($credits, 'credits', 'accounts.id', '=', 'credits.account_id')
            ->select('accounts.id as account_id', 'accounts.name as account_name',
                DB::raw('COALESCE(debits.total_debit, 0) as total_debit'),
                DB::raw('COALESCE(credits.total_credit, 0) as total_credit'),
                DB::raw('COALESCE(credits.total_credit, 0) - COALESCE(debits.total_debit, 0) as balance'))
            ->get();

        // Categorize balances by account type
        $assets = [];
        $liabilities = [];
        $equity = [];

        foreach ($balances as $balance) {
            $account = Account::find($balance->account_id);
            if ($account->account_type_id == AccountTypeEnum::ASSET) {
                $assets[] = $balance;
            } elseif ($account->account_type_id == AccountTypeEnum::LIABILITY) {
                $liabilities[] = $balance;
            } elseif ($account->account_type_id == AccountTypeEnum::EQUITY) {
                $equity[] = $balance;
            }
        }

        // Calculate totals
        $totalAssets = array_sum(array_column($assets, 'balance'));
        $totalLiabilities = array_sum(array_column($liabilities, 'balance'));
        $totalEquity = array_sum(array_column($equity, 'balance'));
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $balanceSheet =  [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalLiabilitiesAndEquity' => $totalLiabilitiesAndEquity,
        ];
        $total_cash_in_hand = Method::sum('balance');

        return view('accounts.reports.balance-sheet', compact('balanceSheet','total_cash_in_hand'));
    }



    public function incomeStatement(Request $request)
    {
        // Get total credits per account
        $credits = DB::table('transactions')
            ->select('credit_account_id as account_id', DB::raw('SUM(amount) as total_credit'))
            ->groupBy('credit_account_id');

        // Get total debits per account
        $debits = DB::table('transactions')
            ->select('debit_account_id as account_id', DB::raw('SUM(amount) as total_debit'))
            ->groupBy('debit_account_id');

        // Combine the debits and credits
        $balances = DB::table('accounts')
            ->leftJoinSub($debits, 'debits', 'accounts.id', '=', 'debits.account_id')
            ->leftJoinSub($credits, 'credits', 'accounts.id', '=', 'credits.account_id')
            ->select('accounts.id as account_id', 'accounts.name as account_name', 'accounts.account_type_id as account_type',
                DB::raw('COALESCE(debits.total_debit, 0) as total_debit'),
                DB::raw('COALESCE(credits.total_credit, 0) as total_credit'),
                DB::raw('COALESCE(credits.total_credit, 0) - COALESCE(debits.total_debit, 0) as balance'))
            ->get();

        // Categorize balances by account type
        $revenues = [];
        $expenses = [];

        foreach ($balances as $balance) {
            if ($balance->account_type == AccountTypeEnum::REVENUE) {
                $revenues[] = $balance;
            } elseif ($balance->account_type == AccountTypeEnum::EXPENSE) {
                $expenses[] = $balance;
            }
        }

        // Calculate totals
        $totalRevenue = array_sum(array_map(function($item) { return $item->balance; }, $revenues));
        $totalExpense = array_sum(array_map(function($item) { return abs($item->balance); }, $expenses));
        $netIncome = $totalRevenue - $totalExpense;

        $incomeStatement = [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netIncome' => $netIncome,
        ];

        $total_cash_in_hand = Method::sum('balance');

        return view('accounts.reports.income-statement', compact('incomeStatement','total_cash_in_hand'));
    }
}
