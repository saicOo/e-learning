<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{

    public function index(Request $request)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'name' => 'nullable|string|max:255',
            'semester' => 'nullable|in:first semester,second semester,full semester',
            'user_id'=> ['nullable',Rule::exists('users','id')->where('role','teacher')],
            'level_id'=> 'nullable|exists:level,id'
        ]);

        $courses = Course::with(['user','level','students'])->when($request->user_id,function ($query) use ($request){ // if user_id
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

    public function show(Course $course)
    {
        return response()->json([
            'status' => true,
            'data' => [
                'course' => $course,
            ]
        ], 200);
    }

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

    public function destroy(Course $course)
    {
        $course->delete();
            return response()->json([
                'status' => true,
                'message' => 'Deleted Data Successfully',
            ], 200);
    }

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
