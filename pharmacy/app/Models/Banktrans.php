<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banktrans extends Model
{
    use HasFactory;
    // In Banktrans model (App\Models\Banktrans.php)

    // In BankTrans model
public function method()
{
    return $this->belongsTo(Method::class, 'paymentmethord_id');
}

public function bank()
{
    return $this->belongsTo(Banks::class, 'bank_id');
}



}
