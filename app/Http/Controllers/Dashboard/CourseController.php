<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/courses",
     *      tags={"Courses"},
     *     summary="get all courses",
     *     operationId="index",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="filter courses with user",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             default="null",
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
     *             default="null",
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
     *             default="null",
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
     *             default="keyword",
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        // //Validated
        // $validate = Validator::make($request->all(),
        // [
        //     'search' => 'nullable|string|max:255',
        //     'semester' => 'nullable|in:first semester,second semester,full semester',
        //     'user_id'=> ['nullable',Rule::exists('users','id')->where('role','teacher')],
        //     'level_id'=> 'nullable|exists:level,id'
        // ]);

        $courses = Course::with(['user','level','subscriptions'])
        ->when($request->user_id,function ($query) use ($request){ // if user_id
            return $query->where('user_id',$request->user_id);
        })->when($request->level_id,function ($query) use ($request){ // if level_id
            return $query->where('level_id',$request->level_id);
        })->when($request->semester,function ($query) use ($request){ // if semester
            return $query->where('semester',$request->semester);
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
     *     path="/api/courses",
     *      tags={"Courses"},
     *     summary="Add New Courses",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="price", type="double", example="double"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="semester", type="enum", example="string"),
     *             @OA\Property(property="user_id", type="integer", example="integer"),
     *             @OA\Property(property="level_id", type="integer", example="integer"),
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
             'image' => 'required|string|max:255',
             'semester' => 'required|in:first semester,second semester,full semester',
             'user_id' => ['required',Rule::exists('users','id')->where('role','teacher')],
             'level_id'=> 'required|exists:levels,id'
         ]);


         if($validate->fails()){
             return response()->json([
                 'status' => false,
                 'message' => 'validation error',
                 'errors' => $validate->errors()
             ], 401);
         }

         $course = Course::create([
            'name'=>$request->name,
            'description'=>$request->description,
            'price'=>$request->price,
            'semester'=>$request->semester,
            'image'=>$request->image,
            'user_id'=>$request->user_id,
            'level_id'=>$request->level_id,
         ]);

         return response()->json([
             'status' => true,
             'message' => 'Course Created Successfully',
         ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/courses/{course_id}",
     *      tags={"Courses"},
     *     summary="show course",
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
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
     *     path="/api/courses/{course_id}",
     *      tags={"Courses"},
     *     summary="Updated Course",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="string"),
     *             @OA\Property(property="price", type="double", example="double"),
     *             @OA\Property(property="description", type="string", example="string"),
     *             @OA\Property(property="semester", type="enum", example="string"),
     *             @OA\Property(property="user_id", type="integer", example="integer"),
     *             @OA\Property(property="level_id", type="integer", example="integer"),
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
            'image' => 'required|string|max:255',
            'semester' => 'required|in:first semester,second semester,full semester',
            'user_id' => ['required',Rule::exists('users','id')->where('role','teacher')],
            'level_id'=> 'required|exists:levels,id',
            // 'active' => 'required|in:1,0',
        ]);


        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $course->update([
            'name'=>$request->name,
            'description'=>$request->description,
            'price'=>$request->price,
            'semester'=>$request->semester,
            'image'=>$request->image,
            'user_id'=>$request->user_id,
            'level_id'=>$request->level_id,
            'active'=> 0,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Course Updated Successfully',
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/courses/{course_id}",
     *      tags={"Courses"},
     *     summary="Delete Course",
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
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
     *     path="/api/courses/{course_id}/approve",
     *      tags={"Courses"},
     *     summary="Approve Course",
     *     @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             default="1",
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
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
