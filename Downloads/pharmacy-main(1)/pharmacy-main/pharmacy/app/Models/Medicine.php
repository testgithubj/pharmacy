<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Medicine extends Model {

    protected $guarded = [];

    public function batch() {
        return $this->hasMany( Batch::class );
    }

    public function stock() {
        return $this->hasMany( Stock::class );
    }

    public function district() {
        return $this->belongsTo( District::class );
    }

    public function thana() {
        return $this->belongsTo( Thana::class );
    }

    public function invoice() {
        return $this->hasMany( Invoice::class );
    }

    public function purchase() {
        return $this->hasMany( Purchase::class );
    }

    public function unit() {
        return $this->belongsTo( Unit::class );
    }

    // public function category() {
    //     return $this->belongsTo( Category::class );
    // }

    // public function supplier() {
    //     return $this->belongsTo( Supplier::class );
    // }

    public function leaf() {
        return $this->belongsTo( Leaf::class );
    }

    public function invoice_pay() {
        return $this->hasMany( InvoicePay::class );
    }

    public function type() {
        return $this->belongsTo( Type::class, 'type_id' );
    }

    public function category() {
        return $this->belongsTo( Category::class, 'category_id' ); // Explicitly define the foreign key
    }

    public function supplier() {
        return $this->belongsTo( Supplier::class, 'supplier_id' ); // Explicitly define the foreign key
    }

}
