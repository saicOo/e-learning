<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StudentLessonProgress;

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
        // التاكد اذا كان دخول الطالب مسجل من قبل
        // التاكد اذا كان الطالب لم ينهي الاختبار
        $studentId = auth()->user()->id;
        $quiz = $request->route('quiz');
        $lessonId =  $quiz->lesson_id;
        $lessonProgress = StudentLessonProgress::where('student_id', $studentId)
        ->where('lesson_id', $lessonId)
        ->first();
        if ($lessonProgress) {
            // اذا تم بدء الاختبار
            if($lessonProgress->status == "started"){
                $request->route()->setParameter('quiz', $lessonProgress->quiz);
            }

            if($lessonProgress->status == "repetition"){
                // اذا تم اعادة الاختبار
                $lessonProgress->update([
                    'status' => 'started',
                    'quiz_id' => $quiz->id,
                ]);
            }

            if($lessonProgress->status == "stoped"){
            // اذا تم انهاء الاختبار
                return response()->json([
                    'status_code' => 403,
                    'success' => false,
                    'message' => 'This quiz cannot be accessed at this time'
                  ], 200);
            }
        }

        if(!$lessonProgress){
            StudentLessonProgress::create([
                'student_id' => $studentId,
                'lesson_id' => $lessonId,
                'quiz_id' => $quiz->id,
            ]);
        }

        return $next($request);
    }
}
