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

    /**
     * @OA\Post(
     *     path="/api/subscriptions",
     *      tags={"Subscriptions"},
     *     summary="Automatically subscribe or update students' subscription to courses for a month",
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="student_id", type="integer", example="1"),
     *             @OA\Property(property="course_id", type="integer", example="1"),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
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
            $student->courses()->updateExistingPivot($courseId,
            [
                'enrollment_date' => now(),
                'completion_date' => $completion_date,
            ]);
            $message = 'Student Is Updated Subscription Successfully';
        }else{
            $student->courses()->attach($courseId,
            [
                'enrollment_date' => now(),
                'completion_date' => $completion_date,
            ]);
            $message = 'Student Is Subscription Successfully';
        }


        return response()->json([
            'status' => true,
            'message' => $message,
        ], 200);
    }
}
