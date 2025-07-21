<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\Method;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $collection = ExpenseCategory::orderBy('created_at', 'desc') // Sort by creation date in descending order
        ->paginate(10); // Adjust the number of items per page as needed
        $total_cash_in_hand = Method::sum('balance');
        return view('expense_category.index', compact('collection','total_cash_in_hand'));
    }

    public function create()
    {
        $total_cash_in_hand = Method::sum('balance');
        return view('expense_category.create', compact('total_cash_in_hand'));
    }

    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|unique:expense_categories,name', // Ensure 'name' is unique
        'status' => 'required',
    ]);

    try {
        // Create the new record
        ExpenseCategory::create($request->all());

        // Success message
        successAlert('Created successfully');
        return redirect()->route('expense-categories.index');
    } catch (\Exception $e) {
        // Error message
        errorAlert($e->getMessage());
        return redirect()->back();
    }
}


    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense_category.edit', compact('expenseCategory'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        $request->validate([
            'name' => 'required',
            'status' => 'required',
        ]);
        try {
            $expenseCategory->update($request->all());

            // Success message
            successAlert('Updated successfully');
            return redirect()->route('expense-categories.index');
        } catch (\Exception $e) {
            // Error message
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function delete(ExpenseCategory $expenseCategory)
    {
        try {
            // Delete the record
            $expenseCategory->delete();
            // Success message
            successAlert('Deleted successfully');
            return redirect()->route('expense-categories.index');
        } catch (\Exception $e) {
            // Error message
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }
}
