<?php

namespace App\Http\Controllers;

use App\CPU\BackEndHelper;
use App\CPU\Helpers;
use App\Models\Batch;
use App\Models\Customer;
use App\Models\Leaf;
use App\Models\Medicine;
use App\Models\Method;
use App\Models\Purchase;
use App\Models\PurchasePay;
use App\Models\Supplier;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellController extends Controller
{
    public function order_list(Request $request)
    {
        $query_param = [];
        $search = $request['search'];


        $orders = Order::with(['customer'])->where(['seller_is' => 'admin'])->where('order_status', 'delivered');


        if ($request->has('search')) {
            $key = explode(' ', $request['search']);
            $orders = $orders->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('id', 'like', "%{$value}%")
                        ->orWhere('order_status', 'like', "%{$value}%")
                        ->orWhere('transaction_ref', 'like', "%{$value}%");
                }
            });
            $query_param = ['search' => $request['search']];
        }

        $orders = $orders->where('order_type', 'POS')->orderBy('id', 'desc')->paginate(Helpers::pagination_limit())->appends($query_param);
        return view('sell.order.list', compact('orders', 'search'));
    }

    public function order_details($id)
    {
        $order = Order::with('details', 'shipping', 'seller')->where(['id' => $id])->first();

        return view('sell.order.order-details', compact('order'));
    }

    public function index(Request $request)
    {
        $date = date('Y-m-d', time());
        $category = $request->query('category_id', 0);
        $keyword = $request->query('search', false);
        $categories = Supplier::where(function ($q) {
            $q->where('shop_id', Auth::user()->shop_id)
                ->orWhere('global', 1);
        })->latest()->get();

        $key = explode(' ', $keyword);
        $products = Medicine::select('name', 'strength', 'id', 'image')->where(function ($q) {
            $q->where('shop_id', Auth::user()->shop_id)
                ->orWhere('global', 1);
        })->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
            $query->where('supplier_id', $request['category_id']);
        })
            ->when($keyword, function ($query) use ($key) {
                return $query->where(function ($q) use ($key) {
                    foreach ($key as $value) {
                        $q->orWhere('name', 'like', "{$value}%");
                    }
                });
            })
            ->latest()->paginate(25);

        $cart_id = 'wc-' . rand(10, 1000);

        if (!session()->has('current_user')) {
            session()->put('current_user', $cart_id);
        }

        if (!session()->has('cart_name')) {
            if (!in_array($cart_id, session('cart_name') ?? [])) {
                session()->push('cart_name', $cart_id);
            }
        }

        return view('sell.index', compact('categories', 'cart_id', 'category', 'keyword', 'products'));
    }

    public function search_product(Request $request)
    {

        $request->validate([
            'name' => 'required',
        ], [
            'name.required' => 'Product name is required',
        ]);
        $date = date('Y-m-d', time());
        $key = explode(' ', $request['name']);
        $products = Medicine::where(function ($q) {
            $q->where('shop_id', Auth::user()->shop_id)
                ->orWhere('global', 1);
        })->when($request->has('category_id') && $request['category_id'] != 0, function ($query) use ($request) {
            $query->where('supplier_id', $request['category_id']);
        })->where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('name', 'like', "{$value}%");
            }
        })->paginate(6);

        $count_p = $products->count();
        $count_p = $products->count();

        if ($count_p > 0) {
            return response()->json([
                'count' => $count_p,
                'id' => $products[0]->id,
                'result' => view('sell._search-result', compact('products'))->render(),
            ]);
        } else {
            return response()->json([
                'count' => $count_p,
                'result' => view('sell._search-result', compact('products'))->render(),
            ]);
        }

    }

    public function quick_view(Request $request)
    {
        $product = Medicine::findOrFail($request->product_id);

        return response()->json([
            'success' => 1,
            'view' => view('sell._quick-view-data', compact('product'))->render(),
        ]);
    }

    public function variant_price(Request $request)
    {
        $product = Product::find($request->id);
        $str = '';
        $quantity = 0;
        $price = 0;

        if ($request->has('color')) {
            $str = Color::where('code', $request['color'])->first()->name;
        }

        foreach (json_decode(Product::find($request->id)->choice_options) as $key => $choice) {
            if ($str != null) {
                $str .= '-' . str_replace(' ', '', $request[$choice->name]);
            } else {
                $str .= str_replace(' ', '', $request[$choice->name]);
            }
        }

        if ($str != null) {
            $count = count(json_decode($product->variation));
            for ($i = 0; $i < $count; $i++) {
                if (json_decode($product->variation)[$i]->type == $str) {
                    $tax = Helpers::tax_calculation(json_decode($product->variation)[$i]->price, $product['tax'], $product['tax_type']);
                    $discount = Helpers::get_product_discount($product, json_decode($product->variation)[$i]->price);
                    $price = json_decode($product->variation)[$i]->price - $discount + $tax;
                    $quantity = json_decode($product->variation)[$i]->qty;
                }
            }
        } else {
            $tax = Helpers::tax_calculation($product->unit_price, $product['tax'], $product['tax_type']);
            $discount = Helpers::get_product_discount($product, $product->unit_price);
            $price = $product->unit_price - $discount + $tax;
            $quantity = $product->current_stock;
        }

        return [
            'price' => \currency_converter($price * $request->quantity),
            'discount' => \currency_converter($discount),
            'tax' => \currency_converter($tax),
            'quantity' => $quantity
        ];

    }

    public function addToCart(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }

        $product = Medicine::find($request->id);

        $data = array();
        $data['id'] = $product->id;
        $str = $request->batch_no;
        $variations = [];
        $price = 0;
        $p_qty = 0;
        $current_qty = 0;

        $cart = session($cart_id);
        if (session()->has($cart_id) && count($cart) > 0) {

            foreach ($cart as $key => $cartItem) {
                if (is_array($cartItem) && $cartItem['id'] == $request['id']) {
                    return response()->json([
                        'data' => 1,
                        'view' => view('sell._cart', compact('cart_id'))->render()
                    ]);
                }
            }


        }


        $leaf = Leaf::where('id', $request['leaf_id'])->first()->amount;
        $data['quantity'] = ($request['quantity'] * $leaf);
        $data['price'] = ($request['bprice'] * $data['quantity']);
        $data['mrp'] = $request['mrp'];
        $data['batch_no'] = $str;
        $data['leaf_id'] = $request['leaf_id'];
        $data['name'] = $product->name;
        $data['discount'] = 0;
        $data['image'] = $product->image;
        $data['expiry_date'] = $request['expiry_date'];
        if (session()->has($cart_id)) {
            $keeper = [];
            foreach (session($cart_id) as $item) {
                array_push($keeper, $item);
            }
            array_push($keeper, $data);
            session()->put($cart_id, $keeper);
        } else {
            session()->put($cart_id, [$data]);
        }

        return response()->json([
            'data' => $data,
            'view' => view('sell._cart', compact('cart_id'))->render()
        ]);
    }

    public function cart_items()
    {
        return view('sell._cart');
    }

    public function emptyCart(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }
        session()->forget($cart_id);
        return response()->json([
            'user_type' => $user_type,
            'view' => view('sell._cart', compact('cart_id'))->render()], 200);
    }

    public function removeFromCart(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';

        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }

        $cart = session($cart_id);
        $cart_keeper = [];

        if (session()->has($cart_id) && count($cart) > 0) {
            foreach ($cart as $key => $cartItem) {
                if ($key != $request['key']) {
                    array_push($cart_keeper, $cartItem);
                }
            }
        }
        session()->put($cart_id, $cart_keeper);

        return response()->json(['view' => view('sell._cart', compact('cart_id'))->render()], 200);
    }

    public function updateQuantity(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }

        if ($request->quantity > 0) {

            $product = Medicine::find($request->key);
            $product_qty = 0;
            $cart = session($cart_id);
            $keeper = [];

            foreach ($cart as $item) {

                if (is_array($item)) {

                    if ($item['id'] == $request->key) {
                        $
                        $item['quantity'] = $request->quantity;
                    }
                    array_push($keeper, $item);
                }
            }
            session()->put($cart_id, $keeper);

            return response()->json([
                'qty_update' => 1,
                'view' => view('sell._cart', compact('cart_id'))->render()
            ], 200);
        } else {
            return response()->json([
                'upQty' => 'zeroNegative',
                'view' => view('sell._cart', compact('cart_id'))->render()
            ]);
        }
    }

    public function extra_dis_calculate($cart, $price)
    {

        if ($cart['ext_discount_type'] == 'percent') {
            $price_discount = ($price / 100) * $cart['ext_discount'];
        } else {
            $price_discount = $cart['ext_discount'];
        }

        return $price_discount;
    }

    public function coupon_discount(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }
        if ($user_id != 0) {
            $couponLimit = Order::where('customer_id', $user_id)
                ->where('customer_type', 'customer')
                ->where('coupon_code', $request['coupon_code'])->count();

            $coupon = Coupon::where(['code' => $request['coupon_code']])
                ->where('limit', '>', $couponLimit)
                ->where('status', '=', 1)
                ->whereDate('start_date', '<=', now())
                ->whereDate('expire_date', '>=', now())->first();
        } else {
            $coupon = Coupon::where(['code' => $request['coupon_code']])
                ->where('status', '=', 1)
                ->whereDate('start_date', '<=', now())
                ->whereDate('expire_date', '>=', now())->first();
        }

        $carts = session($cart_id);
        $total_product_price = 0;
        $product_discount = 0;
        $product_tax = 0;
        $ext_discount = 0;

        if ($coupon != null) {
            if ($carts != null) {
                foreach ($carts as $cart) {
                    if (is_array($cart)) {
                        $product = Batch::find($cart['batch']);
                        $total_product_price += $cart['price'] * $cart['quantity'];
                        $product_discount += $cart['discount'] * $cart['quantity'];
                        $product_tax += Helpers::tax_calculation($cart['price'], $product['tax'], $product['tax_type']) * $cart['quantity'];
                    }
                }
                if ($total_product_price >= $coupon['min_purchase']) {
                    if ($coupon['discount_type'] == 'percentage') {

                        $discount = (($total_product_price / 100) * $coupon['discount']) > $coupon['max_discount'] ? $coupon['max_discount'] : (($total_product_price / 100) * $coupon['discount']);
                    } else {
                        $discount = $coupon['discount'];
                    }
                    if (isset($carts['ext_discount_type'])) {
                        $ext_discount = $this->extra_dis_calculate($carts, $total_product_price);

                    }
                    $total = $total_product_price - $product_discount + $product_tax - $discount - $ext_discount;
                    //return $total;
                    if ($total < 0) {
                        return response()->json([
                            'coupon' => "amount_low",
                            'view' => view('sell._cart', compact('cart_id'))->render()
                        ]);
                    }

                    $cart = session($cart_id, collect([]));
                    $cart['coupon_code'] = $request['coupon_code'];
                    $cart['coupon_discount'] = $discount;
                    $cart['coupon_title'] = $coupon->title;
                    $request->session()->put($cart_id, $cart);

                    return response()->json([
                        'coupon' => 'success',
                        'view' => view('sell._cart', compact('cart_id'))->render()
                    ]);
                }
            } else {
                return response()->json([
                    'coupon' => 'cart_empty',
                    'view' => view('sell._cart', compact('cart_id'))->render()
                ]);
            }

            return response()->json([
                'coupon' => 'coupon_invalid',
                'view' => view('sell._cart', compact('cart_id'))->render()
            ]);

        }

        return response()->json([
            'coupon' => 'coupon_invalid',
            'view' => view('admin-views.sell._cart', compact('cart_id'))->render()
        ]);
    }

    public function update_discount(Request $request)
    {
        $cart_id = session('current_user');
        if ($request->type == 'percent' && $request->discount < 0) {
            Toastr::error(\App\CPU\translate('Extra_discount_can_not_be_less_than_0_percent'));
            return response()->json([
                'extra_discount' => "amount_low",
                'view' => view('sell._cart', compact('cart_id'))->render()
            ]);
        } elseif ($request->type == 'percent' && $request->discount > 100) {
            Toastr::error(\App\CPU\translate('Extra_discount_can_not_be_more_than_100_percent'));
            return response()->json([
                'extra_discount' => "amount_low",
                'view' => view('sell._cart', compact('cart_id'))->render()
            ]);
        }


        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }

        $cart = session($cart_id, collect([]));
        if ($cart != null) {
            $total_product_price = 0;
            $product_discount = 0;
            $product_tax = 0;
            $ext_discount = 0;
            $coupon_discount = $cart['coupon_discount'] ?? 0;

            foreach ($cart as $ct) {
                if (is_array($ct)) {
                    $total_product_price += $ct['price'] * $ct['quantity'];
                    $product_discount += $ct['discount'] * $ct['quantity'];
                    $product_tax = 0;
                    //$product_tax += Helpers::tax_calculation($ct['price'], $product['tax'], $product['tax_type'])*$ct['quantity'];
                }
            }

            if ($request->type == 'percent') {
                $ext_discount = ($total_product_price / 100) * $request->discount;
            } else {
                $ext_discount = $request->discount;
            }
            $total = $total_product_price - $product_discount + $product_tax - $coupon_discount - $ext_discount;
            if ($total < 0) {
                return response()->json([
                    'extra_discount' => "amount_low",
                    'view' => view('sell._cart', compact('cart_id'))->render()
                ]);
            } else {
                $cart['ext_discount'] = $request->type == 'percent' ? $request->discount : BackEndHelper::currency_to_usd($request->discount);
                $cart['ext_discount_type'] = $request->type;
                session()->put($cart_id, $cart);

                return response()->json([
                    'extra_discount' => "success",
                    'view' => view('sell._cart', compact('cart_id'))->render()
                ]);
            }
        } else {
            return response()->json([
                'extra_discount' => "empty",
                'view' => view('sell._cart', compact('cart_id'))->render()
            ]);
        }
    }

    public function get_customers(Request $request)
    {
        $key = explode(' ', $request['q']);
        $data = DB::table('customers')
            ->where(function ($q) use ($key) {
                foreach ($key as $value) {
                    $q->orWhere('name', 'like', "{$value}%")
                        ->orWhere('phone', 'like', "{$value}%");
                }
            })->where('shop_id', Auth::user()->shop_id)
            ->whereNotNull(['name'])
            ->limit(8)
            ->get([DB::raw('id,IF(id <> "0", CONCAT(name, " "," (", phone ,")"),CONCAT(name, " ", phone)) as text')]);

        //$data[] = (object)['id' => false, 'text' => 'walk_in_customer'];

        return response()->json($data);
    }

    public function place_order(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }
        if (session()->has($cart_id)) {
            if (count(session()->get($cart_id)) < 1) {
                Toastr::error('cart_empty_warning');
                return back();
            }
        } else {
            Toastr::error(\App\CPU\translate('cart_empty_warning'));
            return back();
        }
        $carts = session($cart_id);
        $item = $carts;

        $product = Medicine::find($carts['0']['id']);

        $total_price = array_sum(array_column($item, 'price'));
        $total_quantity = array_sum(array_column($item, 'quantity'));
        $purchase = new Purchase();
        $purchase->supplier_id = $product->supplier_id;
        $purchase->date = date('Y-m-d');
        $offerNo = Purchase::count();
        $purchase->subtotal = $total_price;
        $purchase->total_price = $request->amount;
        $purchase->qty = $total_quantity;
        $purchase->due_price = $request->due;
        $purchase->discount = $cart['ext_discount'] ?? 0;
        $purchase->inv_id = uniqueOrderId($offerNo, Auth::user()->shop->prefix, 'purchases', 'inv_id');
        $purchase->medicines = json_encode($item);
        if ($request->due > 0) {
            $sup = Supplier::where('id', $user_id)->first();
            if ($sup != null) {
                $sup->due += $request->due;
                $sup->save();
            }
        }
        $purchase->thana_id = Auth::user()->shop->thana_id;
        $purchase->shop_id = Auth::user()->shop_id;
        $purchase->district_id = Auth::user()->shop->district_id;
        $purchase->method_id = $request->type;
        if ($purchase->save()) {
            $invpay = new PurchasePay();
            $invpay->shop_id = Auth::user()->shop_id;
            $invpay->invoice_id = $purchase->id;
            $invpay->date = date('Y-m-d');
            $invpay->amount = $request->paid;
            $invpay->supplier_id = $product->supplier_id;
            $invpay->method_id = $request->type;
            $invpay->save();


            $method = Method::find($request->type);
            $method->balance -= $request->paid;
            $method->save();
            $count = count($carts);
            for ($i = 0; $i < $count; $i++) {

                if (isset($carts[$i]['batch_no'])) {

                    $batch = new Batch();
                    $batch->shop_id = Auth::user()->shop_id;
                    $batch->qty = $carts[$i]['quantity'];
                    $batch->medicine_id = $carts[$i]['id'];
                    $batch->name = $carts[$i]['batch_no'];
                    $batch->price = $carts[$i]['mrp'];
                    $batch->buy_price = ($carts[$i]['price'] / $carts[$i]['quantity']);

                    $batch->expire = $carts[$i]['expiry_date'];
                    $batch->leaf_id = $carts[$i]['leaf_id'];
                    $batch->purchase_id = $purchase->id;

                    $batch->save();
                }

            }

        }

        session()->forget($cart_id);
        session(['last_order' => $purchase->id]);
        Toastr::success(\App\CPU\translate('order_placed_successfully'));
        if (!empty($purchase->id)) {
            return redirect()->route('sell.order.invoice', $purchase->id);
        }
        return redirect()->route('sell.index');
    }

    public function generate_invoice_order($id)
    {
        if (!empty($id)) {
            $data['order_id'] = $id;
            return view('sell.order.invoice')->with($data);
        }
        return redirect()->route('sell.index');
    }


    public function store_keys(Request $request)
    {
        session()->put($request['key'], $request['value']);
        return response()->json('', 200);
    }

    public function get_cart_ids(Request $request)
    {
        $cart_id = session('current_user');
        $user_id = 0;
        $user_type = 'wc';
        if (Str::contains(session('current_user'), 'sc')) {
            $user_id = explode('-', session('current_user'))[1];
            $user_type = 'sc';
        }
        $cart = session($cart_id);
        $cart_keeper = [];
        if (session()->has($cart_id) && count($cart) > 0) {
            foreach ($cart as $cartItem) {
                array_push($cart_keeper, $cartItem);
            }
        }
        session()->put(session('current_user'), $cart_keeper);
        $user_id = explode('-', session('current_user'))[1];
        $current_customer = '';
        if (explode('-', session('current_user'))[0] == 'wc') {
            $current_customer = 'Walking Customer';
        } else {
            $current = Customer::where('id', $user_id)->first();
            $current_customer = $current->name . ' (' . $current->phone . ')';
        }
        return response()->json([
            'current_user' => session('current_user'), 'cart_nam' => session('cart_name') ?? '',
            'current_customer' => $current_customer,
            'view' => view('sell._cart', compact('cart_id'))->render()]);
    }

    public function clear_cart_ids()
    {
        session()->forget('cart_name');
        session()->forget(session('current_user'));
        session()->forget('current_user');

        return redirect()->route('sell.index');
    }

    public function remove_discount(Request $request)
    {
        $cart_id = ($request->user_id != 0 ? 'sc-' . $request->user_id : 'wc-' . rand(10, 1000));
        if (!in_array($cart_id, session('cart_name') ?? [])) {
            session()->push('cart_name', $cart_id);
        }

        $cart = session(session('current_user'));

        $cart_keeper = [];
        if (session()->has(session('current_user')) && count($cart) > 0) {
            foreach ($cart as $cartItem) {

                array_push($cart_keeper, $cartItem);

            }
        }
        if (session('current_user') != $cart_id) {
            $temp_cart_name = [];
            foreach (session('cart_name') as $cart_name) {
                if ($cart_name != session('current_user')) {
                    array_push($temp_cart_name, $cart_name);
                }
            }
            session()->put('cart_name', $temp_cart_name);
        }
        session()->put('cart_name', $temp_cart_name);
        session()->forget(session('current_user'));
        session()->put($cart_id, $cart_keeper);
        session()->put('current_user', $cart_id);
        $user_id = explode('-', session('current_user'))[1];
        $current_customer = '';
        if (explode('-', session('current_user'))[0] == 'wc') {
            $current_customer = 'Walking Customer';
        } else {
            $current = Customer::where('id', $user_id)->first();
            $current_customer = $current->name . ' (' . $current->phone . ')';
        }

        return response()->json([
            'cart_nam' => session('cart_name'),
            'current_user' => session('current_user'),
            'current_customer' => $current_customer,
            'view' => view('sell._cart', compact('cart_id'))->render()]);
    }

    public function new_cart_id(Request $request)
    {
        $cart_id = 'wc-' . rand(10, 1000);
        session()->put('current_user', $cart_id);
        if (!in_array($cart_id, session('cart_name') ?? [])) {
            session()->push('cart_name', $cart_id);
        }

        return redirect()->route('sell.index');

    }

    public function change_cart(Request $request)
    {

        session()->put('current_user', $request->cart_id);

        return redirect()->route('sell.index');
    }

    public function customer_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:customers'
        ]);

        $customer = new Customer();
        $customer->name = $request->name;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        if ($request->filled('due')) {
            $customer->due = $request->due;
        }
        $customer->shop_id = Auth::user()->shop_id;
        $customer->thana_id = Auth::user()->shop->thana_id;
        $customer->district_id = Auth::user()->shop->district_id;
        $customer->save();

        Toastr::success(\App\CPU\translate('customer added successfully'));
        return back();
    }

}
