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
        $user = $request->user();
        if ($user->roles[0]->name == 'manager') {
            return $next($request);
        }

        if ($user->roles[0]->name == 'teacher') {
            if (
                ($request->route('course') && $request->user()->id == $request->route('course')->user_id) ||
                 ($request->route('quiz') && $request->user()->id == $request->route('quiz')->course->user_id) ||
                 ($request->route('lesson') && $request->user()->id == $request->route('lesson')->course->user_id) ||
                 ($request->route('question') && $request->user()->id == $request->route('question')->course->user_id) ||
                 ($request->route('teacher') && $request->user()->id == $request->route('teacher')) ||
                 ($user->assistants->find($request->route('assistant')))
                 ) {
                return $next($request);
            }
        }

        if ($user->roles[0]->name == 'assistant') {
            if ($request->route('course') && $request->user()->user_id == $request->route('course')->user_id ||
            ($request->route('quiz') && $request->user()->user_id == $request->route('quiz')->course->user_id) ||
                 ($request->route('lesson') && $request->user()->user_id == $request->route('lesson')->course->user_id) ||
                 ($request->route('question') && $request->user()->user_id == $request->route('question')->course->user_id) ||
                 ($request->route('teacher') && $request->user()->user_id == $request->route('teacher')) ||
                 ($request->route('assistant') && $request->user()->id == $request->route('assistant'))
                 ) {
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
