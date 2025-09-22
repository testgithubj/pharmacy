<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseMedcine extends Model
{
   
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
     public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
    
    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }
    
    public function purchase(){
    	return $this->belongsTo(Purchase::class);
    }
    
}
