<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {

    public function medicine() {
        return $this->hasMany( Medicine::class );
    }

}
