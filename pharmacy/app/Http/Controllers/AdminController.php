<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Carbon;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $admins = User::with('role')->latest()->get();
        return view('systems.admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles  = Role::latest()->get();
        return view('systems.admin.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validation($request);
        try {
            $storeId = Auth::user()->store_id;
            $data = $request->only('name', 'email', 'password');
            $data['store_id'] = $storeId;
            $role = $request->input('role');
            $user = User::create($data);
            $user->assignRole($role);
            return redirect()->route('user.index')->with('success','Created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Admin $admin
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Admin $admin)
    {

        if (Auth::guard('admin')->user()->role_id == 1) {
            return User::with('role')->find($admin->id);
        }
        return User::with('role')->find(Auth::guard('admin')->user()->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Admin $admin
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = User::findOrFail($id);
        $roles  = Role::latest()->get();
        return view('systems.admin.edit', compact('admin', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Admin $admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $admin = User::findOrFail($id);
        $arr = [
            'name'    => $request->name,
            'role_id' => $request->role_id ?? $admin->role_id,
            'email'  => $request->email,
            'password'  => $request->password ? \Hash::make($request->password) : $admin->password,
        ];
        $admin->update($arr);

        Toastr::success('User updated successfully!', '', ['toast-top-right']);
        return redirect()->route('admin.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Admin  $admin
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $res = $admin->delete();
        Toastr::success('User deleted successfully!', '', ['toast-top-right']);
        return redirect()->route('admin.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Admin $admin
     * @return \Illuminate\Http\Response
     */
    public function checkOldPassword(Request $request)
    {
        if (empty($request->for_delete)) {
            if (Auth::guard('admin')->user()->role_id == 1 && Auth::guard('admin')->user()->id != $request->id) {
                return response()->json(true);
            }
        }
        if (Auth::guard('admin')->validate(['password' => $request->old_password, 'id' => $request->id])) {
            return response()->json(true);
        } else {
            return response()->json(false);
        }
    }
    //password change==============
    public function passwordChange(Request $request)
    {
        $request->validate([
            'new_password'     => 'required|min:6|required_with:confirm_password|same:confirm_password',
            'confirm_password' => 'required|min:6',
        ]);
        $password = Hash::make($request->new_password);
        Admin::where('id', $request->id)->update(['password' => $password]);
        return response()->json(['message' => 'Password change successfully!!'], 200);
    }

    /**
     * Validate form field.
     *
     * @return \Illuminate\Http\Response
     */
    public function validateCheck($request)
    {
        return $request->validate(
            [
                'name'     => 'required',
                'email'    => 'required|unique:users',
                'password' => 'required|min:8',
                'role_id'  => 'required',
            ],
            [
                'required' => 'The :attribute field is required.',
                'unique' => 'Email has already been taken,',
                'min' => 'To sort, at least :min characters',
            ]
        );
    }


    public function mailSmsConfig(Request $request)
    {
        if ($request->isMethod('post')) {
            if (isset($request->MAIL_DRIVER)) {
                Controller::setEnv('MAIL_DRIVER', $request->MAIL_DRIVER);
            }
            if (isset($request->MAIL_HOST)) {
                Controller::setEnv('MAIL_HOST', $request->MAIL_HOST);
            }
            if (isset($request->MAIL_PORT)) {
                Controller::setEnv('MAIL_PORT', $request->MAIL_PORT);
            }
            if (isset($request->MAIL_USERNAME)) {
                Controller::setEnv('MAIL_USERNAME', $request->MAIL_USERNAME);
            }
            if (isset($request->MAIL_PASSWORD)) {
                Controller::setEnv('MAIL_PASSWORD', $request->MAIL_PASSWORD);
            }
            if (isset($request->MAIL_ENCRYPTION)) {
                Controller::setEnv('MAIL_ENCRYPTION', $request->MAIL_ENCRYPTION);
            }
            if (isset($request->MAIL_FROM_ADDRESS)) {
                Controller::setEnv('MAIL_FROM_ADDRESS', $request->MAIL_FROM_ADDRESS);
            }
            if (isset($request->MAIL_FROM_NAME)) {
                Controller::setEnv('MAIL_FROM_NAME', "'$request->MAIL_FROM_NAME'");
            }
            if (isset($request->TWILIO_SID)) {
                Controller::setEnv('TWILIO_SID', $request->TWILIO_SID);
            }
            if (isset($request->TWILIO_AUTH_TOKEN)) {
                Controller::setEnv('TWILIO_AUTH_TOKEN', $request->TWILIO_AUTH_TOKEN);
            }
            if (isset($request->TWILIO_NUMBER)) {
                Controller::setEnv('TWILIO_NUMBER', $request->TWILIO_NUMBER);
            }
            Toastr::success('Your Configuration save successfully!', '', ['toast-top-right']);
            return back();
        }
        $config = [
            'MAIL_DRIVER' => env('MAIL_DRIVER'),
            'MAIL_HOST' => env('MAIL_HOST'),
            'MAIL_PORT' => env('MAIL_PORT'),
            'MAIL_USERNAME' => env('MAIL_USERNAME'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
            'TWILIO_SID' => env('TWILIO_SID'),
            'TWILIO_AUTH_TOKEN' => env('TWILIO_AUTH_TOKEN'),
            'TWILIO_NUMBER' => env('TWILIO_NUMBER'),
        ];
        return view('systems.mail_sms_config', compact('config'));
    }
}