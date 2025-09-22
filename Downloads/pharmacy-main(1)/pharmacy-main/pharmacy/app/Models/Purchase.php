<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected  $guarded = [];
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
   
    
    // public function medicine()
    // {
    //     return $this->hasMany(Medicine::class);
    // }
    
    public function district()
    {
        return $this->belongsTo(District::class);
    }
    
  
    
    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }
    
    public function purchase_pay(){
    	return $this->hasMany(PurchasePay::class);
    }

    public function method()
{
    return $this->belongsTo(Method::class, 'method_id');
}

// public function batch()
// {
//     return $this->belongsTo(Batch::class, 'batch_id');
// }

public function medicine()
{
    return $this->belongsTo(Medicine::class, 'medicine_id');
}

public function batch()
{
    return $this->hasMany(Batch::class);
}

// public function supplier()
// {
//     return $this->belongsTo(Supplier::class);
// }
// public function batch()
// {
//     return $this->hasMany(Batch::class);
// }

public function purchasePay()
{
    return $this->hasOne(PurchasePay::class, 'purchase_id');
}

    
}
