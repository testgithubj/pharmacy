<?php namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class SAAS
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && env('SUPERUSER') == Auth::user()->email) {
            return $next($request);
        } else {
            return redirect()->route('dashboard');
        }
    }
}