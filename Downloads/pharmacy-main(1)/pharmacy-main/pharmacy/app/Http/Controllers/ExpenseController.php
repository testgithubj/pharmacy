<?php

namespace App\Http\Controllers;

use App\Models\Account\Account;
use App\Models\ExpenseCategory;
use App\Models\PharmacyExpense;
use App\Models\Method;
use Illuminate\Validation\Rule;  
use App\Service\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        // Fetch categories
        $categories = ExpenseCategory::select('id', 'name')->get();
        $category_id = $request->category_id;
        
        // Create the query for fetching expenses
        $query = PharmacyExpense::query();
        
        // Apply the category filter if provided
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
        
        // Paginate the results
        $collection = $query->paginate(10);
        
        // Calculate the total cash in hand
        $total_cash_in_hand = Method::sum('balance');
        
        // Return the view with necessary data
        return view('expenses.index', compact('collection', 'categories', 'category_id', 'total_cash_in_hand'));
    }
    
    


    


    
    public function create()
    {
        // Fetch only active categories
        $categories = ExpenseCategory::select('id', 'name', 'status')->where('status', 'active')->get();
    
        // Fetch active accounts
        $accounts = Account::select('id', 'name')->where('status', 'active')->get();
    
        // Get total cash in hand
        $total_cash_in_hand = Method::sum('balance');
    
        return view('expenses.create', compact('categories', 'accounts', 'total_cash_in_hand'));
    }
    

    public function info_setting(Request $request)
{
    if ($request->isMethod("post")) {
        if (Auth::user()->shop_id == 34 || Auth::user()->shop_id == 47) {
            Toastr::error('You are in demo mode!', 'Error!');
            return redirect()->back(); die;
        }

        $id = Auth::user()->id;
        $user = User::find($id);
        $user->email = $request->email;
        $user->name = $request->name;

        // Check if an image is uploaded
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $currentDate = Carbon::now()->toDateString();
            $imageName = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Check if the directory exists, create if it doesn't
            if (Storage::disk('public')->exists('images/profile')) {
                Storage::disk('public')->makeDirectory('images/profile');
            }

            // Delete old image if it exists
            if (Storage::disk('public')->exists('images/profile/' . $user->image)) {
                Storage::disk('public')->delete('images/profile/' . $user->image);
            }

            // Resize and save the new image
            $bannerImage = Image::make($image)->resize(1500, 1000)->stream();
            Storage::disk('public')->put('images/profile/' . $imageName, $bannerImage);
            $user->image = $imageName;
        } else {
            // If no image is uploaded, use default image
            $user->image = 'employee/9977332.png';
        }

        $user->save();

        Toastr::success('Profile updated!', 'Success!');
        return redirect()->back(); die;
    }

    return view("info_reset");
}


public function store(Request $request)
{
    try {
        // Validate the incoming request
        $this->validation($request);

        // Start a database transaction to ensure atomicity
        DB::transaction(function () use ($request) {
            // Store the expense
            $expense = PharmacyExpense::create([
                'date' => $request->date,
                'category_id' => $request->category_id,
                'title' => $request->title,
                'amount' => $request->amount,
                'account_id' => $request->account_id, // Debit account
                'credit_account_id' => $request->credit_account_id, // Credit account
                'reference' => $request->reference,
                'note' => $request->note,
            ]);

            // Record the expense transaction (for debit and credit)
            TransactionService::expenseTransaction($request->amount, $request->account_id, $request->title);
            TransactionService::expenseTransaction(-$request->amount, $request->credit_account_id, $request->title, true); // The negative amount for credit transaction
        });

        // Success message and redirect
        successAlert('Created successfully');
        return redirect()->route('expenses.index');
    } catch (\Exception $e) {
        // Handle any errors
        errorAlert($e->getMessage());
        return redirect()->back();
    }
}





    public function edit(PharmacyExpense $expense)
    {
        $categories = ExpenseCategory::select('id','name')->get();
        $accounts = Account::select('id','name')->where('status', 'active')->get();
        return view('expenses.edit',compact('categories','expense','accounts'));
    }

    public function update(Request $request, PharmacyExpense $expense)
    {
        try {
            $this->validation($request);
            $expense->update($request->all());
            // Success message
            successAlert('Updated successfully');
            return redirect()->route('expenses.index');
        } catch (\Exception $e) {
            // Error message
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    public function delete(PharmacyExpense $expense)
    {
        try {
            $expense->delete();
            // Success message
            successAlert('Deleted successfully');
            return redirect()->route('expenses.index');
        } catch (\Exception $e) {
            // Error message
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    private function validation($request, $id = null)
    {
        $request->validate([
            'date' => 'required',
            'category_id' => 'required',
            'title' => 'required',
            'amount' => 'required',
        ]);
    }
}
