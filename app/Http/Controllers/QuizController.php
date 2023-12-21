<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class QuizController extends Controller
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
        return response()->json([
            'status' => true,
            'data' => [
                'quiz' => $quiz,
            ]
        ], 200);
    }

}
