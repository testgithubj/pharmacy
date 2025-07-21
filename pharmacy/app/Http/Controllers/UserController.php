<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $collection = User::latest()->get();
        return view('user.index', compact('collection'));
    }


    public function create()
    {
        $roles = Role::select('id', 'name','display_name')->get()->toArray();
        return view('user.create',compact('roles'));
    }


    public function store(Request $request)
    {
        $this->validation($request);
        try {
            $data = $request->only('name', 'email', 'password');
            $role = $request->input('role');
            $user = User::create($data);
            $user->assignRole($role);
            return redirect()->route('user.index')->with('success','Created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function edit(User $user)
    {
        $roles = Role::select('id', 'name', 'display_name')->get()
            ->toArray();
        return view('user.edit', compact('user', 'roles'));
    }



    public function update(Request $request, User $user)
    {
        $this->validation($request, $user->id);
        try {
            DB::beginTransaction();
            $data = $request->only('name', 'email', 'password');
            if (!empty($request->input('password'))) {
                $data['password'] = Hash::make($request->input('password'));
            } else {
                $data['password'] = $user->password;
            }
            $role = $request->input('role');
            $user->update($data);
            $user->syncRoles($role);
            DB::commit();
            return redirect()->route('user.index')->with('success','Updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function delete(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('user.index')->with('success', 'Deleted Successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    private function validation($request, $id = null)
    {
        $arr = [
            'name' => 'required',
            'role' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
        ];
        if (empty($id)) {
            array_push($arr, ['password' => 'required|min:6']);
        }

        return $request->validate($arr);
    }
}
