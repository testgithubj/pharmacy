<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Shop extends Model
{

    public static function setting($key)
    {
        $shop = Shop::find(Auth::user()->shop_id)->toArray();
        if (!empty($key)) {
            return $shop[$key];
        } else {
            return $shop;
        }
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function thana()
    {
        return $this->belongsTo(Thana::class);
    }


    public static function shopSetting($field, $default = null)
    {
        $setting = $shop = Shop::find(Auth::user()->shop_id);
        if ($setting) {
            return $setting[$field];
        }
        return $default;
    }


}
