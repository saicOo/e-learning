<?php

namespace App\Http\Controllers\Front;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class QuizController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['checkSubscription','checkCourseProgress',
        'checkLessonProgress','checkQuizProcess'])->only('show');
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
    public function show(Quiz $quiz)
    {
        if ($quiz->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        $quiz->questions;
        return $this->sendResponse("",['quiz' => $quiz]);
    }

}
