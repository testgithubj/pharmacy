<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lol extends Model
{
   
    
    public function medicine(){
    	return $this->belongsTo(Medicine::class);
    }
    
   
    
}
