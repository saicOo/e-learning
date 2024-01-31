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
        // if(!$request->route('lesson')){
            $previousLesson = $course->lessons()->select(["id","name","description","order"])->orderBy('order', 'DESC')->first();
            $student = $request->user();
            $previousLessonProgress = $student->hasCurrentLesson($previousLesson->id);
            // في حالة اذا كان الدرس السابق لم يتم النجاح في اختباره
            if (!$previousLessonProgress || !$previousLessonProgress->is_passed) {
                return response()->json([
                    'status_code' => 403,
                    'success' => false,
                    'message' => 'Please complete the previous lesson quiz.'
                ], 200);
            }
        // }

        return $next($request);
    }
}
