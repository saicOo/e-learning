<?php

namespace App\Http\Controllers\Dashboard\Reports;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class StudentController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/report/courses/{course_id}/students-score",
     *      tags={"Dashboard Api Report Students"},
     *     summary="Get Students Score",
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
    public function studentsScore(Course $course)
    {
        $courseId = $course->id;

        $students = Student::whereHas('attempts', function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        })->with(['attempts' => function ($query) use ($courseId) {
            $query->where('course_id', $courseId);
        }])->with(['subscriptions' => function ($query) use ($courseId) {
            $query->where('course_id', $courseId)->where('end_date', '>', now());
        }])->get();

        $studentScores = [];
        foreach ($students as $index => $student) {
            $studentScores[$index] =[
                 'id'=>$student->id,
                 'name'=>$student->name,
                 'email'=>$student->email,
                 'total_score'=>$student->attempts->sum('score'),
                ];
        }

         return $this->sendResponse("",['studentScores'=>$studentScores]);
    }
}
