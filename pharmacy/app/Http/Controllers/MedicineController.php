<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\Leaf;
use App\Models\Shop;
use App\Models\Type;
use App\Models\Unit;
use App\Models\Batch;
use App\Models\Method;
use App\Models\Category;
use App\Models\Medicine;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\EmergencyStock;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class MedicineController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware( 'auth' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function update_price( Request $request, $id ) {
        $batch = Batch::findOrFail( $id );

        if ( $request->isMethod( 'post' ) ) {
            $batch->price = $request->price;

            if ( $batch->save() ) {
                Toastr::success( 'Medicine successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
                return redirect()->route( 'instock' );
            } else {
                Toastr::error( 'Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
                return redirect()->back();
            }

        }

        return view( 'medicine.update_price', compact( 'batch' ) );
    }

    public function add( Request $request ) {

        if ( $request->isMethod( 'post' ) ) {
            // Check for duplicate data based on unique fields
            $duplicate = Medicine::where( 'name', $request->name )
                ->first();

            if ( $duplicate ) {
                Toastr::error( 'Duplicate medicine entry detected. Please verify the details.', '', [
                    'progressBar'   => true,
                    'closeButton'   => true,
                    'positionClass' => 'toast-top-right',
                ] );
                return redirect()->back()->withInput(); // Redirect back with old input
            }

            $customer               = new Medicine();
            $customer->product_type = $request->product_type;
            $customer->name         = $request->name;
            $customer->qr_code      = $request->qr_code;
            $customer->hns_code     = $request->hns_code;
            $customer->strength     = $request->strength;
            $customer->leaf_id      = $request->leaf_id;
            $customer->shelf        = $request->shelf;

            if ( $request->category_id ) {

                if ( is_numeric( $request->category_id ) ) {
                    $customer->category_id = $request->category_id;
                } else {
                    $category       = new Category();
                    $category->name = $request->category_id;

                    if ( $category->save() ) {
                        $customer->category_id = $category->id;
                    }

                }

            }

            $customer->unit_id      = $request->unit_id;
            $customer->type_id      = $request->type_id;
            $customer->supplier_id  = $request->supplier_id;
            $customer->status       = $request->status;
            $customer->global       = 1;
            $customer->generic_name = $request->generic_name;

            if ( $request->hot ) {
                $customer->hot = $request->hot;
            }

            $customer->des       = $request->des;
            $customer->price     = $request->price;
            $customer->buy_price = $request->buy_price;

            // Assign Manufacturing and Expiry Dates
            $customer->mfg_date = $request->mfg_date;
            $customer->exp_date = $request->exp_date;

            if ( $request->hasFile( 'image' ) ) {
                $image       = $request->file( 'image' );
                $currentDate = Carbon::now()->toDateString();
                $imageName   = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

                if ( !Storage::disk( 'public' )->exists( 'images/medicine' ) ) {
                    Storage::disk( 'public' )->makeDirectory( 'images/medicine' );
                }

                if ( Storage::disk( 'public' )->exists( 'images/medicine/' . $customer->image ) ) {
                    Storage::disk( 'public' )->delete( 'images/medicine/' . $customer->image );
                }

                $bannerImage = Image::make( $image )->resize( 1500, 1000 )->stream();
                Storage::disk( 'public' )->put( 'images/medicine/' . $imageName, $bannerImage );
                $customer->image = $imageName;
            }

            $customer->shop_id = Auth::user()->shop_id;

            if ( $customer->save() ) {
                Toastr::success( 'Medicine successfully created', '', [
                    'progressBar'   => true,
                    'closeButton'   => true,
                    'positionClass' => 'toast-top-right',
                ] );

// Redirect based on product_type
                if ( $customer->product_type === 'FMCG' ) {
                    return redirect()->route( 'medicine.fmcg' );
                } else {
                    return redirect()->route( 'medicine.list' );
                }

            } else {
                Toastr::error( 'Something Went Wrong', '', [
                    'progressBar'   => true,
                    'closeButton'   => true,
                    'positionClass' => 'toast-top-right',
                ] );
                return redirect()->route( 'medicine.list' );
            }

        } else {
            $supplier = Supplier::where( 'shop_id', Auth::user()->shop_id )->latest()->get();
            $leaf     = Leaf::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $category = Category::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $unit     = Unit::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $type     = Type::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();

            return view( 'medicine.add', compact( 'leaf', 'unit', 'category', 'supplier', 'type' ) );
        }

    }

    public function edit( Request $request, $id ) {
        $medicine = Medicine::where( 'id', $id )->firstOrFail();
        if ( $request->isMethod( 'post' ) ) {
            $medicine->name         = $request->name;
            $medicine->product_type = $request->product_type;
            $medicine->qr_code      = $request->qr_code;
            $medicine->strength     = $request->strength;
            $medicine->leaf_id      = $request->leaf_id;
            $medicine->shelf        = $request->shelf;

            if ( $request->category_id ) {
                $medicine->category_id = $request->category_id;
            }

            $medicine->type_id     = $request->type_id;
            $medicine->supplier_id = $request->supplier_id;
            // $medicine->vat = $request->vat;
            $medicine->status       = $request->status;
            $medicine->global       = 1;
            $medicine->generic_name = $request->generic_name;
            $medicine->unit_id      = $request->unit_id;
            $medicine->des          = $request->des;
            $medicine->price        = $request->price;
            $medicine->buy_price    = $request->buy_price;

            if ( $request->hot ) {
                $medicine->hot = $request->hot;
            }

            // Update Manufacturing and Expiry Dates
            $medicine->mfg_date = $request->mfg_date;
            $medicine->exp_date = $request->exp_date;

            if ( $request->hasFile( 'image' ) ) {
                $image       = $request->file( 'image' );
                $currentDate = Carbon::now()->toDateString();
                $imageName   = $currentDate . '-' . uniqid() . '.' . $image->getClientOriginalExtension();

                if ( Storage::disk( 'public' )->exists( 'images/medicine' ) ) {
                    Storage::disk( 'public' )->makeDirectory( 'images/medicine' );
                }

                if ( Storage::disk( 'public' )->exists( 'images/medicine/' . $medicine->image ) ) {
                    Storage::disk( 'public' )->delete( 'images/medicine/' . $medicine->image );
                }

                $bannerImage = Image::make( $image )->resize( 1500, 1000 )->stream();
                Storage::disk( 'public' )->put( 'images/medicine/' . $imageName, $bannerImage );
                $medicine->image = $imageName;
            }

            if ( $medicine->save() ) {
                Toastr::success( 'Medicine successfully updated', '', [
                    'progressBar'   => true,
                    'closeButton'   => true,
                    'positionClass' => 'toast-top-right',
                ] );

                if ( $medicine->product_type === 'FMCG' ) {
                    return redirect()->route( 'medicine.fmcg' );
                } else {
                    return redirect()->route( 'medicine.list' );
                }

            } else {
                Toastr::error( 'Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
                return redirect()->back();
            }

        } else {
            $supplier = Supplier::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $leaf     = Leaf::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $category = Category::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $unit     = Unit::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            $type     = Type::where( 'shop_id', Auth::user()->shop_id )->orWhere( 'global', 1 )->get();
            return view( 'medicine.edit', compact( 'medicine', 'leaf', 'unit', 'category', 'supplier', 'type' ) );
        }

    }

    public function delete( Request $request, $id ) {
        $customer = Medicine::where( 'shop_id', Auth::user()->shop_id )->where( 'id', $id )->firstOrFail();

        if ( $customer->delete() ) {
            Toastr::success( 'Medicine successfully Deleted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );

            if ( $customer->product_type === 'FMCG' ) {
                return redirect()->route( 'medicine.fmcg' );
            } else {
                return redirect()->route( 'medicine.list' );
            }

        } else {
            Toastr::error( 'Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
            return redirect()->route( 'medicine.list' );
        }

    }

    public function expired( Request $request ) {

        if ( $request->ajax() ) {
            $data = Batch::where( 'expire', '<=', date( 'Y-m-d' ) )->latest( 'id' );
            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'name', function ( $row ) {
                    $medicine = Medicine::where( 'id', $row->medicine_id )->first();

                    if ( $medicine != null ) {
                        return '' . $medicine->name . '(' . $medicine->strength . ')';
                    } else {
                        return '';
                    }

                } )
                ->addColumn( 'supplier', function ( $row ) {
                    $supplier = Medicine::where( 'id', $row->medicine_id )->first();
                    return $supplier->supplier->name;
                } )
                ->addColumn( 'action', function ( $row ) {

                    if ( $row->global != 1 ) {
                        return '<a onclick="return confirm(\'Are you sure?\')" href="' . route( 'expired.delete', $row->id ) . '" class="badge bg-danger"><i class="fas fa-trash"></i></a>';
                    }

                } )
                ->rawColumns( ['action'] )
                ->make( true );
        }

        return view( 'medicine.expired' );
    }

    public function expired_delete( $id ) {
        $customer = Batch::where( 'id', $id )->firstOrFail();

        if ( $customer->delete() ) {
            Toastr::success( 'Medicine successfully Deleted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
            return redirect()->route( 'report.already_expire' );
        } else {
            Toastr::error( 'Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
            return redirect()->route( 'report.already_expire' );
        }

    }

    public function upcoming( Request $request ) {
        $day = Shop::shopSetting( 'upcoming_expire_alert' );

        if ( $request->ajax() ) {
            $today        = date( 'Y-m-d', time() );
            $upcomingDate = date( 'Y-m-d', strtotime( "+$day days" ) );
            $data         = $data         = Batch::where( 'expire', '>', $today )
                ->where( 'expire', '<=', $upcomingDate )
                ->latest( 'id' );

            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'name', function ( $row ) {
                    $medicine = Medicine::where( 'id', $row->medicine_id )->first();

                    if ( $medicine != null ) {
                        return '' . $medicine->name . '(' . $medicine->strength . ')';
                    } else {
                        return '';
                    }

                } )
                ->addColumn( 'supplier', function ( $row ) {
                    $supplier = Medicine::where( 'id', $row->medicine_id )->first();
                    return $supplier->supplier->name;
                } )
                ->make( true );
        }

        return view( 'medicine.upcoming' );
    }

    public function index( Request $request ) {

        if ( $request->ajax() ) {
            $query = Medicine::with( ['category', 'supplier', 'type'] ) // Eager load relationships
                ->where( 'product_type', 'medicine' );

// Filter by product_type == 'medicine'

// Apply additional filters if present
            if ( $request->category ) {
                $query->where( 'category_id', $request->category );
            }

            if ( $request->supplier ) {
                $query->where( 'supplier_id', $request->supplier );
            }

            if ( $request->shelf ) {
                $query->where( 'shelf', 'LIKE', "%{$request->shelf}%" );
            }

// Search functionality
            if ( !empty( $request['search']['value'] ) ) {
                $keyword = $request['search']['value'];
                $query->where( function ( $q ) use ( $keyword ) {
                    $q->where( 'name', 'LIKE', "%{$keyword}%" )
                        ->orWhere( 'generic_name', 'LIKE', "%{$keyword}%" )
                        ->orWhere( 'shelf', 'LIKE', "%{$keyword}%" )
                        ->orWhere( 'price', 'LIKE', "%{$keyword}%" )
                        ->orWhere( 'buy_price', 'LIKE', "%{$keyword}%" )
                        ->orWhere( 'strength', 'LIKE', "%{$keyword}%" )
                        ->orWhereHas( 'category', function ( $q ) use ( $keyword ) {
                            $q->where( 'name', 'LIKE', "%{$keyword}%" );
                        } )
                        ->orWhereHas( 'supplier', function ( $q ) use ( $keyword ) {
                            $q->where( 'name', 'LIKE', "%{$keyword}%" );
                        } );
                } );
            }

            $data = $query->latest();

            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'category', function ( $row ) {
                    return $row->category->name ?? 'N/A';
                } )
                ->addColumn( 'supplier', function ( $row ) {
                    return $row->supplier->name ?? 'N/A';
                } )
                ->addColumn( 'type', function ( $row ) {
                    return $row->type->name ?? 'N/A';
                } )
                ->addColumn( 'picture', function ( $row ) {
                    if ( $row->image ) {
                        return '<img src="' . asset( 'storage/images/medicine/' . $row->image ) . '" alt="Medicine Image" height="50" width="50">';
                    }

                    return '<img src="' . asset( 'images/employee/pngtree-medical-drug-cartoon-illustration-png-image_4544247.jpg' ) . '" height="50" width="50" alt="Default Image">';
                } )
                ->addColumn( 'action', function ( $row ) {
                    return '<a href="' . route( 'medicine.edit', $row->id ) . '" class="badge bg-primary"><i class="fas fa-edit"></i></a>
                        <a onclick="return confirm(\'Are you sure you want to delete this medicine?\')" href="' . route( 'medicine.delete', $row->id ) . '" class="badge bg-danger"><i class="fas fa-trash"></i></a>';
                } )
                ->rawColumns( ['action', 'picture'] )
                ->make( true );
        }

        $total_cash_in_hand = Method::sum( 'balance' );
        $categories         = Category::all();
        $suppliers          = Supplier::all();
        $type               = Type::all();

        return view( 'medicine.index', compact( 'categories', 'suppliers', 'total_cash_in_hand', 'type' ) );
    }

    public function instock( Request $request ) {
        $stock = Batch::select( \DB::raw( 'sum(buy_price * qty) as total' ) )->get();
        $sales = Batch::select( \DB::raw( 'sum(price * qty) as total' ) )->get();

        $stockes  = $stock['0']->total;
        $expected = $sales['0']->total;
        $profit   = ( $expected - $stockes );
        if ( $request->ajax() ) {
            $data = Medicine::whereHas( 'batch', function ( $query ) {
                $query->where( 'qty', '>', 0 );
            } )->latest();
            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'supplier', function ( $row ) {
                    return $row->supplier->name;
                } )
                ->addColumn( 'stock', function ( $row ) {
                    $stock = Batch::where( 'medicine_id', $row->id )->sum( 'qty' );
                    return $stock;
                } )

                ->addColumn( 'batch', function ( $row ) {
                    $batch_name = Batch::where( 'medicine_id', $row->id )->first()->name;
                    return $batch_name;
                } )
                ->addColumn( 'cost', function ( $row ) {
                    $batch_price = Batch::where( 'medicine_id', $row->id )->first()->buy_price;
                    return number_format( $batch_price, 2 );
                } )
                ->addColumn( 'total', function ( $row ) {
                    $qty   = Batch::where( 'medicine_id', $row->id )->sum( 'qty' );
                    $stock = Batch::where( 'medicine_id', $row->id )->first()->buy_price;
                    $amt   = ( $stock * $qty );
                    return number_format( $amt, 2, ".", "," );
                } )
                ->addColumn( 'action', function ( $row ) {
                    $stock = Batch::where( 'medicine_id', $row->id )->where( 'qty', '>', 0 )->latest()->first();
                    return '<a href="' . route( 'update.price', $stock->id ) . '" class="badge bg-primary">Update Price</a> ';

                } )
                ->rawColumns( ['action'] )
                ->make( true );
        }

        return view( 'medicine.instock', compact( 'stockes', 'expected', 'profit' ) );
    }

    public function emergencyStock( Request $request ) {
        $stock = Batch::select( \DB::raw( 'sum(buy_price * qty) as total' ) )->where( 'shop_id', Auth::user()->shop_id )->get();
        $sales = Batch::select( \DB::raw( 'sum(price * qty) as total' ) )->where( 'shop_id', Auth::user()->shop_id )->get();

        $stockes  = $stock['0']->total;
        $expected = $sales['0']->total;
        $profit   = ( $expected - $stockes );
        if ( $request->ajax() ) {
            $data = EmergencyStock::where( function ( $q ) {
                $q->where( 'global', 1 );
            } )->whereHas( 'batch', function ( $query ) {
                $query->where( 'qty', '>', 0 );
            } )->orderBy( 'name', 'asc' );
            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'supplier', function ( $row ) {
                    return $row->supplier->name;
                } )
                ->addColumn( 'stock', function ( $row ) {
                    $stock = Batch::where( 'medicine_id', $row->id )->sum( 'qty' );
                    return $stock;
                } )

                ->addColumn( 'batch', function ( $row ) {
                    $batch_name = Batch::where( 'medicine_id', $row->id )->first()->name;
                    return $batch_name;
                } )
                ->addColumn( 'cost', function ( $row ) {
                    $batch_price = Batch::where( 'medicine_id', $row->id )->first()->buy_price;
                    return number_format( $batch_price, 2 );
                } )
                ->addColumn( 'total', function ( $row ) {
                    $qty   = Batch::where( 'medicine_id', $row->id )->sum( 'qty' );
                    $stock = Batch::where( 'medicine_id', $row->id )->first()->buy_price;
                    $amt   = ( $stock * $qty );
                    return number_format( $amt, 2, ".", "," );
                } )
                ->addColumn( 'action', function ( $row ) {
                    $stock = Batch::where( 'medicine_id', $row->id )->where( 'qty', '>', 0 )->latest()->first();
                    return '<a href="' . route( 'update.price', $stock->id ) . '" class="badge bg-primary">Update Price</a> ';

                } )
                ->rawColumns( ['action'] )
                ->make( true );
        }

        return view( 'medicine.emergency_stock', compact( 'stockes', 'expected', 'profit' ) );
    }

    public function lowstock( Request $request ) {
        $low_stock_amount = Shop::shopSetting( 'low_stock_alert' );
        if ( $request->ajax() ) {
            $data = Medicine::select( '*' )->whereHas( 'batch', function ( $query ) {
                $query->where( 'qty', '>', 0 );
            } )->withCount( ['batch as total_quantity' => function ( $query ) {
                $query->select( DB::raw( 'sum(qty)' ) );
            },
            ] )
                ->having( 'total_quantity', '<', $low_stock_amount )
                ->latest();
            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'supplier', function ( $row ) {
                    return $row->supplier->name;
                } )
                ->addColumn( 'quantity', function ( $row ) {
                    return $row->total_quantity;
                } )
                ->make( true );
        }

        return view( 'medicine.lowstock' );
    }

    public function stockout( Request $request ) {
        if ( $request->ajax() ) {
            $data = Medicine::select( '*' )
                ->whereHas( 'batch', function ( $query ) {
                    $query->where( 'qty', '<', 1 );
                } )
                ->latest( 'id' );
            return Datatables::of( $data )
                ->addIndexColumn()
                ->addColumn( 'supplier', function ( $row ) {
                    return $row->supplier->name;
                } )
                ->make( true );
        }

        return view( 'medicine.stockout' );
    }

    public function importFormView() {
        $total_cash_in_hand = Method::sum( 'balance' );
        return view( 'medicine.import_form', compact( 'total_cash_in_hand' ) );
    }

// public function importProcess( Request $request ) {

//     $request->validate( [

//         'medicines' => 'required',

//     ] );

//     try {

//         if ( $request->hasFile( 'medicines' ) ) {

//             $file = $request->file( 'medicines' );

//             Excel::import( new MedicineImport(), $file );

//         }

//         Toastr::success( 'Medicines has been imported successfully!', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );

//         return redirect()->back();

//     } catch ( \Exception $e ) {

//         Toastr::error( $e->getMessage(), '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );

//         return redirect()->back();

//     }

    // }

    public function importProcess( Request $request ) {
        $request->validate( [
            'medicines' => 'required',
        ] );

        try {

            if ( $request->hasFile( 'medicines' ) ) {
                $file = $request->file( 'medicines' );
                Excel::import( new MedicineImport(), $file );
            }

            Toastr::success( 'Medicines have been imported successfully!', '', [
                'progressBar'   => true,
                'closeButton'   => true,
                'positionClass' => 'toast-top-right',
            ] );
            return redirect()->back();
        } catch ( \Exception $e ) {
            Toastr::error( 'Import Failed: ' . $e->getMessage(), '', [
                'progressBar'   => true,
                'closeButton'   => true,
                'positionClass' => 'toast-top-right',
            ] );
            return redirect()->back();
        }

    }

    public function exporter( $slug ) {
        try {
            $csv_name = $slug;
            $table    = $slug;

            if ( $slug == 'leaves' ) {
                $csv_name = 'leaf';
            }

            $filename      = "$csv_name.csv";
            $exported_data = DB::table( $table )->get()->toArray();

            if ( !empty( $exported_data ) ) {
                $headers = $this->makeHeader( $table );
                $handle  = fopen( $filename, 'w+' );
                fputcsv( $handle, $headers );

                foreach ( $exported_data as $key => $item ) {
                    fputcsv( $handle, json_decode( json_encode( $item ), true ) );
                }

                fclose( $handle );
                $headers = [
                    'Content-Type' => 'text/csv',
                ];
                return Response::download( $filename, $filename, $headers );
            }

            Toastr::error( "No record found in your database, please add $slug", '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
            return redirect()->back();
        } catch ( \Exception $e ) {
            Toastr::error( $e->getMessage(), '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right'] );
            return redirect()->back();
        }

    }

    protected function makeHeader( $table ) {
        $fields  = DB::table( $table )->first();
        $columns = [];

        foreach ( $fields as $key => $field ) {
            $columns[] = $key;
        }

        return $columns;
    }

// public function fmcgproducts() {

//     // Get all medicines where product_type is 'fmcg'

//     $fmcgProducts = Medicine::where( 'product_type', 'fmcg' )->paginate( 10 );

//     return view( 'medicine.fmcgproducts', compact( 'fmcgProducts' ) );
    // }

    public function fmcgproducts() {
        $fmcgProducts = Medicine::where( 'product_type', 'fmcg' )
            ->with( ['category', 'supplier'] ) // Eager load relationships
            ->paginate( 10 );

        return view( 'medicine.fmcgproducts', compact( 'fmcgProducts' ) );
    }

}
