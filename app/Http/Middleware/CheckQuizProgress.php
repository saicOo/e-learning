<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckQuizProgress
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

        $studentId = auth()->user()->id;
        $lessonId = $quiz->lesson_id;
        $lessonProgress = StudentLessonProgress::where('student_id', $studentId)
        ->where('lesson_id', $lessonId)
        ->first();
        
        if($lessonProgress && $lessonProgress->is_passed){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => 'Not authorized.'
              ], 200);
        }
        if(!$lessonProgress){
            StudentLessonProgress::create([
                'student_id' => $studentId,
                'lesson_id' => $lessonId,
            ]);
        }

        return $next($request);
    }
}
