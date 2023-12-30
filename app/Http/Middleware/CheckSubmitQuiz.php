<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\StudentLessonProgress;

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
        $lessonProgress = StudentLessonProgress::where('student_id', $studentId)
        ->where('lesson_id', $lessonId)
        ->first();

        if(!$lessonProgress || ($lessonProgress && $lessonProgress->status != "started") ||
            ($lessonProgress && $lessonProgress->quiz_id != $quiz->id)){
            return response()->json([
                'status_code' => 403,
                'success' => false,
                'message' => 'This quiz cannot be accessed at this time !'
              ], 200);
        }

        // if($lessonProgress && $lessonProgress->status == "started"){
        // }
        $lessonProgress->update([
            'status' => 'stoped',
        ]);

        return $next($request);
    }
}
