<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class QuestionController extends Controller
{
/**
     * @OA\Get(
     *     path="/api/dashboard/questions",
     *      tags={"Dashboard Api Questions"},
     *     summary="get all questions",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="query",
     *         description="filter questions with course",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *   @OA\Parameter(
     *         name="listen_id",
     *         in="query",
     *         description="filter questions with listen",
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
    public function index(Request $request)
    {
        $questions = Question::when($request->course_id,function ($query) use ($request){ // if course_id
            return $query->where('course_id',$request->course_id);
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
                'questions' => $questions,
            ]
        ], 200);
    }
    /**
     * @OA\Post(
     *     path="/api/dashboard/courses/{course_id}/questions",
     *      tags={"Dashboard Api Questions"},
     *     summary="Add New Question",
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
     *             @OA\Property(property="correct_option", type="integer", example="index option : 0, 1, 2, 3" ),
     *             @OA\Property(property="type", type="integer", example="1=>TrueFalse, 2=>Choice,3 =>Article'"),
     *             @OA\Property(property="listen_id", type="integer", example="integer"),
     *             @OA\Property(property="options", type="array", @OA\Items(
     *               type="string",example="option answer",
     *              ),),
     *
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
            'options' => 'required|array|min:1',
            'options.*' => 'required|string|max:1000',
            'grade' => 'required|integer|max:100',
            'correct_option' => 'required|in:0,1,2,3',
            'type' => 'required|in:1,2,3', //1=>TrueFalse, 2=>Choice,3 =>Article'
            'listen_id' => 'required|exists:listens,id',
        ]);


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $question = $course->questions()->create([
            "title" => $request->title,
            "grade" => $request->grade,
            "type" => $request->type,
            "correct_option" => $request->correct_option,
            "options" => $request->options,
            "listen_id" => $request->listen_id,
        ]);


            return response()->json([
                'status' => true,
                'message' => 'Question Created Successfully',
            ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/questions/{question_id}",
     *      tags={"Dashboard Api Questions"},
     *     summary="Delete Question",
     *     @OA\Parameter(
     *         name="question_id",
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
     *    )
     */
    public function destroy(Question $question)
    {
        $question->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }
}
