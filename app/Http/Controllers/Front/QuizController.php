<?php

namespace App\Http\Controllers\Front;

use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class QuizController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['checkSubscription','checkLessonAttempt',
        'quizCompleted','quizRepetition','quizReview'])->only('show');
    }
    /**
     * @OA\Get(
     *     path="/api/quizzes/{quiz_id}",
     *      tags={"Front Api Quizzes"},
     *     summary="show quiz",
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *       @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function startQuiz(Lesson $lesson)
    {
         $user = Auth::user();
         $lessonId = $lesson->id;
         // Check if the user has an existing quiz_id for this lesson
         $attempt = $user->attempts()
             ->where('lesson_id', $lessonId)
             ->whereNotNull('quiz_id')
             ->first();

         if (!$attempt) {
             // Select a random test associated with the current lesson for the user
             $quiz = Lesson::find($lessonId)->quizzes()->with('questions:id,title,options,type')->inRandomOrder()->first();
             $attempt = $user->attempts()->create([
                 'lesson_id' => $lessonId,
                 'quiz_id' => $quiz->id,
             ]);
         }else{
            $quiz = Quiz::with('questions:id,title,options,type')->find($attempt->quiz_id);
         }

         $quiz->questions;
        return $this->sendResponse("",['quiz' => $quiz]);
    }

}
