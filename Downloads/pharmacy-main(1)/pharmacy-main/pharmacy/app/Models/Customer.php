<?php

namespace App\Models;

use App\Models\Ecommerce\DeliveryAddress;
use App\Models\Ecommerce\Eprescription;
use App\Models\Ecommerce\Order;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use Notifiable;

    protected  $guard = 'customer';
    protected $fillable = ['name','email','phone','type'];
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function invoice(){
    	return $this->hasMany(Invoice::class);
    }
    
    public function invoice_pay(){
    	return $this->hasMany(InvoicePay::class);
    }
    
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    public function address()
    {
        return $this->hasMany(DeliveryAddress::class,'customer_id','id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class,'customer_id','id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Eprescription::class,'customer_id','id');
    }
}
