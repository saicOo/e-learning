<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserAccess
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
        $authUser = $request->user();
        $user = $request->route('user');
        if ($authUser->hasRole('manager') || ($authUser->hasRole('teacher') && $authUser->id == $user->id) ||
        ($authUser->hasRole('assistant') && $authUser->id == $user->id)) {
            return $next($request);
        }

        return response()->json([
            'status_code' => 403,
            'success' => false,
            'message' => __('auth.not_authorized')
          ], 200);
    }
}
