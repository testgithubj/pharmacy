<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EmergencyStock extends Model
{
    use HasFactory;

    public function batch(){
        return $this->hasMany(Batch::class)->where('shop_id', Auth::user()->shop_id)->orderBy('id', 'desc');
    }

    public function stock(){
        return $this->hasMany(Stock::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }

    public function invoice(){
        return $this->hasMany(Invoice::class);
    }

    public function purchase(){
        return $this->hasMany(Purchase::class);
    }



    public function unit(){
        return $this->belongsTo(Unit::class);
    }

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function leaf(){
        return $this->belongsTo(Leaf::class);
    }

    public function invoice_pay(){
        return $this->hasMany(InvoicePay::class);
    }

    public function supplier(){
        return $this->belongsTo(Supplier::class);
    }
}
