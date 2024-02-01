<?php

namespace App\Http\Controllers\Dashboard\Lesson;

use App\Models\Lesson;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;

class QuizController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:quizzes_create'])->only('store');
    }
    /**
     * @OA\Post(
     *     path="/api/dashboard/lessons/{lesson_id}/quizzes",
     *      tags={"Dashboard Api Lesson Quizzes"},
     *     summary="Add New Quizzes Of Lesson",
     * @OA\Parameter(
     *         name="lesson_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="duration", type="integer", example="integer" ),
     *             @OA\Property(property="quiz_id", type="integer", example="1"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request, Lesson $lesson)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'quiz_id' => 'required|exists:quizzes,id',
            'duration' => 'required|integer|min:1|max:168',
        ]);
        $check_quiz = $lesson->quizzes()->where('quiz_id', $request->input("quiz_id"))->first();
        if($check_quiz){
            $validate->after(function($validate) {
                $validate->errors()->add('quiz_id', 'This quiz already exists');
              });
        }
        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quiz = $lesson->quizzes()->attach($request->input("quiz_id"),["duration"=>$request->input("duration")]);

        return $this->sendResponse("Quiz Created Successfully",['quiz' => $quiz]);

    }
}
