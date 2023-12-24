<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuizAttemptController extends Controller
{
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
        $attempts = $quiz->attempts;
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
        $attempt = $quizAttempt;
        foreach ($attempt->answers as $answer) {
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
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="video", type="file", example="path file"),
     *             @OA\Property(property="attached", type="file", example="path file"),
     *             @OA\Property(property="course_id", type="integer", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, QuizAttempt $quizAttempt)
    {

        return $this->sendResponse("test this api");
        $quizAttempt->students()->sync();

        return $this->sendResponse("",['attempt' => $attempt]);
    }
}
