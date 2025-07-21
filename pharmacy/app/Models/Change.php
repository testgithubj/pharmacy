<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
   
    
    public function medicine(){
    	return $this->belongsTo(Medicine::class);
    }
    
   public function category(){
    	return $this->belongsTo(Category::class);
    }
    
    public function oldcat(){
    	return $this->belongsTo(Category::class);
    }
    
    
     public function shop(){
    	return $this->belongsTo(Shop::class);
    }
    
}
