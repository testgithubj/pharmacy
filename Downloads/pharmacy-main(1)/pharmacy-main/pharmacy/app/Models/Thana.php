<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thana extends Model
{
   
    
     public function district()
    {
        return $this->belongsTo(District::class);
    }
    
    public function invoice(){
    	return $this->hasMany(Invoice::class);
    }
    
    public function customer()
    {
        return $this->hasMany(Customer::class);
    }
    
}
