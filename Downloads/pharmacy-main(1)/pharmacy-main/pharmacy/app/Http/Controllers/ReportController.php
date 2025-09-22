<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\hrm\Expense;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Method;
use Carbon\Carbon;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{

    public function customerDue(Request $request)
    {
        $shop_id = Auth::user()->shop_id;
        if ($request->ajax()) {
            $data = Customer::select('id', 'name', 'address', 'phone', 'due')->where('shop_id', Auth::user()->shop_id)->where('due', '>', 0)->orderBy('id', 'DESC');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('invoice_due', function ($row) {
                    return $invoice_due = Invoice::where('customer_id', $row->id)->where('due_price', '>', 0)->sum('due_price') ?? 00;
                })
                ->make(true);
        }
        // $total_invoice_dues = DB::table('customers')
        // ->join('invoices', 'customers.id', '=','invoices.customer_id')
        // ->select(DB::raw('sum(due_price) as total_invoice_due_price'))->get();
        $total_dues = DB::select(DB::raw("
        SELECT
        SUM(customer.due) AS previous_due,
        SUM(invoice.due_price) AS invoice_due
        FROM
            customers AS customer
        JOIN invoices AS invoice
        ON
            customer.id = invoice.customer_id
        WHERE
            customer.shop_id = $shop_id
        "));

        $total_previous_due = $total_dues[0]->previous_due;
        $total_invoice_due = $total_dues[0]->invoice_due;

        $customer = Customer::where('shop_id', Auth::user()->shop_id)->get();

        $total_cash_in_hand = Method::sum('balance');

        return view('reports.customer_due', compact('customer', 'total_invoice_due', 'total_previous_due','total_cash_in_hand'));
    }

    // public function supplierDue(Request $request)
    // {
    //     $shop_id = Auth::user()->shop_id;
    //     if ($request->ajax()) {
    //         $data = Purchase::where('shop_id', Auth::user()->shop_id)->groupBy('supplier_id')->selectRaw('sum(due_price) as invoice_due, supplier_id, id')->having('invoice_due', '>', 0);
    //         return Datatables::of($data)
    //             ->addIndexColumn()
    //             ->addColumn('previous_due', function ($row) {
    //                 return Supplier::where('id', $row->supplier_id)->where('due', '>', 0)->sum('due') ?? 00;
    //             })
    //             ->addColumn('name', function ($row) {
    //                 return $row->supplier->name;
    //             })
    //             ->addColumn('phone', function ($row) {
    //                 return $row->supplier->phone;
    //             })
    //             ->make(true);
    //     }
    //     $total_dues = DB::select(DB::raw("
    //         SELECT 
    //         SUM(purchase.due_price) AS invoice_payable,
    //         SUM(supplier.due) AS previous_payable 
    //         FROM
    //             purchases AS purchase
    //         JOIN suppliers AS supplier
    //         ON
    //            supplier.id = purchase.supplier_id 
    //         WHERE
    //             purchase.shop_id = $shop_id
    //         "));
    //     $total_previous_payable = $total_dues[0]->previous_payable;
    //     $total_invoice_payable = $total_dues[0]->invoice_payable;
    //     $suppliers = Supplier::where('shop_id', Auth::user()->shop_id)->get();

    //     return view('reports.supplier_due', compact('suppliers', 'total_previous_payable', 'total_invoice_payable'));
    // }


    public function topSellMedicine(Request $request)
{
    $keyword = $request->keyword ?? '';
    $from_date = '';
    $to_date = '';

    if (!empty($request->from) && !empty($request->to)) {
        $from_date = $request->from;
        $to_date = $request->to;
    } else {
        $from_date = date('Y-m-d', strtotime("-7 days", time()));
        $to_date = date('Y-m-d');
    }

    $dates = list_days($from_date, $to_date);
    $medicineIds = [];
    $query = Invoice::select('id', 'medicines')
                    ->where('shop_id', Auth::user()->shop_id)
                    ->whereIn('date', $dates);

    // Fetch the latest 10 invoices and apply pagination
    $sells = $query->latest()->paginate(10);  // Pagination added here

    $medicines = [];
    foreach ($sells as $sell) {
        $sold_medicines = json_decode($sell->medicines, true);
        foreach ($sold_medicines as $key => $medicine) {
            if (is_array($medicine)) {
                $mquery = Medicine::where('id', $medicine['id'])
                                  ->select('name', DB::raw('count(*) as total'), 'id', 'generic_name')
                                  ->groupBy('name');
                $mData = $mquery->get();

                foreach ($mData as $data) {
                    $report['total_sale'] = $data['total'];
                    $report['id'] = $data['id'];
                    $report['name'] = $data['name'];
                    $report['generic_name'] = $data['generic_name'];
                    array_push($medicines, $report);
                }
            }
        }
    }

    $total_cash_in_hand = Method::sum('balance');

    return view('reports.topsell_medicine', compact('medicines', 'keyword', 'from_date', 'to_date', 'sells','total_cash_in_hand'));
}



    // Business Profit & Loss
    public function businessProfitLoss(Request $request)
    {

        $year = now()->year;
        if (!empty($request->year)) {
            $year = $request->year;
        }

        $totalSale = Invoice::sum('total_price');
        $totalSaleQuantity = Invoice::sum('qty');

        $totalPurchase = Purchase::sum('total_price');
        $totalPurchaseQuantity = Purchase::sum('qty');

        $balanceInhand = Method::sum('balance');
        $totalExpenses = Expense::sum('amount');


        $salesData = $this->getData('sales', 'invoices', 'total_price', $year);
        $purchasesData = $this->getData('purchases', 'purchases', 'total_price', $year);
        $expensesData = $this->getData('expenses', 'expenses', 'amount', $year);

        $monthlyData = [];

        foreach ($salesData as $sales) {
            $month = $sales->month;

            $monthlyData[$month]['month'] = date('F', mktime(0, 0, 0, $month, 1));
            $monthlyData[$month]['total_sales'] = $sales->total_sales;

            $monthlyData[$month]['total_purchases'] = 0;
            foreach ($purchasesData as $purchases) {
                if ($purchases->month == $month) {
                    $monthlyData[$month]['total_purchases'] = $purchases->total_purchases;
                    break;
                }
            }

            $monthlyData[$month]['total_expenses'] = 0;
            foreach ($expensesData as $expenses) {
                if ($expenses->month == $month) {
                    $monthlyData[$month]['total_expenses'] = $expenses->total_expenses;
                    break;
                }
            }
            $monthlyData[$month]['profit_loss'] = $monthlyData[$month]['total_sales'] - ($monthlyData[$month]['total_purchases'] + $monthlyData[$month]['total_expenses']);
        }
        return view('reports.business_profitloss',
            compact('totalSale', 'totalSaleQuantity', 'totalPurchase', 'totalPurchaseQuantity'
                , 'totalExpenses', 'balanceInhand', 'monthlyData', 'year'));
    }

    private function getData($field, $table, $amount_field, $year)
    {
        return DB::table($table)
            ->select(DB::raw('MONTH(date) as month'), DB::raw('SUM(' . $amount_field . ') as total_' . $field))
            ->whereYear('date', $year)
            ->groupBy('month')
            ->get();
    }


    public function inStockMedicine(Request $request)
    {
        $today = now();
        $limit = $request->input('limit', 100);
        $collection = Medicine::select('id', 'name')
            ->when($request->keyword, function ($query) use ($request) {
                {
                    $query->where('name', 'LIKE', "%$request->keyword%");
                }
            })
            ->whereHas('batch', function ($query) use ($today) {
                $query->whereDate('expire', '>', $today)
                    ->where('qty', '>', 9);
            })
            ->with(['batch' => function ($query) use ($today) {
                $query->whereDate('expire', '>', $today)
                    ->where('qty', '>', 9);
            }])
            ->withSum(['batch as total_qty' => function ($query) use ($today) {
                $query->whereDate('expire', '>', $today)
                    ->where('qty', '>', 9);
            }], 'qty')
            ->orderBy('name', 'ASC')
            ->paginate($limit);
            $total_cash_in_hand = Method::sum('balance');

        return view('reports.instock', compact('collection','total_cash_in_hand'));
    }

   public function lowStockMedicine(Request $request)
{
    $today = \Carbon\Carbon::today()->toDateString();
    $limit = $request->input('limit', 10);

    // Query to fetch medicines with related data
    $collection = Medicine::with([
        'batch' => function ($query) use ($today) {
            $query->select('id', 'medicine_id', 'name', 'qty')
                  ->where('qty', '<', 9)
                  ->whereDate('expire', '>', $today)
                  ->where('qty', '>', 2);
        },
        'supplier'
    ])
    ->select('id', 'name', 'supplier_id', 'generic_name')
    ->when($request->keyword, function ($query) use ($request) {
        $query->where('name', 'LIKE', "%{$request->keyword}%");
    })
    ->orderBy('name', 'ASC')
    ->paginate($limit);

    // Calculate total cash in hand
    $total_cash_in_hand = Method::sum('balance');

    return view('reports.lowstock', compact('collection', 'total_cash_in_hand'));
}

    
    
    
    

    public function stockoutMedicine(Request $request)
    {
        $limit = $request->input('limit', 10);
    $collection = Medicine::with('batch', 'supplier') // Ensure 'batch' relationship is loaded
        ->select('id', 'name', 'supplier_id', 'generic_name')
        ->when($request->keyword, function ($query) use ($request) {
            $query->where('name', 'LIKE', "%{$request->keyword}%");
        })
        ->whereHas('batch', function ($query) {
            $query->where('qty', '<', 1);
        })
        ->orderBy('name', 'ASC')
        ->paginate($limit);
    $total_cash_in_hand = Method::sum('balance');

    return view('reports.stockout', compact('collection','total_cash_in_hand'));
    }

    public function upcomingExpireMedicine(Request $request)
    {
        // Get today's date
        $today = Carbon::today(); // Use Carbon for easier date manipulation
    
        // Set the range for upcoming expiration (default is 2 days)
        $alertDate = setting('upcoming_expire_alert') ?? 2; // Use a default of 2 days
        $upcomingDate = $today->copy()->addDays($alertDate); // Calculate the upcoming date (today + alert days)
    
        // Pagination limit (default is 10)
        $limit = $request->input('limit', 10);
    
        // Query to fetch batches where expiration date is before or on today's date
        $today = Carbon::today(); // Today's date
        $tomorrow = Carbon::tomorrow(); // Tomorrow's date
        
        $collection = Batch::with('medicine')
            ->where(function($query) use ($today, $tomorrow) {
                $query->whereDate('expire', '=', $today) // Expiry is today
                      ->orWhereDate('expire', '=', $tomorrow); // Or expiry is tomorrow
            })
            ->latest('expire') // Sort by expiration date (latest first)
            ->when($request->field && $request->keyword, function ($query) use ($request) {
                // Apply additional filters based on the field and keyword
                $query->where($request->field, 'LIKE', "%$request->keyword%");
            })
            ->paginate($limit); // Pagination
        // Get total cash in hand
        $total_cash_in_hand = Method::sum('balance');
    
        // Pass the data to the view
        return view('reports.upcoming_expire', compact('collection', 'total_cash_in_hand'));
    }
    

    

    public function alreadyExpireMedicine(Request $request)
    {
        $today = date('Y-m-d', time());
        $limit = $request->input('limit', 10);
        $collection = Batch::with(['medicine' => function ($query) use ($request) {
            $query->when($request->keyword, function ($q) use ($request) {
                $q->where('name', 'LIKE', "%$request->keyword%");
            });
        }])->when($request->date, function ($q) use ($request) {
            $q->whereDate('expire', $request->date);
        })
            ->where('expire', '<=', $today)
            ->latest('expire')
            ->paginate($limit);
        $total_cash_in_hand = Method::sum('balance');
        return view('reports.already_expire', compact('collection','total_cash_in_hand'));
    }

    public function dueCustomer(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $limit = $request->input('limit') ?? 10;
        $collection = Customer::select('id', 'name', 'address', 'phone', 'email', 'due')
            ->where('store_id', $store_id)
            ->where('due', '>', 0)
            ->when($request->field && $request->keyword, function ($query) use ($request) {
                $query->where($request->field, 'LIKE', "%$request->keyword%");
            })->paginate($limit);
        return view('store.reports.due_customer', compact('collection'));
    }

    public function payableManufacturer(Request $request)
    {
        $store_id = Auth::user()->store_id;
        $limit = $request->input('limit') ?? 10;
        $collection = Manufacturer::select('id', 'name', 'address', 'phone', 'payable')
            ->where('store_id', $store_id)
            ->where('payable', '>', 0)
            ->when($request->field && $request->keyword, function ($query) use ($request) {
                $query->where($request->field, 'LIKE', "%$request->keyword%");
            })->paginate($limit);

            $total_cash_in_hand = Method::sum('balance');
        return view('store.reports.payable_manufacturer', compact('collection','total_cash_in_hand'));
    }

    public function salePurchase(Request $request)
    {
        $store_id = Auth::user()->store_id;

        $from_date = date('Y-m-d', strtotime("-7 day", time()));
        $to_date = date('Y-m-d');

        if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
        }


        $dates = list_days($from_date, $to_date);
        $collection = [];

        foreach ($dates as $date) {
            $sales = Sale::where('store_id', $store_id)
                ->withCount(['saleDetails as total_sale_quantity' => function ($query) {
                    $query->select(DB::raw('sum(quantity)'));
                }])->whereDate('created_at', $date)
                ->get();

            $totalSalesQuantity = $sales->sum('total_sale_quantity');
            $totalSalesAmount = $sales->sum('paid_amount');

            $totalPurchases = Purchase::where('store_id', $store_id)
                ->withCount(['batch as total_quantity' => function ($query) {
                    $query->select(DB::raw('sum(quantity)'));
                }])->whereDate('purchase_date', $date)
                ->get();
            $totalPurchaseQuantity = $totalPurchases->sum('total_quantity');
            $totalPurchaseAmount = $totalPurchases->sum('total');
            $profitOrLoss = $totalSalesAmount - $totalPurchaseAmount;
            $collection[$date] = [
                'total_sale_quanity' => $totalSalesQuantity,
                'total_sale_amount' => $totalSalesAmount,
                'total_purchases_quantity' => $totalPurchaseQuantity,
                'total_purchases_amount' => $totalPurchaseAmount,
                'profitOrLoss' => $profitOrLoss,
            ];
        }

        return view('store.reports.sale_purchase', compact('collection'));
    }


    public function profitLoss(Request $request)
    {
        $store_id = Auth::user()->store_id;

        $from_date = date('Y-m-d', strtotime("-7 day", time()));
        $to_date = date('Y-m-d');

        if (!empty($request->input('from_date')) && !empty($request->input('to_date'))) {
            $from_date = $request->input('from_date');
            $to_date = $request->input('to_date');
        }


        $dates = list_days($from_date, $to_date);
        $collection = [];
        // Loop through each day in the date range
        foreach ($dates as $date) {
            $store_id = Auth::user()->store_id;
            $reports = DB::select("
            SELECT
                SUM((batches.buy_price * sale_details.quantity) - batches.discount) as total_purchases_amount,
                SUM(sales.grand_total) as total_sale_amount,
                SUM(sale_details.quantity) as total_sale_quantity,
                SUM(batches.quantity) as total_purchases_quantity
            FROM sales
            INNER JOIN sale_details ON sales.id = sale_details.sale_id
            INNER JOIN batches ON sale_details.batch_id = batches.id
            INNER JOIN purchases ON batches.purchase_id = purchases.id
            WHERE
                sales.store_id = :store_id
                AND DATE(sales.created_at) = :date
        ", ['store_id' => $store_id, 'date' => $date])[0];

            $collection[$date] = [
                'total_sale_quantity' => $reports->total_sale_quantity,
                'total_sale_amount' => $reports->total_sale_amount,
                'total_purchases_quantity' => $reports->total_purchases_quantity,
                'total_purchases_amount' => $reports->total_purchases_amount,
                'profitOrLoss' => ($reports->total_sale_amount - $reports->total_purchases_amount),
            ];
        }

        $total_cash_in_hand = Method::sum('balance');
        return view('store.reports.profit_loss', compact('collection','total_cash_in_hand'));
    }

   
    public function supplierDue(Request $request)
    {
        $shop_id = Auth::user()->shop_id;
    
        // Get filters from request
        $keyword = $request->keyword ?? '';
        $from_date = $request->from ?? date('Y-m-d', strtotime("-7 days"));
        $to_date = $request->to ?? date('Y-m-d');
    
        // Grouping and fetching supplier dues data with filters
        $query = Purchase::where('shop_id', $shop_id)
            ->groupBy('supplier_id')
            ->selectRaw('sum(due_price) as invoice_due, supplier_id, id')
            ->having('invoice_due', '>', 0);
    
        // Apply date filters
        if ($from_date && $to_date) {
            $query->whereBetween('created_at', [$from_date, $to_date]);
        }
    
        // Apply pagination
        $data = $query->paginate(10);
    
        // Collect data for display
        $suppliers_with_dues = $data->map(function ($row) use ($keyword) {
            $supplier = Supplier::find($row->supplier_id);
    
            // Apply keyword filter
            if ($keyword && (!str_contains($supplier->name ?? '', $keyword) && !str_contains($supplier->phone ?? '', $keyword))) {
                return null; // Exclude rows that don't match the keyword
            }
    
            return [
                'name' => $supplier->name ?? 'Unknown',
                'phone' => $supplier->phone ?? 'N/A',
                'invoice_due' => $row->invoice_due ?? 0,
                'previous_due' => $supplier->due ?? 0,
            ];
        })->filter(); // Remove null entries after applying keyword filter
    
        // Calculating total dues using Eloquent
        $total_previous_payable = Supplier::whereHas('purchases', function ($query) use ($shop_id) {
            $query->where('shop_id', $shop_id);
        })->sum('due');
    
        $total_invoice_payable = Purchase::where('shop_id', $shop_id)->sum('due_price');
    
        // Fetch all suppliers for display if needed
        $suppliers = Supplier::where('shop_id', $shop_id)->get();
    
        $total_cash_in_hand = Method::sum('balance');
    
        return view('reports.supplier_due', compact(
            'suppliers_with_dues',
            'total_previous_payable',
            'total_invoice_payable',
            'suppliers',
            'keyword',
            'from_date',
            'to_date',
            'data',
            'total_cash_in_hand'
        ));
    }
    
    

    public function supplierreport(Request $request)
{
    $paginate = $request->input('paginate', 10); // Default to 10 items per page
    $supplierId = $request->input('supplier_id'); // Get the supplier filter value
    $statusFilter = $request->input('status'); // Get the status filter value

    // Fetch data from the Purchase model with pagination and optional filtering
    $query = Purchase::with(['supplier', 'method'])
        ->select('inv_id', 'supplier_id', 'date', 'subtotal', 'due_price', 'total_price');

    if ($supplierId) {
        $query->where('supplier_id', $supplierId); // Filter by supplier if provided
    }

    if ($statusFilter) {
        $query->whereRaw("CASE 
                            WHEN due_price = 0 THEN 'Fully Paid' 
                            WHEN due_price > 0 AND due_price < total_price THEN 'Advance' 
                            ELSE 'Pending' 
                          END = ?", [$statusFilter]); // Filter by status if provided
    }

    $purchases = $query->paginate($paginate)
        ->through(function ($purchase) {
            $purchase->balance = $purchase->total_price - $purchase->due_price; // Calculate balance

            // Determine status based on the prices
            if ($purchase->due_price == 0) {
                $purchase->status = 'Fully Paid';
            } elseif ($purchase->due_price > 0 && $purchase->due_price < $purchase->total_price) {
                $purchase->status = 'Advance';
            } else {
                $purchase->status = 'Pending';
            }

            return $purchase;
        });

    // Fetch all suppliers for the dropdown
    $suppliers = Supplier::select('id', 'name')->get();

    $total_cash_in_hand = Method::sum('balance');

    return view('reports.supplierreport', compact('purchases', 'suppliers','total_cash_in_hand'));
}


    
    


}