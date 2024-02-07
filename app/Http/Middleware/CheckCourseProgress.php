<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCourseProgress
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
        $course = $request->route('course');
        $student = $request->user();

        $previousLessons = $course->lessons()->select(["id","order"])->orderBy('order')->get();
        $previousLessonProgress = true;
        foreach ($previousLessons as $previousLesson) {
            if(!$student->hasCurrentLesson($previousLesson->id) ||
            ($student->hasCurrentLesson($previousLesson->id)->is_passed == false
            && $previousLessonProgress == true)){
                $previousLessonProgress = false;
            }
        }

        if(!$previousLessonProgress){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => __('auth.please_complete_all_lessons')
            ], 200);
        }

        return $next($request);
    }
}
