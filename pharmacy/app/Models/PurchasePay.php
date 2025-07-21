<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasePay extends Model
{
   
    // public function purchase(){
    // 	return $this->hasMany(Purchase::class);
    // }

    
    public function supplier()
    {
        return $this->hasMany(Supplier::class);
    }
    
    
    public function method()
    {
        return $this->belongsTo(Method::class);
    }

    public function purchase()
{
    return $this->belongsTo(Purchase::class, 'purchase_id');
}
    
}
