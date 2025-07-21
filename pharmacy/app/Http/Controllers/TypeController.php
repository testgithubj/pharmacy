<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Type;
use App\Models\Method;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\DataTables;
class TypeController extends Controller
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
    

  

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            // Check for duplicate entry manually
            $custom = Type::where('name', $request->name)->where('shop_id', Auth::user()->shop_id)->first();
            if ($custom != null) {
                Toastr::error('Type Exists Already', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->back()->withInput(); // Redirect back with input
            }
    
            // Validate the input
            $request->validate([
                'name' => [
                    'required',
                    Rule::unique('types', 'name')->where(function ($query) {
                        return $query->where('shop_id', Auth::user()->shop_id);
                    }),
                ],
            ], [
                'name.unique' => 'Type already exists for this shop.',
            ]);
    
            // Create a new Type record
            $type = new Type();
            $type->name = $request->name;
            $type->shop_id = Auth::user()->shop_id;
    
            if ($type->save()) {
                Toastr::success('Type successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('types');
            } else {
                Toastr::error('Something went wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('types');
            }
        } else {
            return view('type.add');
        }
    }
    

    
    
    
    
    public function edit(Request $request, $id)
    {
        $customer = Type::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();
        if ($request->isMethod('post')) {
            
            $customer->name = $request->name;
           
            if($customer->save()){
                   Toastr::success('Types successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('types');
            } else {
                Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('types');
            }
        } else {
            
            return view('type.edit', compact('customer'));
        }
    }
    
    
     public function delete(Request $request, $id)
    {
        $customer = Type::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();

        if($customer->delete()){
               Toastr::success('Type successfully Deleted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('types');
        } else {
            Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('types');
        }

    }
    
    
    
    
    public function type(Request $request)
    {
        
         if ($request->ajax()) {
            $data = Type::select('*')->where('shop_id', Auth::user()->shop_id)->latest();
            return Datatables::of($data)
                    ->addIndexColumn()
                    
                    ->addColumn('action', function($row){
     
                           return '<a href="'.route('type.edit', $row->id).'" class="badge bg-primary"><i class="fas fa-edit"></i></a><a onclick="return confirm(\'Are you sure?\')" href="'.route('type.delete', $row->id).'" class="badge bg-danger"><i class="fas fa-trash"></i></a>';
      
                    })
                    ->rawColumns(['action'])
                    ->make(true);
        }
        $category = Type::where('shop_id', Auth::user()->shop_id)->get();
        $total_cash_in_hand = Method::sum('balance');
        
        return view('type.list', compact('category','total_cash_in_hand'));
    }
}
