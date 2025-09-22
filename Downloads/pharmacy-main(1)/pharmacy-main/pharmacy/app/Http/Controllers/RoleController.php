<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use function Ramsey\Collection\Map\replace;

class RoleController extends Controller {

    public function index(Request $request )
    {
        $collection = Role::latest()->get();
        return view('role.index',compact('collection'));
    }


    public function create() {
        if (Cache::has('permissions')) {
            $permissions = Cache::get('permissions');
        } else {
            $permissions = Permission::select('id', 'name', 'label', 'module')
                ->get()
                ->groupBy('module')
                ->toArray();
            Cache::put('permissions', $permissions, now()->addHour());
        }
        return view('role.create', compact('permissions'));
    }


    public function store( Request $request )
    {
        $this->validation($request);
        try {
            DB::beginTransaction();
            $roleName = $request->input('name');
            $data['display_name'] = $roleName;
            $data['name'] = strtolower(str_replace(' ', '_', $roleName));
            $permissions = $request->input('permissions');
            $role = Role::create($data);
            $role->syncPermissions($permissions);
            DB::commit();
            return redirect()->route('role.index')->with('success','Created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permitteds = $role->permissions->groupBy('module')->toArray();
        $permissions = Permission::select('id', 'name', 'label', 'module')
            ->get()
            ->groupBy('module')
            ->toArray();

        return view('role.edit', compact('role','permissions','permitteds'));
    }


    public function update( Request $request, Role $role )
    {
        $this->validation($request, $role->id);
        try {
            DB::beginTransaction();
            $roleName = $request->input('name');
            $data['display_name'] = $roleName;
            $data['name'] = strtolower(str_replace(' ', '_', $roleName));
            $permissions = $request->input('permissions');
            $role->update($data);
            $role->syncPermissions($permissions);
            DB::commit();
            return redirect()->route('role.index')->with('success','Updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error',$e->getMessage());
        }
    }


    public function delete(Role $role)
    {
        try {
            $role->delete();
            successAlert('Deleted Successfully');
            return redirect()->route('role.index');
        } catch (\Exception $e) {
            errorAlert($e->getMessage());
            return redirect()->back();
        }
    }

    private function validation($request, $id = null)
    {
        return $request->validate([
            'name' => 'required|unique:roles,name,'.$id,
        ]);
    }

}
