<?php

namespace App\Http\Controllers\Front\Lesson;

use App\Models\Quiz;
use App\Models\Lesson;
use App\Models\Question;
use App\Traits\QuizProcess;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UploadService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class QuizController extends BaseController
{
    protected $uploadService;
    public function __construct(UploadService $uploadService)
    {
        $this->middleware(['checkSubscription']);
        $this->middleware(['checkLessonAttempt',
        'quizCompleted','quizRepetition','quizReview'])->only('startQuiz');
        $this->middleware(['checkSubmitQuiz'])->only('submitQuiz');
        $this->uploadService = $uploadService;
    }
    use QuizProcess;
    /**
     * @OA\Get(
     *     path="/api/lessons/{lesson_id}/start-quiz",
     *      tags={"Front Api Quizzes"},
     *     summary="show lesson quiz",
     *     @OA\Parameter(
     *         name="lesson_id",
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
        $attempt = $user->hasCurrentLesson($lesson->id);
         if (!$attempt) {
             // Select a random test associated with the current lesson for the user
             $quiz = Lesson::find($lesson->id)->quizzes()->with('questions:id,title,options,type')->inRandomOrder()->first();
             $attempt = $user->attempts()->create([
                 'lesson_id' => $lesson->id,
                 'course_id' => $lesson->course_id,
                 'quiz_id' => $quiz->id,
             ]);
         }else{
            $quiz = Quiz::with('questions:id,title,options,type')->find($attempt->quiz_id);
         }

        $quiz->questions;
        return $this->sendResponse("",['quiz' => $quiz]);
    }

    /**
     * @OA\Post(
     *     path="/api/lessons/{lesson_id}/submit-quiz",
     *      tags={"Front Api Quizzes"},
     *     summary="submit lesson quiz",
     * @OA\Parameter(
     *          name="lesson_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="answers", type="object",example={"10":0,"25":1}
     *            ),
     *          @OA\Property(property="images", type="array", @OA\Items(
     *               type="integer",example="source file data form",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function submitQuiz(Request $request, Lesson $lesson)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'answers' => 'nullable|array',
            'images' => 'nullable|array',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = Auth::user();
        $attempt = $user->hasCurrentLesson($lesson->id);
        $quiz = $attempt->quiz;



        $grades = $this->submitAnswers($attempt,$quiz,$request->file('images'));

        // Calculate overall score based on grades
        $score = $this->calculateScore($grades["totalScore"], $grades["maxGrade"]);

        $response = $this->saveScore($attempt, $quiz, $score);


        return $this->sendResponse("Quiz Created Successfully", ['attempt' => $response['attempt'],'status'=> $response['status']]);
    }
}
