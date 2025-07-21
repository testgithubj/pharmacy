<?php

namespace App\Http\Controllers;
use Mail;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Thana;
use App\Models\District;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Shop;
use App\Models\User;
use App\Models\Cart;
use App\Models\Income;
use App\Models\Coupon;
use Illuminate\Support\Facades\Auth;
use App\Models\Medicine;
use shurjopayv2\ShurjopayLaravelPackage8\Http\Controllers\ShurjopayController;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Brian2694\Toastr\Facades\Toastr;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
  
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
       
        $package = Package::get();
        return view('home', compact('package'));
    }
    
    
    
    public function demologin($username)
    {
        if($username == 'ashtha' || $username == 'surokkha' ){
        $shop = Shop::where('username', $username)->firstOrFail();
        $user = User::where('shop_id', $shop->id)->firstOrFail();
        auth()->login($user, true);
        Toastr::success('Logged In As '.$user->name.'');
        return redirect()->route('dashboard');
        }
    }
    public function thank(Request $request, $username, $id)
    {   
        $data['shop'] = Shop::where('username', $username)->first();
        $data['order'] = Invoice::where('id', $id)->where('shop_id', $data['shop']->id)->first();
        if($data['order'] == null){
        Toastr::error('Unknown Error', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home', $data['shop']->username);  
        }
        return view('thankyou')->with($data);
    }
    public function terms()
    {
        return view('terms');
    }
    public function contact(Request $request, $username)
    {
        $shop = Shop::where('username', $username)->first();
        return view('contacts', compact('shop'));
    }
    
    public function contacts(Request $request)
    {
       return view('contact');
    }
    
    public function login(Request $request, $username)
    {
        $shop = Shop::where('username', $username)->first();
        
         if($request->isMethod('post')){
            $data= $request->all();
            if(Auth::guard('customer')->attempt(['email' => $data['email'], 'password' => $data['password'], 'shop_id' => $shop->id])){
                Toastr::success("You are logged in!");
                return redirect()->route('shop.index', $username);;die;
            }else{
                Toastr::error("Credentials do not match!");
                return redirect()->back();die;
            }
        }
        
        return view('signin', compact('shop'));
    }
    
    public function delcart(Request $request, $username, $id)
    {
    $shop = Shop::where('username', $username)->first();
    Cart::where('user_id', session()->get('cartid'))->where('id', $id)->where('shop_id', $shop->id)->delete();
     Toastr::success('Cart Added', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->back();  
    }
    public function addcart(Request $request, $username, $id)
    {
        $shop = Shop::where('username', $username)->first();
        $cart = Cart::where('user_id', session()->get('cartid'))->where('medicine_id', $id)->where('shop_id', $shop->id)->first();
        if($cart != null){
            $cart->qty += 1;
            $cart->save();
        } else {
            $new = new Cart();
            $new->shop_id = $shop->id;
            $new->medicine_id = $id;
            $new->qty = 1;
            $new->user_id = session()->get('cartid');
            $new->save();
        }
        Toastr::success('Cart Added', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->back();   
        
    }
    
    
    public function order(Request $request, $username)
    {
        $shop = Shop::where('username', $username)->first();
        
        $customer = new Customer();
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        if($request->filled('due')){
        $customer->due = $request->due;    
        }
        $customer->shop_id = $shop->shop_id;
        $customer->thana_id = $shop->thana_id;
        $customer->district_id = $shop->district_id;
    if($customer->save()){
        
        
        $cart = Cart::where('user_id', session()->get('cartid'))->where('shop_id', $shop->id)->get();
        
         $purchase = new Invoice();
            $purchase->customer_id = $customer->id;
            $purchase->date = date('Y-m-d');
            $offerNo = Invoice::count();
            $purchase->total_price = $request->amount;
            $purchase->due_price = $request->amount;
            $purchase->inv_id = uniqueOrderId($offerNo, $shop->prefix, 'purchases', 'inv_id');
           
            $purchase->medicines = json_encode($cart->toArray());
            $purchase->type = 'ecommerce';
                if($request->due>0){
                $sup = Customer::where('id', $customer->id)->first();
                if($sup != null){
                $sup->due += $request->due;
                $sup->save();
                }
                }
            $purchase->union_id = $shop->union_id;
            $purchase->shop_id = $shop->id;
            
            $purchase->district_id = $shop->district_id;
            $purchase->thana_id = $shop->thana_id;
            if($purchase->save()){
               
               foreach($cart as $test){
                   Cart::where('id', $test->id)->delete();
               }
                
                Toastr::success('Customer successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect('/thank/'.$purchase->id.'');
            } else {
                Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->back();
            }
        
        
        
        
        
        
    }
     
     
    }
    
    
    public function shop(Request $request, $username)
    {
        
        
        if($username == 'pvl'){
            
            if(!Auth::guard('customer')->check()){
                
             return redirect()->route('signin', $username);     
                
            }
            
        }
        $shop = Shop::where('username', $username)->first();
        if($shop != null){
            
         if (strtotime($shop->next_pay) < time()) {
         Toastr::success('Shop Is Expired', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home');   
        }   
        
        if ($shop->package_id == 5) {
         Toastr::success('Unauthorized Access', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home');   
        }    
            
        $medicine = Medicine::where('hot', 1)->where(function($q) use($shop) {
          $q->where('shop_id', $shop->id)
            ->orWhere('global', 1);
      })->orderBy('created_at','desc')->paginate(16);
        return view('shop', compact('shop','medicine'));
        } else {
         Toastr::success('Incorrect Link', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home');   
        }
        
        
        
    }
    
    
     public function cart(Request $request, $username)
    {
        $shop = Shop::where('username', $username)->first();
        if($shop != null){
            
         if (strtotime($shop->next_pay) < time()) {
         Toastr::success('Shop Is Expired', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home');   
        }   
        
        if ($shop->package_id == 5) {
         Toastr::success('Unauthorized Access', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home');   
        }    
            
        $medicine = Cart::where('user_id', session()->get('cartid'))->where('shop_id', $shop->id)->orderBy('created_at','desc')->paginate(16);
        return view('cart', compact('shop','medicine'));
        } else {
         Toastr::success('Incorrect Link', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('home');   
        }
    }
    
    
    public function buy(Request $request, $id)
    {
        $district = District::all();
        $package = Package::findOrfail($id);
        return view('buy', compact('package','district'));
    }
    public function approve_order(Request $request, $id){
        
        $income = Income::where('inv_id',$id)->first();
        $income->status = 1;
        $income->save();
        $shop = Shop::find($income->shop_id);
        $shop->next_pay = date('Y-m-d', strtotime("+$request->duration month", strtotime($shop->next_pay)));
        $shop->status = 1;
        if($shop->save()){
       Toastr::success('Invoices successfully Approved', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
        return redirect()->route('saas.invoice');
        }
        
    }
    public function place_order(Request $request)
    {
        $district = District::findOrfail($request->district_id);
        $thana = Thana::findOrfail($request->thana_id);
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $oid = 'AYA'.uniqid();
        
        
         if(User::where('email', $request->email)->first() != null){
            Toastr::error('User With The Mail Exists Already', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
        if(!empty($request->coupon_number)){
        if($request->coupon_number != $request->phone){
            Toastr::error('UnAuthorized Coupon Used', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
        }
        if(Shop::where('phone', $request->phone)->first() != null){
            Toastr::error('User With The Phone Exists Already', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
        if(Shop::where('username', $request->username)->first() != null){
            Toastr::error('Shop With The Username Exists Already', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }
        $package = Package::find($request->package_id);
        $shop = new Shop();
        $shop->name = $request->shop_name;
        $shop->email = $request->email;
        $shop->phone = $request->phone;
        $shop->username = $request->username;
        $shop->currency = 'BDT';
        $shop->district_id = $request->district_id;
        $shop->thana_id = $request->thana_id;
        $shop->address = $request->address;
        $shop->theme = 'light';
        $shop->package_id = $request->package_id;
        $shop->last_renew = date('Y-m-d');
        if($package->trial == 0){
        $shop->status = 0;
        $shop->next_pay = date('Y-m-d');
        } else {
        $shop->status = 0;  
        $shop->next_pay = date('Y-m-d', strtotime("+$request->duration days"));
        }
        
        
        if($request->hasFile('image'))
                {
                $image=$request->file('image');
                $currentDate=Carbon::now()->toDateString();
                $imageName=$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
                if(Storage::disk('public')->exists('images/nid_card'))
                {
                   Storage::disk('public')->makeDirectory('images/nid_card');
                }
                if(Storage::disk('public')->exists('images/nid_card/'.$shop->image))
                {
                   Storage::disk('public')->delete('images/nid_card/'.$shop->image);
                }
                $bannerImage = Image::make($image)->resize(1500,1000)->stream();
                Storage::disk('public')->put('images/nid_card/'.$imageName,$bannerImage);
                $shop->image=$imageName;
                }
        
        if($shop->save()){
        
        
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->name = $request->name;
        $user->shop_id = $shop->id;
        
        $customer = new Customer();
        $customer->name = 'OTC Customer';
        $customer->phone = '0000000000';
        $customer->address = $request->address;
        $customer->shop_id = $shop->id;
        $customer->thana_id = $shop->thana_id;
        $customer->district_id = $shop->district_id;
        $customer->save();
         if($user->save()){
             
             
             
             
             
             $msg = '
<!DOCTYPE html>
<html>
    <head>

        <meta charset="utf-8">
        <meta http-equiv="x-ua-compatible" content="ie=edge">
        <title>Email Confirmation</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
            *{margin: 0px; padding: 0px;font-family:sans-serif;}
            .footer{}
            .main{width: 500px; border: 1px solid #999999; height: 800px; margin: 0px auto;}
            .logo{height: 60px; background: #720000;display: block;}
            .logo h2{color:#ffffff; text-align: center; padding-top: 15px;display: block;}
            .welcome h1{color:#720000; text-align: center; padding: 40px;display: block;}
            .message {display: block;}
            .message p{padding: 0px 20px 20px 20px; display: block;text-align: justify;}
            .message a{color:#c60000; text-decoration: none;}
            .btn a{background: #c60000; color:#ffffff; font-weight: 500; padding: 10px; text-decoration: none; text-align: center;}
            .btn{margin-bottom: 50px; display: block;}
            .btn h3{text-align: center;}
            .social{padding: 20px 20px 0px 20px; display:block; background-color: #eaeaea;}
            .social p{color:#777777; font-size: 13px;}
            .social2{padding: 20px; display:block; background-color: #eaeaea;}
        </style>
        <script src=\'https://kit.fontawesome.com/a076d05399.js\' crossorigin=\'anonymous\'></script>

    </head>
    <body>
        <div class="main">
            <div class="content">
                <div class="logo"><h2>Ayaan Tech Limited</h2></div>
                <div class="welcome"><h1>WELCOME, '.$request->name.'</h1></div>
                <div class="message">
                    <p><b>Hello, '.$request->name.'</b><br><br>
                        Thanks for your registration and make your software order. We are really appriaciate your interest to our software. </p>
                    <p>Hope you will manage your business more easier than previous.</p>
                    <p>Thanks</p>
                </div>
                <div class="btn"><h3><a href="https://pharmacyss.com/login">Log In to Your Account</a></h3></div>
                <div class="message">
                    <p><b>Have a Question?</b><br><br>
                        Check Our <a href="#">Knowledge Base</a> for Quick Answer </p>
                    <p>You can always contact our <a href="#">24/7 Support Team</a> via Live chat or email we will be happy to help you</p>
                </div>
            </div>
            <div class="footer">
                <div class="social">
                    <img width="250px" src="http://ayaantec.com/public/assets/frontend/uploads/logo.png">
                    <p><b>Ayaan Tech Limited</b><br>House-24, Road-14, Block-G,
                    <br>Niketon, Gulshan-01, Dhaka-1212<br>Mobile:   +88 01973198574<br> Email:   connect@ayaantec.com</p>
                </div>
                <div class="social2">
                    <span><b>Follow Us:  </b></span>
                    <a><i style=\'font-size:24px; color:#c60000\' class=\'fas\'>&#xf0ac;</i></a>
                    <a><i style=\'font-size:24px; color:#333af2\' class=\'fab\'>&#xf09a;</i></a>
                    <a><i style=\'font-size:24px; color:#1166bc\' class=\'fab\'>&#xf08c;</i></a>
                    <a><i style=\'font-size:24px; color:#333af8\' class=\'fab\'>&#xf081;</i></a>
                </div>
                
            </div>
        </div>
    </body>
</html>';
             
             
             
             
             
             
             
             
             
             
             
             
             if($package->trial == 1){
                 
                 
                 $income = new Income();
            $income->user_id = $user->id;
            $income->shop_id = $shop->id;
            $income->date = date('Y-m-d');
            $income->package_id = $request->package_id;
            $income->method_id = 6;
            $income->status = 1;
            $income->amount = 0;
            $income->duration = $request->duration;
            $income->save();
                 
                 
                  $data = array('name'=>$request->name);
      Mail::send([], [], function($message) use($request, $msg){
         $message->to($request->email, $request->name)->subject
            ('Welcome To Pharmacy Software Solution');
         $message->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'));
         $message->setBody($msg, 'text/html');
      });
                 
             $seller = User::where('id', $user->id)->firstOrFail();
        
                auth()->login($seller, true);
                Toastr::success('Logged In As '.$seller->name.'');
                return redirect()->route('dashboard');
             }
             
            $income = new Income();
            $income->user_id = $user->id;
            $income->shop_id = $shop->id;
            $income->date = date('Y-m-d');
            $income->package_id = $request->package_id;
            $income->inv_id = $oid;
            $income->method_id = 6;
            $income->status = 0;
            $income->amount = $request->price;
            $income->duration = $request->duration;
             if($income->save()){
        
          $data = array('name'=>$request->name);
      Mail::send([], [], function($message) use($request, $msg){
         $message->to($request->email, $request->name)->subject
            ('Welcome To Pharmacy Software Solution');
         $message->from(env('MAIL_FROM_ADDRESS'),env('MAIL_FROM_NAME'));
         $message->setBody($msg, 'text/html');
      });
      $tamt = ($request->price-$request->coupon);
       $info = array( 'prefix' => "AYA",'currency' => "BDT", 'amount' => $request->price, 'order_id' => $oid, 'discsount_amount' => 0, 'disc_percent' => 0, 'client_ip' => $ip, 'customer_name' => $request->name, 'customer_phone' => $request->phone, 'email' => $request->email, 'customer_address' => $request->address, 'customer_city' => $thana->name, 'customer_state' => $district->name, 'customer_country' => "Bangladesh", );
       
        
        $shurjopay_service = new ShurjopayController(); 
        
        return $shurjopay_service->checkout($info);
             }
         }
        }
    }
}
