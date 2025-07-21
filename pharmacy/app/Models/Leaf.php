<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaf extends Model
{
   
    
    public function medicine(){
    	return $this->hasMany(Medicine::class);
    }
    
   
    
}
