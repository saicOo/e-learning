<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Course;
use Illuminate\Http\Request;

class CheckLessonProgress
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
        $currentLesson = $request->route('lesson');
        if(!$currentLesson){
            return $next($request);
        }
        $course = Course::find($currentLesson->course_id);

        if($course->lessons()->orderBy('order')->first()->id == $currentLesson->id){
            // Handle edge case for the first lesson
            return $next($request);
        }

        $previousLesson = $course->lessons()
        ->where('order', '<', $currentLesson->order)
        ->orderBy('order', 'desc')
        ->first();
        $student = $request->user();
        $currentLessonProgress = $student->hasCurrentLesson($currentLesson->id);
        $previousLessonProgress = $student->hasCurrentLesson($previousLesson->id);

        // في حالة اذا كان الدرس السابق لم يتم النجاح في اختباره
        if ((!$previousLessonProgress || !$previousLessonProgress->is_passed) && (!$currentLessonProgress || !$currentLessonProgress->is_passed)) {
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => 'Please complete the previous lesson quiz.'
              ], 200);
        }
        return $next($request);
    }
}
