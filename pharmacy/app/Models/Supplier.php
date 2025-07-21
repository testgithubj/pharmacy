<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
   
    
    public function purchase(){
    	return $this->hasMany(Purchase::class);
    }
    
    public function medicine(){
    	return $this->hasMany(Medicine::class);
    }
    
    public function purchase_pay(){
    	return $this->hasMany(PurchasePay::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'supplier_id', 'id');
    }

    
    
}
