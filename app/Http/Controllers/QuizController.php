<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class QuizController extends BaseController
{
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
        $quiz->questions;
        return $this->sendResponse("",['quiz' => $quiz]);
    }

}
