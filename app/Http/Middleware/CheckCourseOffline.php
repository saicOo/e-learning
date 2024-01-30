<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCourseOffline
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if($request->route('course')->type == "offline"){
            return $next($request);
        }

        return response()->json([
            'status_code' => 403,
            'success' => false,
            'message' => 'This course cannot be accessed. This domain is for offline courses only.'
        ], 200);
    }
}
