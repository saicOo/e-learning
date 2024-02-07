<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class QuizCompleted
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
        if($attempt && $attempt->status_passed == "compleated"){
            // اذا تم انهاء الاختبار
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => __('auth.quiz_completed')
              ], 200);
        }

        return $next($request);
    }
}
