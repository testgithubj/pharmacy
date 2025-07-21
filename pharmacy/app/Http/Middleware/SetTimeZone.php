<?php

namespace App\Http\Middleware;

use App\Models\Shop;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SetTimeZone
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            if (!empty(setting('time_zone'))) {
                date_default_timezone_set(setting('time_zone'));
            }else{
                date_default_timezone_set('Asia/Dhaka');
            }
        }
        return $next($request);
    }
}
