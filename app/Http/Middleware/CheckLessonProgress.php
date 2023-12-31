<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Models\QuizProcess;

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
        $currentLesson = $request->route('lesson'); // Get the current lesson ID from the route
        $course = Course::find($currentLesson->course_id);

        if($course->lessons[0]->id == $currentLesson->id){
            // Handle edge case for the first lesson
            return $next($request);
        }
        $previousLesson = $course->lessons()
        ->where('id', '<', $currentLesson->id)
        ->orderBy('id', 'desc')
        ->first();

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
        return $next($request);
    }
}
