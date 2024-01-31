<?php

namespace App\Http\Controllers\Dashboard\Course;
use App\Models\Course;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;

class QuizController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/quizzes",
     *      tags={"Dashboard Api Course Quizzes"},
     *     summary="Add New Quizzes Of Course",
     * @OA\Parameter(
     *         name="course_id",
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
    public function store(Request $request, Course $course)
    {
        $validate = Validator::make($request->all(),
        [
            'quiz_id' => 'required|exists:quizzes,id',
            'duration' => 'required|integer|min:1|max:168',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quiz = $course->quizzes()->attach($request->input("quiz_id"),["duration"=>$request->input("duration")]);
        return $this->sendResponse("Quiz Created Successfully",['quiz' => $quiz]);

    }
}
