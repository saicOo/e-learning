<?php

namespace App\Http\Controllers\Dashboard;

use Carbon\Carbon;
use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Models\CourseStudent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SubscribeController extends Controller
{
    
    public function index()
    {
        return Student::with('courses')->get();
    }

    public function subscrip(Request $request)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'student_id'=> 'required|exists:students,id',
            'course_id'=> 'required|exists:courses,id',
            // 'completion_date' => 'required|date_format:Y-m-d H:i:s|after:5 hours',
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 401);
        }

        $studentId = $request->student_id;
        $courseId = $request->course_id;

        $student = Student::find($studentId);
        $course = Course::find($request->courseId);

        $current = Carbon::now();
        $completion_date = $current->addMonth(1);

        if($student->courses()->where('course_id', $courseId)->first()){
            $student->courses()->sync($course,
            [
                'enrollment_date' => now(),
                'completion_date' => $completion_date,
            ]);
        }else{
            $student->courses()->attach($course,
            [
                'enrollment_date' => now(),
                'completion_date' => $completion_date,
            ]);
        }


        return response()->json([
            'status' => true,
            'message' => 'Student Is Subscriptions Successfully',
        ], 200);
    }
}
