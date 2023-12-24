<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class QuizController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:quizzes_read'])->only(['index','show']);
        $this->middleware(['ability:teacher|assistant,quizzes_create,require_all'])->only('store');
        $this->middleware(['permission:quizzes_delete'])->only('destroy');
        $this->middleware(['checkApiAffiliation']);
    }
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
     *         name="lesson_id",
     *         in="query",
     *         description="filter quizzes with lesson",
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
        $quizzes = Quiz::with('questions')->where('course_id',$course->id)
        ->when($request->lesson_id,function ($query) use ($request){ // if lesson_id
            return $query->where('lesson_id',$request->lesson_id);
        })->when($request->type,function ($query) use ($request){ // if type
            return $query->where('type',$request->type);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('title','Like','%'.$request->search.'%');
        })->get();

        return $this->sendResponse("",['quizzes' => $quizzes]);
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
     *             @OA\Property(property="questions_count", type="integer", example="integer" ),
     *             @OA\Property(property="type", type="integer", example="course, lesson"),
     *             @OA\Property(property="lesson_id", type="integer", example="integer"),
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
            'type' => 'required|in:course,lesson',
            'lesson_id' => 'nullable|exists:lessons,id',
            'quiz_id' => 'nullable|exists:quizzes,id',
            'questions_count' => 'required|integer',
            'questions' => 'required|array|min:1',
            'questions.*' => 'required|exists:questions,id',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $request_data = $validate->validated();
        unset($request_data['questions']);
        $quiz = $course->quizzes()->create($request_data);

        foreach ($request->questions as $index => $question_id) {
            if($request_data['questions_count'] >= $index+1){
                $quiz->questions()->attach($question_id);
            }
        }

        return $this->sendResponse("Quiz Created Successfully",['quiz' => $quiz]);

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
        return $this->sendResponse("",['quiz' => $quiz]);
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }
}
