<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
   
    
    public function supplier(){
    	return $this->belongsTo(Suppler::class);
    }
    
   public function shop(){
    	return $this->belongsTo(Shop::class);
    }
    
}
