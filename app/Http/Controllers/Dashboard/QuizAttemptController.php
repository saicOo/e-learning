<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['checkApiAffiliation'])->only("index");
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/quizzes/{quiz_id}/quiz-attempts",
     *      tags={"Dashboard Api Attempts"},
     *     summary="get all attempts of student",
     * @OA\Parameter(
     *         name="quiz_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Quiz $quiz)
    {
        $attempts = QuizAttempt::query();
        $attempts->with('student:id,name,email');
        $attempts->where('quiz_id', $quiz->id);
        $attempts = $attempts->get();
        return $this->sendResponse("",['attempts' => $attempts]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/quiz-attempts/{quizAttempt_id}",
     *      tags={"Dashboard Api Attempts"},
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
        $quizAttempt->quiz;
        $quizAttempt->student;
        foreach ($quizAttempt->answers as $answer) {
            $answer->question;
        }

        return $this->sendResponse("",['attempt' => $attempt]);
    }

/**
     * @OA\Put(
     *     path="/api/dashboard/quiz-attempts/{quizAttempt_id}",
     *      tags={"Dashboard Api Attempts"},
     *     summary="Updated Attempts",
     * @OA\Parameter(
     *          name="quizAttempt_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="note", type="string", example="string"),
     *             @OA\Property(property="answers", type="object",example={"10":0,"25":1}
     *            ),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, QuizAttempt $quizAttempt)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'note' => 'required|string|max:255',
            'grades' => 'required|array|min:1',
            'grades.*' => 'required|integer',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $grades = $request->input('grades');
        $responses = [];
        $maxGrade = 0;
        foreach ($grades as $questionId => $grade) {

            $responses[$questionId] = [
                'grade' => $grade,
            ];

            $quizAttempt->answers()->update([
                'grade' => $grade,
            ]);

            $maxGrade += $grade;
        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($responses, $maxGrade);

        $quizAttempt->update([
            'score'=>$score,
            'status'=> $score >= 50 ? "successful" : "failed",
        ]);

        return $this->sendResponse("Quiz Grades Update Successfully");
        }

        private function calculateScore($responses ,$maxGrade = 10)
        {
        $totalQuestions = count($responses);
        $totalScore = 0;
        foreach ($responses as $response) {
            // Add each grade to the total score
            $totalScore += $response['grade'];
        }

        // Calculate the overall score as a percentage
        $overallScore = $totalQuestions > 0 ? (($totalScore / $maxGrade) * 100) : 0;

        return $overallScore;
        }
}
