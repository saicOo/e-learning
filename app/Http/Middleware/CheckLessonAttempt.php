<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckLessonAttempt
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
         $lessonId = $request->route('lesson')->id;
         // Check if the user has an existing quiz_id for this lesson
         $attempt = $user->attempts()
             ->where('lesson_id', $lessonId)
             ->whereNotNull('quiz_id')
             ->first();
        if($attempt){
            $request->attributes->add(['attempt' => $attemp]);
        }
        return $next($request);
    }
}
