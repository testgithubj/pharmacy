<?php

namespace App\Models\Account;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function setDateAttribute($value)
    {
        $this->attributes['date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function debitAccount()
    {
        return $this->belongsTo(Account::class,'debit_account_id','id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class,'credit_account_id','id');
    }

    


}
