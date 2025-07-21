<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Cart
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (session('cartid')) {
            App::setLocale(session()->get('cartid'));
            return $next($request);
        } else {
            session(['cartid' => generateRandomString()]);
            App::setLocale(session()->get('cartid'));
            return $next($request);
        }
    }
}
