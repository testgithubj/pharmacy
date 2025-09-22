<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Hash;
use Toastr;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
class ProfileController extends Controller
{
    public function index(){
        $id= Auth::user()->id;
        $adminInfo =User::find($id)->toArray();
        // dd($adminInfo);
        return view("asset.profile")->with(compact('adminInfo'));
    }
    public function cpass(){
        $id= Auth::user()->id;
        $adminInfo =User::find($id)->toArray();
        return view("asset.password")->with(compact('adminInfo'));
    }
    public function edit(Request $request){
        // dd($request->all());
        $id= Auth::user()->id;
        User::where('id',$id)->update(['name'=>$request['name']]);
        return redirect()->back();
    }
    public function Upass(Request $request){
        Toastr::error('You are in demo mode!','Error!');
         return redirect()->back();die;
        $id= Auth::user()->id;
        $data=$request->all();
        $user=User::find($id);
          if(!Hash::check($data['old'],$user['password'])){
            Toastr::error('Current password is incorrect!','Sorry!');
            return redirect()->back();die;
          }else{
            if(Auth::user()->shop_id == 34 || Auth::user()->shop_id == 47){
        Toastr::error('You are in demo mode!','Error!');
        return redirect()->back();die;
        }
            $user->update(['password'=>Hash::make($data['confirm'])]);
            Toastr::success('Password updated!','Success!');
            return redirect()->back();die;
          }
        
    }
    public function setting( Request $request){
        if($request->isMethod("post")){
        if(Auth::user()->shop_id == 34 || Auth::user()->shop_id == 47){
        Toastr::error('You are in demo mode!','Error!');
        return redirect()->back();die;
        }
         $id= Auth::user()->id;
         $user=User::find($id);
         $data=$request->all();
         if($request->new !== $request->confirm){
             Toastr::error('Insert carefully!','Sorry!');
             return redirect()->back();die;
         }
          if(!Hash::check($data['old'],$user['password'])){
            Toastr::error('Current password is incorrect!','Sorry!');
            return redirect()->back();die;
          }else{
            $user->update(['password'=>Hash::make($data['confirm'])]);
            Toastr::success('Password updated!','Success!');
            return redirect()->back();die;
          }
        }
       return view("reset");
    }
    public function info_setting( Request $request){
        if($request->isMethod("post")){
        if(Auth::user()->shop_id == 34 || Auth::user()->shop_id == 47){
        Toastr::error('You are in demo mode!','Error!');
        return redirect()->back();die;
        }
         $id= Auth::user()->id;
         $user=User::find($id);
         $user->email = $request->email;
         $user->name = $request->name;
      
      if($request->hasFile('image'))
                {
                $image=$request->file('image');
                $currentDate=Carbon::now()->toDateString();
                $imageName=$currentDate.'-'.uniqid().'.'.$image->getClientOriginalExtension();
                if(Storage::disk('public')->exists('images/profile'))
                {
                   Storage::disk('public')->makeDirectory('images/profile');
                }
                if(Storage::disk('public')->exists('images/profile/'.$user->image))
                {
                   Storage::disk('public')->delete('images/profile/'.$user->image);
                }
                $bannerImage = Image::make($image)->resize(1500,1000)->stream();
                Storage::disk('public')->put('images/profile/'.$imageName,$bannerImage);
                $user->image=$imageName;
                }
      $user->save();
        Toastr::success('profile  updated!','Success!');
        return redirect()->back();die;
        }
       return view("info_reset");
    }
    
   
}
