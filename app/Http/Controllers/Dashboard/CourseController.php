<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/courses",
     *      tags={"Dashboard Api Courses"},
     *     summary="get all courses",
     *     operationId="index",
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
     *      @OA\Response(response=401, description="Unauthenticated"),
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
     * @OA\Post(
     *     path="/api/dashboard/courses",
     *      tags={"Dashboard Api Courses"},
     *     summary="Add New Courses",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="price", type="double", example="double"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="semester", type="enum", example="string"),
     *             @OA\Property(property="image", type="string", example="https://www.techsmith.com/blog/wp-content/uploads/2022/03/resize-image.png"),
     *             @OA\Property(property="user_id", type="integer", example="integer"),
     *             @OA\Property(property="level_id", type="integer", example="integer"),
     *             @OA\Property(property="category_id", type="integer", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function store(Request $request)
    {
         //Validated
         $validate = Validator::make($request->all(),
         [
             'name' => 'required|string|max:255',
            'price' => 'required',
             'description' => 'required|string|max:255',
             'image' => 'required|url|max:255',
             'semester' => 'required|in:first semester,second semester,full semester',
             'user_id'=> 'required|exists:users,id',
             'level_id'=> 'required|exists:levels,id',
            'category_id'=> 'required|exists:categories,id',
         ]);


         if($validate->fails()){
             return response()->json([
                 'success' => false,
                 'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                 'message' => 'validation error',
                 'errors' => $validate->errors()
             ], 200);
         }

         $course = Course::create($validate->validated());

         return response()->json([
             'status' => true,
             'message' => 'Course Created Successfully',
         ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/courses/{course_id}",
     *      operationId="getCourseById",
     *      tags={"Dashboard Api Courses"},
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
     *       @OA\Response(response=401, description="Unauthenticated"),
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

    /**
     * @OA\Put(
     *     path="/api/dashboard/courses/{course_id}",
     *      tags={"Dashboard Api Courses"},
     *     summary="Updated Course",
     * @OA\Parameter(
     *          name="course_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="price", type="double", example="double"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="semester", type="enum", example="string"),
     *             @OA\Property(property="image", type="string", example="https://www.techsmith.com/blog/wp-content/uploads/2022/03/resize-image.png"),
     *             @OA\Property(property="user_id", type="integer", example="integer"),
     *             @OA\Property(property="level_id", type="integer", example="integer"),
     *             @OA\Property(property="category_id", type="integer", example="integer"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function update(Request $request, Course $course)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'required|string|max:255',
            'price' => 'required',
            'description' => 'required|string|max:255',
            'image' => 'required|url|max:255',
            'semester' => 'required|in:first semester,second semester,full semester',
            'user_id'=> 'required|exists:users,id',
            'level_id'=> 'required|exists:levels,id',
            'category_id'=> 'required|exists:categories,id',
            // 'active' => 'required|in:1,0',
        ]);


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $course->update([
            'name'=>$request->name,
            'description'=>$request->description,
            'price'=>$request->price,
            'semester'=>$request->semester,
            'image'=>$request->image,
            'user_id'=>$request->user_id,
            'level_id'=>$request->level_id,
            'category_id'=>$request->category_id,
            'active'=> 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Course Updated Successfully',
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/dashboard/courses/{course_id}",
     *      tags={"Dashboard Api Courses"},
     *     summary="Delete Course",
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
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function destroy(Course $course)
    {
        $course->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }

    /**
     * @OA\Put(
     *     path="/api/dashboard/courses/{course_id}/approve",
     *      tags={"Dashboard Api Courses"},
     *     summary="Approve Course",
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
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function approve(Course $course)
    {
        $course->update([
            'active'=> 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Course Approved Successfully',
        ], 200);
    }
}
