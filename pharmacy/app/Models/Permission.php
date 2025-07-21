<?php

namespace App\Models;

use App\Models;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    protected static $logName = 'Permission';

    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }
    //EACH CATEGORY MIGHT HAVE MULTIPLE CHILDREN
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }
    
    public function getRouteAttribute($value)
    {
        return !empty($value) ? json_decode($value,true):[];
    }
}
