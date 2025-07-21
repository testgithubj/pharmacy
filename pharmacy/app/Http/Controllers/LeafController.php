<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Leaf;
use App\Models\Method;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\DataTables;
class LeafController extends Controller
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
             // Validate input to ensure qty is unique for the given shop_id
             $request->validate([
                 'name' => 'required|string|max:255',
                 'qty' => 'required|numeric|min:1' . Auth::user()->shop_id,
             ]);
     
             // Create a new leaf entry
             $leaf = new Leaf();
             $leaf->name = $request->name;
             $leaf->amount = $request->qty;
             $leaf->shop_id = Auth::user()->shop_id;
     
             if ($leaf->save()) {
                 Toastr::success('Leaf successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                 return redirect()->route('leaf');
             } else {
                 Toastr::error('Something went wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                 return redirect()->route('leaf');
             }
         } else {
             return view('leaf.add');
         }
     }
     

     
    
    
    
    
    
    public function edit(Request $request, $id)
    {
        $customer = Leaf::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();
        if ($request->isMethod('post')) {          
            $customer->name = $request->name;
            $customer->amount = $request->qty;
            if($customer->save()){
                Toastr::success('Leaf successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('leaf');
            } else {
                Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('leaf');
            }
        } else {
            
            return view('leaf.edit', compact('customer'));
        }
    }
    
    
     public function delete(Request $request, $id)
    {
        $customer = Leaf::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();

        if($customer->delete()){
               Toastr::success('Leaf successfully Deleted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('leaf');
        } else {
            Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('leaf');
        }

    }
    
    
    
    
    public function leaf(Request $request)
{
    if ($request->ajax()) {
        $data = Leaf::select('*')
            ->where(function ($query) use ($request) {
                if ($search = $request->get('search')['value']) {
                    $query->where('name', 'like', "%$search%")
                          ->orWhere('amount', 'like', "%$search%");
                }
            })
            ->where(function ($query) {
                $query->where('shop_id', Auth::user()->shop_id)
                      ->orWhere('global', 1);
            })
            ->latest();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                if ($row->global != 1) {
                    return '<a href="'.route('leaf.edit', $row->id).'" class="badge bg-primary"><i class="fas fa-edit"></i></a>
                            <a onclick="return confirm(\'Are you sure?\')" href="'.route('leaf.delete', $row->id).'" class="badge bg-danger"><i class="fas fa-trash"></i></a>';
                }
                return '';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    $category = Leaf::where('shop_id', Auth::user()->shop_id)->get();
    $total_cash_in_hand = Method::sum('balance');
    return view('leaf.list', compact('category','total_cash_in_hand'));
}

}
