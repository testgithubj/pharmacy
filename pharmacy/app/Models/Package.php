<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
   
    
    
    public function shop()
    {
        return $this->hasMany(Shop::class);
    }
    
}
