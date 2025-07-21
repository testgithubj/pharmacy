<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{

    protected $guarded = [];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
    public function leaf()
{
    return $this->belongsTo(Leaf::class);  // Assuming a 'leaf' relationship
}

 


}
