<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckDurationQuiz
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

        $currentTime = now();
        $user = Auth::user();

        if($request->route('lesson')){
            $lesson = $request->route('lesson');
           $attempt = $user->hasCurrentLesson($lesson->id);
           if(!$attempt){
            return $next($request);
           }
           $quizDuration = $lesson->quizzes()->find($attempt->quiz_id)->pivot->duration;
        }
        if($request->route('course')){
            $course = $request->route('course');
           $attempt = $user->hasCurrentCourse($course->id);
           if(!$attempt){
            return $next($request);
           }
           $quizDuration = $course->quizzes()->find($attempt->quiz_id)->pivot->duration;
        }
        $endTime = $attempt->created_at->addMinutes($quizDuration);
        if ($currentTime > $endTime) {
            $attempt->update([
                'is_visited'=>true,
                'status'=>'failed'
            ]);
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => __('auth.quiz_time_over')
              ], 200);
        } else {
            return $next($request);
        }
    }
}
