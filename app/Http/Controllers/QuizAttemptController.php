<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\UploadService;
use App\Models\StudentLessonProgress;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class QuizAttemptController extends BaseController
{

    protected $uploadService;

    public function __construct(UploadService $uploadService)
    {
        $this->middleware(['checkSubscription','checkSubmitQuiz'])->only('submitQuiz');
        $this->uploadService = $uploadService;
    }
    /**
     * @OA\Get(
     *     path="/api/quiz-attempts",
     *      tags={"Front Api Attempts"},
     *     summary="get all attempts of student",
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $attempts = $request->user()->attempts;
        return $this->sendResponse("",['attempts' => $attempts]);
    }

    /**
     * @OA\Get(
     *     path="/api/quiz-attempts/{quizAttempt_id}",
     *      tags={"Front Api Attempts"},
     *     summary="show quiz attempt and answers",
     *     @OA\Parameter(
     *         name="quizAttempt_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function show(QuizAttempt $quizAttempt)
    {
        $attempt = $quizAttempt;
        $attempt->questions;
        // foreach ($attempt->answers as $answer) {
        //     $answer->question;
        // }

        return $this->sendResponse("",['attempt' => $attempt]);
    }

    /**
     * @OA\Post(
     *     path="/api/quiz-attempts/{quiz_id}",
     *      tags={"Front Api Quiz Attempt"},
     *     summary="update user",
     * @OA\Parameter(
     *          name="quiz_id",
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
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        if ($quiz->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        //Validated
        $validate = Validator::make($request->all(),
        [
            'answers' => 'required|array|min:1',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $studentId = auth()->user()->id;
        $answers = $request->input('answers');
        $totalScore = 0;
        $maxGrade = 0;

        $attempt = $quiz->attempts()->create([
            'student_id' => $studentId,
        ]);


        foreach ($quiz->questions as $index => $question) {
            $image = null;
            $answer = "";
            $grade = 0;
            $questionId = $question->id;

            if (isset($answers[$questionId])) {
                if($question->type == 3){
                    $image = $this->uploadService->uploadImage('answers', $answers[$questionId]);
                }else{
                    if($answers[$questionId] == $question->correct_option){
                            $grade = $question->grade;
                            $totalScore += $grade;
                        }
                        $answer = $question->options[$answers[$questionId]];
                }
            }

            $attempt->questions()->attach($questionId,[
                'answer' =>  $answer,
                'image' =>  $image,
                'grade' => $grade,
            ]);
            $maxGrade += $question->grade;
        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($totalScore, $maxGrade);

        $attempt->update([
            'score'=>$score,
        ]);

        return $this->sendResponse("Quiz Created Successfully", ['attempt' => $attempt]);
    }

    // Add helper methods as needed
    private function calculateScore($totalScore ,$maxGrade = 10)
    {
        // Calculate the overall score as a percentage
        $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

        return $overallScore;
    }
}
