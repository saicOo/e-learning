<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiAffiliationMiddleware
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
        $user = $request->user(); // Assuming you are using Laravel's built-in authentication
        if ($user->roles[0]->name == 'manager') {
            return $next($request); // Allow administrators to access
        }

        if ($user->roles[0]->name == 'teacher') {
            if ($request->user()->id == $request->route('course')->user_id) {
                return $next($request);
            }
        }

        if ($user->roles[0]->name == 'assistant') {
            if ($request->user()->user_id == $request->route('course')->user_id) {
                return $next($request);
            }
        }

        return response()->json([
            'status_code' => 403,
            'success' => false,
            'message' => 'Not authorized.'
          ], 200);
    }
}
