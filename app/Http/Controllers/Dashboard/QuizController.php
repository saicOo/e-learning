<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class QuizController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/courses/{course_id}/quizzes",
     *      tags={"Dashboard Api Quizzes"},
     *     summary="get all quizzes",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *   @OA\Parameter(
     *         name="listen_id",
     *         in="query",
     *         description="filter quizzes with listen",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name or description",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request, Course $course)
    {
        $quizzes = Quiz::with('questions')->when($course->id,function ($query) use ($course){ // if course_id
            return $query->where('course_id',$course->id);
        })->when($request->listen_id,function ($query) use ($request){ // if listen_id
            return $query->where('listen_id',$request->listen_id);
        })->when($request->type,function ($query) use ($request){ // if type
            return $query->where('type',$request->type);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('title','Like','%'.$request->search.'%');
        })->get();

        return response()->json([
            'status' => true,
            'data' => [
                'quizzes' => $quizzes,
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/quizzes",
     *      tags={"Dashboard Api Quizzes"},
     *     summary="Add New Quizzes",
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
     *             @OA\Property(property="title", type="string", example="string"),
     *             @OA\Property(property="grade", type="integer", example="integer"),
     *             @OA\Property(property="questions_count", type="integer", example="integer" ),
     *             @OA\Property(property="type", type="integer", example="course, listen"),
     *             @OA\Property(property="listen_id", type="integer", example="integer"),
     *             @OA\Property(property="integer", type="integer", example="integer"),
     *             @OA\Property(property="questions", type="array", @OA\Items(
     *               type="integer",example="1",
     *              ),),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request, Course $course)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'title' => 'required|string|max:1000',
            'type' => 'required|in:course,listen',
            'listen_id' => 'nullable|exists:listens,id',
            'quiz_id' => 'nullable|exists:quizzes,id',
            'questions_count' => 'required|integer',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|exists:questions,id',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $request_data = $validate->validated();
        unset($request_data['questions']);
        $quiz = $course->quizzes()->create($request_data);

        foreach ($request->questions as $index => $question_id) {
            if($request_data['questions_count'] >= $index+1){
                $quiz->questions()->attach($question_id);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Quiz Created Successfully',
            'data' => [
                'quiz' => $quiz,
            ]
        ], 200);


    }

     /**
     * @OA\Get(
     *     path="/api/dashboard/quizzes/{quiz_id}",
     *      tags={"Dashboard Api Quizzes"},
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

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return response()->json([
            'status' => true,
            'message' => 'Deleted Data Successfully',
        ], 200);
    }
}
