<?php

namespace App\Http\Controllers;

use App\CPU\Helpers;
use App\Models\Leaf;
use App\Models\Batch;
use App\Models\Method;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Medicine;
use App\Models\Supplier;
use App\CPU\BackEndHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\EmergencyStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;

class POSController extends Controller {
    public function order_list( Request $request ) {
        $query_param = [];
        $search      = $request['search'];
        $orders      = Order::with( ['customer'] )->where( ['seller_is' => 'admin'] )->where( 'order_status', 'delivered' );

        if ( $request->has( 'search' ) ) {
            $key    = explode( ' ', $request['search'] );
            $orders = $orders->where( function ( $q ) use ( $key ) {

                foreach ( $key as $value ) {
                    $q->orWhere( 'id', 'like', "%{$value}%" )
                        ->orWhere( 'order_status', 'like', "%{$value}%" )
                        ->orWhere( 'transaction_ref', 'like', "%{$value}%" );
                }

            } );
            $query_param = ['search' => $request['search']];
        }

        $orders = $orders->where( 'order_type', 'POS' )->orderBy( 'id', 'desc' )->paginate( Helpers::pagination_limit() )->appends( $query_param );
        return view( 'pos.order.list', compact( 'orders', 'search' ) );
    }

    public function order_details( $id ) {
        $order = Order::with( 'details', 'shipping', 'seller' )->where( ['id' => $id] )->first();

        return view( 'pos.order.order-details', compact( 'order' ) );
    }

    public function index( Request $request ) {
        $today      = date( 'Y-m-d', time() );
        $category   = $request->query( 'category_id', 0 );
        $customers  = Customer::select( 'id', 'name', 'shop_id', 'phone' )->latest()->get();
        $supplier   = $request->query( 'supplier_id', 0 ); // Supplier filter
        $suppliers  = Supplier::select( 'id', 'shop_id', 'name' )->orderBy( 'name' )->get(); // Fetch suppliers
        $keyword    = $request->query( 'search', false );
        $categories = Category::latest()->get();
        $key        = explode( ' ', $keyword );

        $products = Medicine::select( 'name', 'strength', 'id', 'image', 'supplier_id', 'product_type', 'qr_code' )
            ->whereHas( 'batch', function ( $query ) use ( $today ) {
                $query->whereDate( 'expire', '>', $today );
            } )
            ->withCount( ['batch as total_quantity' => function ( $query ) use ( $today ) {
                $query->whereDate( 'expire', '>', $today )
                    ->select( DB::raw( 'sum(qty)' ) );
            },
            ] )
            ->when( $request->has( 'category_id' ) && $request['category_id'] != 0, function ( $query ) use ( $request ) {
                $query->where( 'category_id', $request['category_id'] );
            } )
            ->when( $request->has( 'supplier_id' ) && $request['supplier_id'] != 0, function ( $query ) use ( $request ) {
                $query->where( 'supplier_id', $request['supplier_id'] ); // Filter by supplier_id
            } )
            ->when( $keyword, function ( $query ) use ( $key ) {
                return $query->where( function ( $q ) use ( $key ) {

                    foreach ( $key as $value ) {
                        $q->orWhere( 'name', 'like', "%{$value}%" )
                            ->orWhere( 'qr_code', 'like', "%{$value}%" )
                            ->orWhere( 'product_type', 'like', "%{$value}%" );
                    }

                } );
            } )
            ->latest()
            ->paginate( 16 );
        $total_cash_in_hand = Method::sum( 'balance' );

        return view( 'pos.index', compact( 'categories', 'customers', 'category', 'suppliers', 'supplier', 'keyword', 'products', 'total_cash_in_hand' ) );
    }

    public function search_product( Request $request ) {
        // Change search query
        $keyword = $request->query( 'keyword', false );
        $key     = explode( ' ', $keyword );
        $today   = date( 'Y-m-d', time() );

        $products = Medicine::select( 'name', 'strength', 'id', 'image', 'vendor_id', 'qr_code' )
            ->where( function ( $q ) {
            } )->with( 'batch' )
            ->withCount( ['batch as total_quantity' => function ( $query ) use ( $today ) {
                $query->whereDate( 'expire', '>', $today )
                    ->where( 'qty', '>', 0 );
                $query->select( DB::raw( 'sum(qty)' ) );
            },
            ] )
            ->when( $request->has( 'vendor_id' ) && $request['vendor_id'] != 0, function ( $query ) use ( $request ) {
                $query->where( 'vendor_id', $request['vendor_id'] );
            } )->when( $keyword, function ( $query ) use ( $key ) {
            return $query->where( function ( $q ) use ( $key ) {

                foreach ( $key as $value ) {
                    $q->orWhere( 'name', 'like', "{$value}%" )
                        ->orWhere( 'qr_code', 'like', "%{$value}%" )
                        ->orWhere( 'product_type', 'like', "%{$value}%" );
                }

            } );
        } )->latest()->paginate( 16 );
        return response()->json( [
            'result' => view( 'pos.products', compact( 'products' ) )->render(),
        ] );
    }

    public function quick_view( Request $request ) {

        if ( session( 'stock' ) == 'emergency-stock' ) {
            $product = EmergencyStock::with( 'batch' )->findOrFail( $request->product_id );
        } else {
            $product = Medicine::with( 'batch' )->findOrFail( $request->product_id );
        }

        return response()->json( ['success' => 1, 'product' => $product] );
    }

    public function emrg_quick_view( Request $request ) {
        $product = EmergencyStock::findOrFail( $request->product_id );
        return response()->json( [
            'success' => 1,
            'view'    => view( 'pos._emrg-quick-view-data', compact( 'product' ) )->render(),
        ] );
    }

    public function variant_price( Request $request ) {
        $product  = Product::find( $request->id );
        $str      = '';
        $quantity = 0;
        $price    = 0;

        if ( $request->has( 'color' ) ) {
            $str = Color::where( 'code', $request['color'] )->first()->name;
        }

        foreach ( json_decode( Product::find( $request->id )->choice_options ) as $key => $choice ) {

            if ( $str != null ) {
                $str .= '-' . str_replace( ' ', '', $request[$choice->name] );
            } else {
                $str .= str_replace( ' ', '', $request[$choice->name] );
            }

        }

        if ( $str != null ) {
            $count = count( json_decode( $product->variation ) );

            for ( $i = 0; $i < $count; $i++ ) {

                if ( json_decode( $product->variation )[$i]->type == $str ) {
                    $tax      = Helpers::tax_calculation( json_decode( $product->variation )[$i]->price, $product['tax'], $product['tax_type'] );
                    $discount = Helpers::get_product_discount( $product, json_decode( $product->variation )[$i]->price );
                    $price    = json_decode( $product->variation )[$i]->price - $discount + $tax;
                    $quantity = json_decode( $product->variation )[$i]->qty;
                }

            }

        } else {
            $tax      = Helpers::tax_calculation( $product->unit_price, $product['tax'], $product['tax_type'] );
            $discount = Helpers::get_product_discount( $product, $product->unit_price );
            $price    = $product->unit_price - $discount + $tax;
            $quantity = $product->current_stock;
        }

        return [
            'price'    => \currency_converter( $price * $request->quantity ),
            'discount' => \currency_converter( $discount ),
            'tax'      => \currency_converter( $tax ),
            'quantity' => $quantity,
        ];
    }

    public function addToCart( Request $request ) {
        try {
            $today     = date( 'Y-m-d', time() );
            $productId = $request->product_id;
            $product   = Medicine::with( ['batch' => function ( $query ) use ( $today ) {
                $query->where( 'qty', '>', 0 )->where( 'expire', '>', $today );
            },
            ] )->find( $productId );

            if ( empty( $product ) ) {
                return response()->json( [
                    'no_batch'    => 1,
                    'already_has' => 0,
                    'added'       => 0,
                    'view'        => view( 'pos._cart', compact( 'product' ) )->render(),
                ] );
            }

            $defaultBatch = $product->batch->first();

            if ( !$defaultBatch ) {
                return response()->json( [
                    'no_batch'    => 1,
                    'already_has' => 0,
                    'added'       => 0,
                    'view'        => view( 'pos._cart', compact( 'product' ) )->render(),
                ] );
            }

            $cardData = [
                'id'           => $product->id,
                'name'         => $product->name,
                'strength'     => $product->strength,
                'generic_name' => $product->generic_name,
                'price'        => $product->price,
                'igta'         => $product->igta,
                'vat'          => $product->vat,
                'buy_price'    => $product->buy_price,
                'quantity'     => 1,
                'batch'        => $product->batch,
                'batch_id'     => $defaultBatch->id,
                'expire'       => $defaultBatch->expire,
                'discount'     => 0,
            ];

            $cart = session( 'cart_store', [] );

            if ( array_key_exists( $productId, $cart ) ) {
                $cart[$productId]['quantity'] += 1;
            } else {
                $cart[$productId] = $cardData;
            }

            session( ['cart_store' => $cart] );

            return response()->json( [
                'already_has' => 0,
                'added'       => 1,
                'view'        => view( 'pos._cart', compact( 'cart' ) )->render(),
            ] );
        } catch ( \Exception $e ) {
            return response()->json( $e->getMessage() );
        }

    }

    public function removeFromCart( Request $request ) {
        $productId = $request->product_id;
        $cart      = session( 'cart_store' );

        if ( array_key_exists( $productId, $cart ) ) {
            $productId = $request->product_id;
            $cart      = collect( session( 'cart_store' ) ); // convert array to collection
            $cart->forget( $productId ); // use forget() method on the collection
            session( ['cart_store' => $cart->toArray()] );
            return response()->json( [
                'not_exsits' => 0,
                'removed'    => 1,
                'view'       => view( 'pos._cart' )->render(),
            ] );
        } else {
            return response()->json( [
                'not_exsits' => 1,
                'removed'    => 0,
                'view'       => view( 'pos._cart' )->render(),
            ] );
        }

    }

    public function __addToCart( Request $request ) {
        $cart_id   = session( 'current_user' );
        $user_id   = 0;
        $user_type = 'wc';

        if ( Str::contains( session( 'current_user' ), 'sc' ) ) {
            $user_id   = explode( '-', session( 'current_user' ) )[1];
            $user_type = 'sc';
        }

        $product = Medicine::find( $request->id );

        $data        = [];
        $data['id']  = $product->id;
        $str         = $request->batch;
        $variations  = [];
        $price       = 0;
        $p_qty       = 0;
        $current_qty = 0;

        $batch = Batch::find( $request->batch );

        $cart = session( $cart_id );

        if ( session()->has( $cart_id ) && count( $cart ) > 0 ) {

            foreach ( $cart as $key => $cartItem ) {

                if ( is_array( $cartItem ) && $cartItem['id'] == $request['id'] ) {
                    return response()->json( [
                        'data' => 1,
                        'view' => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                    ] );
                }

            }

        }

//Check the string and decreases quantity for the stock
        if ( $str != null ) {
            $p_qty       = $batch->qty;
            $current_qty = $p_qty - $request['quantity'];
            if ( $current_qty < 0 ) {
                return response()->json( [
                    'data' => 0,
                    'view' => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                ] );
            }

            $price = $batch->price;
        } else {
            $p_qty       = $batch->qty;
            $current_qty = $p_qty - $request['quantity'];
            if ( $current_qty < 0 ) {
                return response()->json( [
                    'data' => 0,
                    'view' => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                ] );
            }

            $price = $batch->price;
        }

        $data['quantity']  = $request['quantity'];
        $data['price']     = $price;
        $data['name']      = $product->name;
        $data['discount']  = 0;
        $data['image']     = $product->image;
        $data['batch']     = $str;
        $data['buy_price'] = $product->buy_price;
        if ( session()->has( $cart_id ) ) {
            $keeper = [];
            foreach ( session( $cart_id ) as $item ) {
                array_push( $keeper, $item );
            }

            array_push( $keeper, $data );
            session()->put( $cart_id, $keeper );
        } else {
            session()->put( $cart_id, [$data] );
        }

        return response()->json( [
            'data' => $data,
            'view' => view( 'pos._cart', compact( 'cart_id' ) )->render(),
        ] );
    }

    public function addToBatch( Request $request ) {
        $batch    = new Batch();
        $leaf     = Leaf::where( 'id', $request->leaf_id )->first()->amount;
        $quantity = ( $request->quantity * $leaf );
        $price    = ( $request->bprice * $request->quantity );

        $batch->shop_id            = Auth::user()->shop_id;
        $batch->emergency_stock_id = $request->emergency_stock_id;
        $batch->name               = $request->batch_no;
        $batch->price              = $request->mrp;
        $batch->buy_price          = ( $price / $quantity );
        $batch->qty                = $quantity;
        $batch->expire             = $request->expiry_date;
        $batch->leaf_id            = $request->leaf_id;
        $batch->purchase_id        = $request->emergency_stock_id;
        $batch->save();
        return response()->json( ['success' => 1, 'product_id' => $request->emergency_stock_id] );
    }

    public function cart_items() {
        return view( 'pos._cart' );
    }

    public function emptyCart( Request $request ) {
        $cart_id   = session( 'current_user' );
        $user_id   = 0;
        $user_type = 'wc';
        if ( Str::contains( session( 'current_user' ), 'sc' ) ) {
            $user_id   = explode( '-', session( 'current_user' ) )[1];
            $user_type = 'sc';
        }

        session()->forget( $cart_id );
        return response()->json( [
            'user_type' => $user_type,
            'view'      => view( 'pos._cart', compact( 'cart_id' ) )->render(),
        ], 200 );
    }

    public function __removeFromCart( Request $request ) {
        $cart_id   = session( 'current_user' );
        $user_id   = 0;
        $user_type = 'wc';

        if ( Str::contains( session( 'current_user' ), 'sc' ) ) {
            $user_id   = explode( '-', session( 'current_user' ) )[1];
            $user_type = 'sc';
        }

        $cart        = session( $cart_id );
        $cart_keeper = [];

        if ( session()->has( $cart_id ) && count( $cart ) > 0 ) {
            foreach ( $cart as $key => $cartItem ) {
                if ( $key != $request['key'] ) {
                    array_push( $cart_keeper, $cartItem );
                }

            }

        }

        session()->put( $cart_id, $cart_keeper );

        return response()->json( ['view' => view( 'pos._cart', compact( 'cart_id' ) )->render()], 200 );
    }

    public function quantityIncrement( Request $request ) {
        $response = [
            'success'         => 0,
            'quantity_over'   => 0,
            'batch_not_found' => 0,
        ];

        if ( !empty( $request->product_id ) ) {
            $productId = $request->product_id;
            $cart      = session( 'cart_store', [] );

            if ( array_key_exists( $productId, $cart ) ) {
                $batch    = $cart[$productId]['batch_id'];
                $quantity = $cart[$productId]['quantity'];

                if ( !empty( $batch ) ) {
                    $stockQuantity = Batch::find( $batch )->qty;

                    if ( $stockQuantity > $quantity ) {
                        $cart[$productId]['quantity'] += 1;
                        $response['success'] = 1;
                    } else {
                        $response['quantity_over'] = 1;
                    }

                } else {
                    $response['batch_not_found'] = 1;
                }

            }

            session( ['cart_store' => $cart] );

            return response()->json( [
                'res'  => $response,
                'view' => view( 'pos._cart', compact( 'cart' ) )->render(),
            ] );
        }

        return response()->json( ['res' => $response] );
    }

    public function quantityDecrement( Request $request ) {
        if ( !empty( $request->product_id ) ) {
            $productId = $request->product_id;
            $cart      = session( 'cart_store', [] );
            if ( array_key_exists( $productId, $cart ) ) {
                if ( $cart[$productId]['quantity'] > 1 ) {
                    $cart[$productId]['quantity'] -= 1;
                }

            }

            $response['success'] = 1;
            session( ['cart_store' => $cart] );
            return response()->json( [
                'res'  => $response,
                'view' => view( 'pos._cart' )->render(),
            ] );
        }

    }

    public function quantityInputed( Request $request ) {
        $response = [
            'success' => 0,
        ];
        $quantity = $request->quantity;
        if ( !empty( $request->product_id ) ) {
            $productId = $request->product_id;
            $cart      = session( 'cart_store', [] );
            if ( array_key_exists( $productId, $cart ) ) {
                $batch = $cart[$productId]['batch_id'];
                if ( !empty( $batch ) ) {
                    $stockQuantity = Batch::find( $batch )->qty;
                    if ( $stockQuantity > $quantity ) {
                        $cart[$productId]['quantity'] = $quantity;
                        $response['success']          = 1;
                    } else {
                        $cart[$productId]['quantity'] = $stockQuantity;
                        $response['quantity_over']    = 1;
                        $response['success']          = 0;
                    }

                } else {
                    $response['batch_not_found'] = 1;
                }

            }

            session( ['cart_store' => $cart] );
            return response()->json( [
                'res'  => $response,
                'view' => view( 'pos._cart' )->render(),
            ] );
        }

    }

    // Set batch and update price and expire date of the cart product
    public function setBatch( Request $request ) {
        $cartId      = $request->cart;
        $batchId     = $request->batch_id;
        $response    = [];
        $batch       = Batch::find( $batchId );
        $currentDate = Carbon::now()->toDateString();
        $expireDate  = $batch->expire;
        $cart        = session( 'cart_store', [] );

        if ( !empty( $batch ) ) {

            if ( $expireDate <= $currentDate ) {
                $response = [
                    'success' => 0,
                    'error'   => 1,
                    'message' => 'Medicine expired!',
                    'view'    => view( 'pos._cart' )->render(),
                ];
                return $response;
            }

            if ( $batch->qty < 1 ) {
                $response = [
                    'success' => 0,
                    'error'   => 1,
                    'message' => 'Medicine stockout!',
                    'view'    => view( 'pos._cart' )->render(),
                ];
                return $response;
            }

            if ( array_key_exists( $cartId, $cart ) ) {
                $cart[$cartId]['expire']   = $batch->expire;
                $cart[$cartId]['price']    = $batch->price;
                $cart[$cartId]['batch_id'] = $batch->id;
            }

            session( ['cart_store' => $cart] );
        }

        $response = [
            'success' => 1,
            'error'   => 0,
            'message' => 'Batch has been updated!',
            'view'    => view( 'pos._cart' )->render(),
        ];
        return response()->json( $response );
    }

    public function setProductDiscount( Request $request ) {

        if ( !empty( $request->product_id ) ) {
            $productId       = $request->product_id;
            $discount_amount = $request->discount_amount;
            $cart            = session( 'cart_store', [] );

            if ( array_key_exists( $productId, $cart ) ) {
                $cart[$productId]['discount'] = $discount_amount;
            }

            session( ['cart_store' => $cart] );
            return response()->json( [
                'success' => 1,
                'view'    => view( 'pos._cart' )->render(),
            ] );
        }

    }

    public function updateQuantity( Request $request ) {
        $cart_id   = session( 'current_user' );
        $user_id   = 0;
        $user_type = 'wc';

        if ( Str::contains( session( 'current_user' ), 'sc' ) ) {
            $user_id   = explode( '-', session( 'current_user' ) )[1];
            $user_type = 'sc';
        }

        if ( $request->quantity > 0 ) {

            $product     = Medicine::find( $request->key );
            $product_qty = 0;
            $cart        = session( $cart_id );
            $keeper      = [];

            foreach ( $cart as $item ) {

                if ( is_array( $item ) ) {

                    if ( $item['id'] == $request->key ) {
                        $str = $request->batch;

                        $batch = Batch::find( $request->batch );

                        $qty = $batch->qty - $request->quantity;

                        if ( $qty < 0 ) {
                            return response()->json( [
                                'qty'  => $qty,
                                'view' => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                            ] );
                        }

                        $item['quantity'] = $request->quantity;
                    }

                    array_push( $keeper, $item );
                }

            }

            session()->put( $cart_id, $keeper );

            return response()->json( [
                'qty_update' => 1,
                'view'       => view( 'pos._cart', compact( 'cart_id' ) )->render(),
            ], 200 );
        } else {
            return response()->json( [
                'upQty' => 'zeroNegative',
                'view'  => view( 'pos._cart', compact( 'cart_id' ) )->render(),
            ] );
        }

    }

    public function extra_dis_calculate( $cart, $price ) {

        if ( $cart['ext_discount_type'] == 'percent' ) {
            $price_discount = ( $price / 100 ) * $cart['ext_discount'];
        } else {
            $price_discount = $cart['ext_discount'];
        }

        return $price_discount;
    }

    public function coupon_discount( Request $request ) {
        $cart_id   = session( 'current_user' );
        $user_id   = 0;
        $user_type = 'wc';

        if ( Str::contains( session( 'current_user' ), 'sc' ) ) {
            $user_id   = explode( '-', session( 'current_user' ) )[1];
            $user_type = 'sc';
        }

        if ( $user_id != 0 ) {
            $couponLimit = Order::where( 'customer_id', $user_id )
                ->where( 'customer_type', 'customer' )
                ->where( 'coupon_code', $request['coupon_code'] )->count();

            $coupon = Coupon::where( ['code' => $request['coupon_code']] )
                ->where( 'limit', '>', $couponLimit )
                ->where( 'status', '=', 1 )
                ->whereDate( 'start_date', '<=', now() )
                ->whereDate( 'expire_date', '>=', now() )->first();
        } else {
            $coupon = Coupon::where( ['code' => $request['coupon_code']] )
                ->where( 'status', '=', 1 )
                ->whereDate( 'start_date', '<=', now() )
                ->whereDate( 'expire_date', '>=', now() )->first();
        }

        $carts               = session( $cart_id );
        $total_product_price = 0;
        $product_discount    = 0;
        $product_tax         = 0;
        $ext_discount        = 0;

        if ( $coupon != null ) {

            if ( $carts != null ) {

                foreach ( $carts as $cart ) {

                    if ( is_array( $cart ) ) {
                        $product = Batch::find( $cart['batch'] );
                        $total_product_price += $cart['price'] * $cart['quantity'];
                        $product_discount += $cart['discount'] * $cart['quantity'];
                        $product_tax += Helpers::tax_calculation( $cart['price'], $product['tax'], $product['tax_type'] ) * $cart['quantity'];
                    }

                }

                if ( $total_product_price >= $coupon['min_purchase'] ) {

                    if ( $coupon['discount_type'] == 'percentage' ) {

                        $discount = ( ( $total_product_price / 100 ) * $coupon['discount'] ) > $coupon['max_discount'] ? $coupon['max_discount'] : ( ( $total_product_price / 100 ) * $coupon['discount'] );
                    } else {
                        $discount = $coupon['discount'];
                    }

                    if ( isset( $carts['ext_discount_type'] ) ) {
                        $ext_discount = $this->extra_dis_calculate( $carts, $total_product_price );
                    }

                    $total = $total_product_price - $product_discount + $product_tax - $discount - $ext_discount;

//return $total;
                    if ( $total < 0 ) {
                        return response()->json( [
                            'coupon' => "amount_low",
                            'view'   => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                        ] );
                    }

                    $cart                    = session( $cart_id, collect( [] ) );
                    $cart['coupon_code']     = $request['coupon_code'];
                    $cart['coupon_discount'] = $discount;
                    $cart['coupon_title']    = $coupon->title;
                    $request->session()->put( $cart_id, $cart );

                    return response()->json( [
                        'coupon' => 'success',
                        'view'   => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                    ] );
                }

            } else {
                return response()->json( [
                    'coupon' => 'cart_empty',
                    'view'   => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                ] );
            }

            return response()->json( [
                'coupon' => 'coupon_invalid',
                'view'   => view( 'pos._cart', compact( 'cart_id' ) )->render(),
            ] );
        }

        return response()->json( [
            'coupon' => 'coupon_invalid',
            'view'   => view( 'admin-views.pos._cart', compact( 'cart_id' ) )->render(),
        ] );
    }

    public function update_discount( Request $request ) {
        $cart_id = session( 'current_user' );
        if ( $request->type == 'percent' && $request->discount < 0 ) {
            Toastr::error( \App\CPU\translate( 'Extra_discount_can_not_be_less_than_0_percent' ) );
            return response()->json( [
                'extra_discount' => "amount_low",
                'view'           => view( 'pos._cart', compact( 'cart_id' ) )->render(),
            ] );
        } elseif ( $request->type == 'percent' && $request->discount > 100 ) {
            Toastr::error( \App\CPU\translate( 'Extra_discount_can_not_be_more_than_100_percent' ) );
            return response()->json( [
                'extra_discount' => "amount_low",
                'view'           => view( 'pos._cart', compact( 'cart_id' ) )->render(),
            ] );
        }

        $user_id   = 0;
        $user_type = 'wc';
        if ( Str::contains( session( 'current_user' ), 'sc' ) ) {
            $user_id   = explode( '-', session( 'current_user' ) )[1];
            $user_type = 'sc';
        }

        $cart = session( $cart_id, collect( [] ) );
        if ( $cart != null ) {
            $total_product_price = 0;
            $product_discount    = 0;
            $product_tax         = 0;
            $ext_discount        = 0;
            $coupon_discount     = $cart['coupon_discount'] ?? 0;

            foreach ( $cart as $ct ) {
                if ( is_array( $ct ) ) {
                    $product = Batch::find( $ct['batch'] );
                    $total_product_price += $ct['price'] * $ct['quantity'];
                    $product_discount += $ct['discount'] * $ct['quantity'];
                    $product_tax = 0;
                    //$product_tax += Helpers::tax_calculation($ct['price'], $product['tax'], $product['tax_type'])*$ct['quantity'];
                }

            }

            if ( $request->type == 'percent' ) {
                $ext_discount = ( $total_product_price / 100 ) * $request->discount;
            } else {
                $ext_discount = $request->discount;
            }

            $total = $total_product_price - $product_discount + $product_tax - $coupon_discount - $ext_discount;

            if ( $total < 0 ) {
                return response()->json( [
                    'extra_discount' => "amount_low",
                    'view'           => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                ] );
            } else {
                $cart['ext_discount']      = $request->type == 'percent' ? $request->discount : BackEndHelper::currency_to_usd( $request->discount );
                $cart['ext_discount_type'] = $request->type;
                session()->put( $cart_id, $cart );

                return response()->json( [
                    'extra_discount' => "success",
                    'view'           => view( 'pos._cart', compact( 'cart_id' ) )->render(),
                ] );
            }

        } else {
            return response()->json( [
                'extra_discount' => "empty",
                'view'           => view( 'pos._cart', compact( 'cart_id' ) )->render(),
            ] );
        }

    }

    public function get_customers( Request $request ) {
        $key  = explode( ' ', $request['q'] );
        $data = DB::table( 'customers' )
            ->where( function ( $q ) use ( $key ) {

                foreach ( $key as $value ) {
                    $q->orWhere( 'name', 'like', "{$value}%" )
                        ->orWhere( 'phone', 'like', "{$value}%" );
                }

            } )->where( 'shop_id', Auth::user()->shop_id )
            ->whereNotNull( ['name'] )
            ->limit( 8 )
            ->get( [DB::raw( 'id,IF(id <> "0", CONCAT(name, " "," (", phone ,")"),CONCAT(name, " ", phone)) as text' )] );

        //$data[] = (object)['id' => false, 'text' => 'walk_in_customer'];

        return response()->json( $data );
    }

}
