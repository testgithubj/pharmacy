<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Method;
use App\Models\Medicine;
use Brian2694\Toastr\Facades\Toastr;
use Yajra\DataTables\DataTables;
class CategoryController extends Controller
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
            // Validate input to ensure name is unique for the given shop_id
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,NULL,id,shop_id,' . Auth::user()->shop_id,
            ]);
    
            // Proceed to create a new category
            $category = new Category();
            $category->name = $request->name;
            $category->shop_id = Auth::user()->shop_id;
            
            if ($category->save()) {
                Toastr::success('Category successfully created', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('category');
            } else {
                Toastr::error('Something went wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('category');
            }
        } else {
            return view('category.add');
        }
    }
    
    

    
    
    
    
    public function edit(Request $request, $id)
    {
        $customer = Category::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();
        if ($request->isMethod('post')) {
            $customer->name = $request->name;
           
            if($customer->save()){
                   Toastr::success('Category successfully updated', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('category');
            } else {
                Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
                return redirect()->route('category');
            }
        } else {
            
            return view('category.edit', compact('customer'));
        }
    }
    
    
     public function delete(Request $request, $id)
    {
        $customer = Category::where('shop_id', Auth::user()->shop_id)->where('id', $id)->firstOrFail();

        if($customer->delete()){
               Toastr::success('catogory successfully Deleted', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('category');
        } else {
            Toastr::error('Something Went Wrong', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->route('category');
        }

    }
    
    
    
    
    public function categories(Request $request)
{
    if ($request->ajax()) {
        // Get the search term from the request
        $search = $request->get('search')['value'];

        // Modify the query to include the search term in the 'name' column
        $data = Category::select('*')
            ->where(function($query) use ($search) {
                $query->where('shop_id', Auth::user()->shop_id)
                      ->orWhere('global', 1);
                
                // If there is a search term, filter by the 'name' column
                if ($search) {
                    $query->where('name', 'like', '%' . $search . '%');
                }
            })
            ->latest(); // Sorting by the latest categories

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('medicine', function($row) {
                return Medicine::where('category_id', $row->id)->count(); // Count medicines for this category
            })
            ->addColumn('action', function($row) {
                if ($row->global != 1) {
                    return '<a href="'.route('category.edit', $row->id).'" class="badge bg-primary"><i class="fas fa-edit"></i></a> 
                            <a onclick="return confirm(\'Are you sure?\')" href="'.route('category.delete', $row->id).'" class="badge bg-danger"><i class="fas fa-trash"></i></a>';
                }
            })
            ->rawColumns(['action']) // Make the action column clickable with HTML
            ->make(true);
    }

    // If not an AJAX request, show the category listing view
    $category = Category::where('shop_id', Auth::user()->shop_id)->get();
    $total_cash_in_hand = Method::sum('balance');
    
    return view('category.list', compact('category','total_cash_in_hand'));
}

}
