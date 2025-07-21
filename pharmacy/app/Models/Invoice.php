<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
   
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }
    
    public function batch(){
    	return $this->hasMany(Batch::class);
    }
    
    public function medicine()
    {
        return $this->hasMany(Medicine::class);
    }
    
    
    // public function method()
    // {
    //     return $this->belongsTo(Method::class);
    // }
    
    
    public function invoice_pay(){
    	return $this->hasMany(InvoicePay::class);
    }

    public function method()
{
    return $this->belongsTo(Method::class, 'method_id');
}
    
}
