<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSubmitQuiz
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
        $user = Auth::user();
        if($request->route('lesson')){
            $lessonId = $request->route('lesson')->id;
           $attempt = $user->hasCurrentLesson($lessonId);
        }
        if($request->route('course')){
            $courseId = $request->route('course')->id;
           $attempt = $user->hasCurrentCourse($courseId);
        }
        if(!$attempt || $attempt->status_passed != "started"){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => 'The quiz cannot be sent before it has been taken'
              ], 200);
        }
        return $next($request);
    }
}
