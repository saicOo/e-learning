<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController as BaseController;

class QuizController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['permission:quizzes_create'])->only('store');
        $this->middleware(['permission:quizzes_delete'])->only('destroy');
        $this->middleware(['permission:quizzes_approve'])->only('approve');
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
     *   @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="filter quizzes with type",
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
        $quizzes = Quiz::query();
        $quizzes->with('questions');
        // Filter by course name
        if ($request->has('search')) {
            $quizzes->where('title', 'like', '%' . $request->input('search') . '%');
        }
        // Filter by course name
        if ($request->has('lesson_id')) {
            $quizzes->where('lesson_id', $request->input('lesson_id'));
        }
        // Filter by course name
        if ($request->has('type')) {
            $quizzes->where('type', $request->input('type'));
        }
        $quizzes->where('course_id', $course->id);

        $quizzes = $quizzes->get();

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

    /**
     * @OA\Delete(
     *     path="/api/dashboard/quizzes/{quiz_id}",
     *      tags={"Dashboard Api Quizzes"},
     *     summary="Delete Quizzes",
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
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return $this->sendResponse("Deleted Data Successfully");
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/quizzes/{quiz_id}/approve",
     *      tags={"Dashboard Api Quizzes"},
     *     summary="Approve Quizzes",
     *     @OA\Parameter(
     *         name="quiz_id",
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
     *             @OA\Property(property="publish", type="boolen", example="publish or unpublish"),
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function approve(Request $request, Quiz $quiz)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'publish' => 'required|in:publish,unpublish',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $quiz->update([
            'publish'=> $request->publish,
        ]);

        return $this->sendResponse("Quiz ".$request->publish." successfully");
    }
}
