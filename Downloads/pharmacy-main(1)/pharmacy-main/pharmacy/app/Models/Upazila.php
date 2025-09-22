<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
   
    public function unions()
    {
        return $this->hasMany(Union::class);
    }
    
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
