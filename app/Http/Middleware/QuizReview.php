<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QuizReview
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
        $attempt = $request->attributes->get('attempt');
        if($attempt && $attempt->status_passed == "review"){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => __('auth.quiz_review')
              ], 200);
        }
        return $next($request);
    }
}
