<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
   protected $guarded = ['id'];
    
    public function medicine(){
    	return $this->hasMany(Medicine::class);
    }

    public function debitAccount()
    {
        return $this->belongsTo(Account::class, 'debit_account_id', 'id');
    }

    public function creditAccount()
    {
        return $this->belongsTo(Account::class, 'credit_account_id', 'id');
    }


    
   
    
}
