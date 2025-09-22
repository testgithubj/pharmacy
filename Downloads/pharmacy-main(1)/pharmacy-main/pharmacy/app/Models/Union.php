<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Union extends Model
{
   
   
    
     public function upazilla()
    {
        return $this->belongsTo(Upazilla::class);
    }
    
    public function invoice(){
    	return $this->hasMany(Invoice::class);
    }
    
    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
    
}
