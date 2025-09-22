<?php

namespace App\Http\Middleware;
use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;

class NotAllowed
{

    public function handle(Request $request, Closure $next)
    {
        if(env('APP_DEMO') && ($request->isMethod('POST') || $request->isMethod('DELETE') || $request->isMethod('PUT'))) {
            Toastr::error('Demo mode does not permit processing this action.', '', ['progressBar' => true, 'closeButton' => true, 'positionClass' => 'toast-top-right']);
            return redirect()->back();
        }

        return $next($request);

    }
}
