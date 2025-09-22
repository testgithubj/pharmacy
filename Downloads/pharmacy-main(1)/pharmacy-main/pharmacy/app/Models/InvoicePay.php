<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePay extends Model
{
   
    public function invoice(){
    	return $this->hasMany(Invoice::class);
    }
    
   
    
    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
    
    
    public function method()
    {
        return $this->belongsTo(Method::class);
    }
    
}
