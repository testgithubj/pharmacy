<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceMedcine extends Model
{
   
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    
     public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
    
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    
    public function invoice(){
    	return $this->belongsTo(Invoice::class);
    }
    
}
