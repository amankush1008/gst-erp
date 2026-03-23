<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BusinessSelected
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Session::has('current_business_id')) {
            $business = Auth::user()->businesses()->first();
            if (!$business) {
                return redirect()->route('settings.business')
                    ->with('warning', 'Please set up your business profile first.');
            }
            Session::put('current_business_id', $business->id);
        }

        return $next($request);
    }
}
