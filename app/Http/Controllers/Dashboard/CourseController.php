<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:courses_read'])->only(['index','show']);
        $this->middleware(['permission:courses_create'])->only('store');
        $this->middleware(['permission:courses_update'])->only('update');
        $this->middleware(['permission:courses_delete'])->only('destroy');
        $this->middleware(['permission:courses_approve'])->only('approve');
    }
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
            'price' => 'required|integer|max:999999',
             'description' => 'required|string|max:255',
             'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
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
         $request_data = $validate->validated();
         if($request->image){
            $imageName = Str::random(20) . uniqid()  . '.webp';
                Image::make($request->image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->save( Storage::disk('public')->path('courses/'.$imageName));
            $request_data['image']  = 'courses/'.$imageName;
        }
         $course = Course::create($request_data);

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
            'name' => 'nullable|string|max:255',
            'price' => 'nullable|integer|max:999999',
            'description' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpg,png,jpeg,gif,svg',
            'semester' => 'nullable|in:first semester,second semester,full semester',
            'user_id'=> 'nullable|exists:users,id',
            'level_id'=> 'nullable|exists:levels,id',
            'category_id'=> 'nullable|exists:categories,id',
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
        if($request->image){
            if($course->image != 'courses/default.webp'){
                Storage::disk('public')->delete($course->image);
            }
            $imageName = Str::random(20) . uniqid()  . '.webp';
                Image::make($request->image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                    $constraint->aspectRatio();
                    })->save( Storage::disk('public')->path('courses/'.$imageName));
            $request_data['image']  = 'courses/'.$imageName;
        }
        $request_data['active'] = 0;
        $course->update($request_data);

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
        if($course->image != 'courses/default.webp'){
            Storage::disk('public')->delete($course->image);
        }
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
