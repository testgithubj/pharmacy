<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
   
   /**
     * The table associated with the model.
     *
     * @var string
     */
    public function batch()
{
    return $this->belongsTo(Batch::class);
}
}
