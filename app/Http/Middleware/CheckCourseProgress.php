<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\QuizProcess;
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
        $course = $request->route('quiz')->course;
        if(!$request->route('quiz')->lesson){
            $previousLesson = $course->lessons()->select(["id","name","description","order"])->orderBy('order', 'DESC')->first();
            $student = $request->user();
            $previousLessonProgress = QuizProcess::where('student_id', $student->id)
                ->where('lesson_id', $previousLesson->id)
                ->first();

            // في حالة اذا كان الدرس السابق لم يتم النجاح في اختباره
            if (!$previousLessonProgress || !$previousLessonProgress->is_passed) {
                return response()->json([
                    'status_code' => 403,
                    'success' => false,
                    'message' => 'Please complete the previous lesson quiz.'
                ], 200);
            }
        }

        return $next($request);
    }
}
