<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\Customer;
use App\Models\Income;
use App\Models\Invoice;
use App\Models\InvoicePay;
use App\Models\Logo;
use App\Models\Medicine;
use App\Models\Method;
use App\Models\Purchase;
use App\Models\PurchasePay;
use App\Models\Shop;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Toastr;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function dashboard()
    {
        $date = date('Y-m-d', time());
    
        // Today report
        $today_sell = Invoice::whereDate('created_at', $date)->count();
        $today_purchase = Purchase::whereDate('created_at', $date)->count();
        $today_sell_amount = Invoice::whereDate('created_at', $date)->sum('total_price');
        $today_purchase_amount = Purchase::whereDate('created_at', $date)->sum('total_price');
        $today_received = InvoicePay::whereDate('created_at', $date)->sum('amount');
        $today_paid = PurchasePay::whereDate('created_at', $date)->sum('amount');
    
        // Total Amount report
        $total_sell_amount = Invoice::sum('total_price');
        $customer = Invoice::select('total_price')->sum('total_price');
        $medicine = Batch::sum('qty');
    
        $expire = Batch::where('expire', '<=', $date)->paginate(10);
        $expired_shop = Shop::where('next_pay', '<=', $date)->take(8)->get();
    
        $income = Income::where('status', 0)->take(8)->get();
        $stockout = Medicine::whereHas('batch', function ($query) {
            $query->where('qty', '<', 1);
        })->paginate(10);
    
        $expire_medicines = Batch::where('expire', '<=', $date)->paginate(10);
        $stockout_medicines = Medicine::select('id','name')
            ->withCount(['batch as total_quantity' => function ($query) {
                $query->select(DB::raw('sum(qty)'));
            }])
            ->having('total_quantity', '<', 1)
            ->get();
    
        $total_customer_due = Customer::sum('due');
        $total_cash_in_hand = Method::sum('balance');
    
        // Pass data using compact
        return view('dashboard', compact(
            'today_sell', 'today_purchase', 'today_sell_amount', 'today_purchase_amount',
            'today_received', 'today_paid', 'total_sell_amount', 'customer', 'medicine',
            'expire', 'expired_shop', 'income', 'stockout', 'expire_medicines', 
            'stockout_medicines', 'total_customer_due', 'total_cash_in_hand'
        ));
    }
    

    public function settings(Request $request)
    {
        $shop = Shop::find(Auth::user()->shop_id);
        if ($request->isMethod('post')) {
            $shop->name = $request->name;
            $shop->site_title = $request->site_title;
            $shop->email = $request->email;
            $shop->phone = $request->phone;
            $shop->currency = $request->currency;
            $shop->address = $request->address;
            $shop->prefix = $request->prefix;
            $shop->theme = $request->theme;
            $shop->low_stock_alert = $request->low_stock_alert;
            $shop->time_zone = $request->time_zone;
            $shop->upcoming_expire_alert = $request->upcoming_expire_alert;
            // site logo
            if ($request->hasFile('site_logo')) {
                $image = $request->file('site_logo');
                $currentDate = Carbon::now()->toDateString();
                $logoimageName = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                if (!Storage::disk('public')->exists('images/admin/site_logo')) {
                    Storage::disk('public')->makeDirectory('images/admin/site_logo');
                }
                $logoImage = Image::make($image)->resize(100, 100)->stream();
                Storage::disk('public')->put('images/admin/site_logo/' . $logoimageName, $logoImage);
                $shop->site_logo = $logoimageName;
            } elseif (!empty($shop->site_logo)) {
                $shop->site_logo = $shop->site_logo;
            } else {
                $shop->site_logo = "default.png";
            }
            // favicon
            if ($request->hasFile('favicon')) {
                $image = $request->file('favicon');
                $currentDate = Carbon::now()->toDateString();
                $faviconimageName = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                if (!Storage::disk('public')->exists('images/admin/favicon')) {
                    Storage::disk('public')->makeDirectory('images/admin/favicon');
                }
                $favImage = Image::make($image)->resize(100, 100)->stream();
                Storage::disk('public')->put('images/admin/favicon/' . $faviconimageName, $favImage);
                $shop->favicon = $faviconimageName;
            } elseif (!empty($shop->favicon)) {
                $shop->favicon = $shop->favicon;
            } else {
                $shop->favicon = "default.png";
            }
            $shop->save();
            Toastr::success('Updated Succesfully', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
        return view('settings', compact('shop'));
    }

    public function uploadLogo(Request $request)
    {
        $data = new Logo();
        if ($request->isMethod('post')) {
            Toastr::error('You are in demo mode!', 'Error!');
            return redirect()->back();
            if ($request->hasFile('logo')) {
                $image = $request->file('logo');
                $currentDate = Carbon::now()->toDateString();
                $imageName = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();
                if (!Storage::disk('public')->exists('images/admin/banner/' . $data->image)) {
                    Storage::disk('public')->makeDirectory('images/admin/banner/' . $data->image);
                }
                $logoImage = Image::make($image)->resize(100, 100)->stream();
                Storage::disk('public')->put('images/admin/banner/' . $imageName, $logoImage);
                $data->logo = $imageName;
            } else {
                $data->logo = "default.png";
            }
            $data->user_id = Auth::user()->id;
            $data->save();
            Toastr::success('Logo Uploaded!', 'Success!');
            return redirect()->back();
            die;
        }
    }
}