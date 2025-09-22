<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Permission;
use Auth;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $appends = ['role_name'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function getRoleNameAttribute()
    {
        $roles = $this->roles()->get()->toArray();
        $roleName = [];
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $roleName = $role['display_name'];
            }
            return $roleName;
        } else if (!empty($this->getRoleNames())) {
            return $this->getRoleNames();
        } else {
            return '-';
        }
    }
    
    // public function shop()
    // {
    //     return $this->belongsTo(Shop::class);
    // }

    public function shop()
{
    return $this->belongsTo(Shop::class, 'shop_id'); // Adjust 'shop_id' if the column name is different
}

    
    public static function get_permissions()
    {
       $role_permissions = Permission::where('role_id',Auth::user()->role_id)->first();
       if(!empty($role_permissions)){
           return json_decode($role_permissions->permissions,true);
       }
       return [];
        
    }
    


    
}
