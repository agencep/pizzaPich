<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPosOtp
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. If not authenticated at all
        if (!session('pos_authenticated') && !session('kitchen_authenticated')) {
            return redirect()->route('login');
        }

        // 2. If kitchen-only, restrict to kitchen route
        if (session('kitchen_authenticated') && !session('pos_authenticated')) {
            if ($request->routeIs('kitchen')) {
                return $next($request);
            }
            return redirect()->route('kitchen');
        }

        return $next($request);
    }
}
