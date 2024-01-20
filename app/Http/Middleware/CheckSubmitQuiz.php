<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\QuizProcess;

class CheckSubmitQuiz
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

        if(!$quizProcess || ($quizProcess && $quizProcess->status != "started") ||
            ($quizProcess && $quizProcess->quiz_id != $quiz->id)){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => 'This test is not accessible at this time. Please re-enter later !'
              ], 200);
        }

        // $quizProcess->update([
        //     'status' => 'stoped',
        // ]);

        return $next($request);
    }
}
