<?php

namespace App\Models;

use Auth;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard  = 'admin';

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'shop_id',
        'password',
        'role_id',
        'profile',
        'mobile',
        'status'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'id', 'role_id');
    }

    public function getProfileAttribute($value)
    {
        if (!empty($value)) {
            return url('/') . "/public/storage/" . $value;
        }
        return null;
    }
    public function setPasswordAttribute($val)
    {
        $this->attributes['password'] = Hash::make($val);
    }

    
    public function modules()
    {
        return  [
            'customers_add' => 0,
            'customers_list' => 0,
            'in_stock' => 0,
            'emergency_stock' => 0,
            'stockout' => 0,
            'lowstock' => 0,
            'upcoming_expired' => 0,
            'already_expired' => 0,
            'admin_user' => 0,
            'roles' => 0,
            'manufacturers_add' => 0,
            'manufacturers_list' => 0,

            'medicine_add' => 0,
            'medicine_list' => 0,
            'categories' => 0,
            'units' => 0,
            'leaf' => 0,
            'types' => 0,
            'new_purchase' => 0,
            'purchase_history' => 0,
            'create_invoice' => 0,
            'invoice_history' => 0,
            'return_history' => 0,
            'due_customer' => 0,
            'payable_manufacturer' => 0,
            'sells_&_purchase_report' => 0,
            'top_sell_medicine' => 0,
            'profit_&_loss' => 0,
            'doctor_add' => 0,
            'doctor_list' => 0,
            'prescription' => 0,
            'diagnosis_&_tests' => 0,
            'payment_method' => 0,
            'site_setting' => 0,
        ];
    }
}
