<?php

namespace App\Http\Controllers\Front;

use App\Models\Course;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class CourseController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/courses",
     *      tags={"Front Api Courses"},
     *     summary="get all courses",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="filter courses with user",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *   @OA\Parameter(
     *         name="level_id",
     *         in="query",
     *         description="filter courses with level",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *   @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="filter courses with category",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="semester",
     *         in="query",
     *         description="filter courses with semester (first semester , second semester , full semester)",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name or description courses",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     * )
     */
    public function index(Request $request)
    {
        $courses = Course::query();

        $courses->with(['user:id,name,email','level:id,name','category:id,name']);
        $courses->where('publish', "publish");

        // Filter by course name
        if ($request->has('search')) {
            $courses->where('name', 'like', '%' . $request->input('search') . '%');
        }
        if($request->has('level_id')){
            $courses->where('level_id', $request->input('level_id'));
        }
        if($request->has('user_id')){
            $courses->where('user_id', $request->input('user_id'));
        }
        if($request->has('category_id')){
            $courses->where('category_id', $request->input('category_id'));
        }
        if($request->has('semester')){
            $courses->where('semester', $request->input('semester'));
        }

        $courses->where('type', "online");
        
        $courses = $courses->withCount(['lessons','students'])->get();

        return $this->sendResponse("",['courses' => $courses]);
    }

        /**
     * @OA\Get(
     *     path="/api/courses/{course_id}",
     *      tags={"Front Api Courses"},
     *     summary="show course",
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function show(Course $course)
    {
        if ($course->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        $course->user;
        $course->level;
        $course->category;
        $quiz = $course->quizzes()->where("type","course")->inRandomOrder()->first();
        return $this->sendResponse("",['course' => $course,'quiz'=>$quiz]);
    }

}
