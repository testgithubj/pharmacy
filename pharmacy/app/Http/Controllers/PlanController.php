<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Shop;
use App\Models\Income;
use App\Models\Package;
use App\Models\Method;
use shurjopayv2\ShurjopayLaravelPackage8\Http\Controllers\ShurjopayController;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\DataTables;
class PlanController extends Controller
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

     public function history(Request $request)
    {
        
         if ($request->ajax()) {
            $data = Income::select('*')->where('shop_id', Auth::user()->shop_id);
             return Datatables::of($data)
                    ->addIndexColumn()
                    
                    ->addColumn('package', function($row){
                    $data = Package::where('id', $row->package_id)->first();
                    if($data != null){
                        return $data->name;
                    }
                    })
                    ->addColumn('stats', function($row){
                    if($row->status == 1){
                        return '<span class="badge bg-success">Approved</span>';
                    } else {
                        return '<span class="badge bg-info">Pending</span>';
                    }
                    })
                    ->addColumn('method', function($row){
                    return  Method::where('id', $row->method_id)->first()->name;
                    })
                    ->rawColumns(['stats'])
                    ->addIndexColumn()
                    ->make(true);
        }
    
    return view('plan.history');
    }
    
    public function renew(Request $request)
    {
         $plan = Package::where('trial', '!=', 1)->get();
           
           if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $oid = 'AYA'.uniqid();
           
        $oprice = Package::find($request->package_id);
           
        if ($request->isMethod('post')) {
            
            $amt = ($oprice->price*$request->duration);
            if(Auth::user()->shop->status != 1){
                $amt += $request->setup_fee;
            }
            
            $income = new Income();
            $income->duration = $request->duration;
            $income->date = date('Y-m-d');
            $income->method_id = 6;
            
            $income->user_id = Auth::user()->id;
            $income->package_id = $request->package_id;
            $income->shop_id = Auth::user()->shop_id;
            $income->amount = $amt;
            if($income->save()){
                $info = array( 'prefix' => "AYA",'currency' => "BDT", 'amount' => $amt, 'order_id' => $oid, 'discsount_amount' => 0, 'disc_percent' => 0, 'client_ip' => $ip, 'customer_name' => Auth::user()->name, 'customer_phone' => Auth::user()->shop->phone, 'email' => Auth::user()->email, 'customer_address' => Auth::user()->shop->address, 'customer_city' => "Dhaka", 'customer_state' => "Dhaka", 'customer_country' => "Bangladesh", );
       
        
        $shurjopay_service = new ShurjopayController(); 
        
        return $shurjopay_service->checkout($info);
            }
        }
        
        
        
            
    return view('plan.add', compact('plan'));
    }
    
    
    public function index(Request $request)
    {
        
       
            $plan = Shop::where('id', Auth::user()->shop_id)->first();
            
    return view('plan.list', compact('plan'));
    }
}
