<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\QuizProcess;

class CheckQuizProcess
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
        $courseId =  $quiz->course_id;

        $quizProcess = QuizProcess::query();
        $quizProcess->where('student_id', $studentId);

        if($quiz->type == "lesson"){
            $quizProcess->where('lesson_id', $lessonId);
        }else{
            $quizProcess->whereNull('lesson_id')->where('course_id', $courseId);

        }

        $quizProcess = $quizProcess->first();

        if ($quizProcess) {
            // اذا تم بدء الاختبار
            if($quizProcess->status == "started"){
                $request->route()->setParameter('quiz', $quizProcess->quiz);
            }

            if($quizProcess->status == "repetition"){
                // اذا تم اعادة الاختبار
                $quizProcess->update([
                    'status' => 'started',
                    'quiz_id' => $quiz->id,
                ]);
            }

            if($quizProcess->status == "stoped"){
            // اذا تم انهاء الاختبار
                return response()->json([
                    'status_code' => 403,
                    'success' => false,
                    'message' => 'This quiz cannot be accessed at this time'
                  ], 200);
            }
        }

        if(!$quizProcess){
            QuizProcess::create([
                'student_id' => $studentId,
                'lesson_id' => $lessonId ? $lessonId : null,
                'course_id' => $quiz->course_id,
                'quiz_id' => $quiz->id,
            ]);
        }

        return $next($request);
    }
}
