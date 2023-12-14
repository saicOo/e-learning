<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
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
     *         name="active",
     *         in="query",
     *         description="filter courses with active (active = 1 , not active = 0)",
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
        $courses = Course::with(['user:id,name,email','level:id,name','category:id,name'])
        ->when($request->user_id,function ($query) use ($request){ // if user_id
            return $query->where('user_id',$request->user_id);
        })->when($request->level_id,function ($query) use ($request){ // if level_id
            return $query->where('level_id',$request->level_id);
        })->when($request->category_id,function ($query) use ($request){ // if category_id
            return $query->where('category_id',$request->category_id);
        })->when($request->semester,function ($query) use ($request){ // if semester
            return $query->where('semester',$request->semester);
        })->when($request->active,function ($query) use ($request){ // if active
            return $query->where('active',$request->active);
        })->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')->OrWhere('description','Like','%'.$request->search.'%');
        })->get();

            return response()->json([
                'status' => true,
                'data' => [
                    'courses' => $courses,
                ]
            ], 200);
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
        return response()->json([
            'status' => true,
            'data' => [
                'course' => $course,
            ]
        ], 200);
    }

}
