<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Method extends Model
{
   protected $guarded = [];
    
    public function invoice_pay(){
    	return $this->hasMany(InvoicePay::class);
    }
    
   public function supplier_pay(){
    	return $this->hasMany(SupplierPay::class);
    }
    
}
