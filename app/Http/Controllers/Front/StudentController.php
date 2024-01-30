<?php

namespace App\Http\Controllers\Front;

use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BaseController as BaseController;

class StudentController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/students-highest-scores",
     *      tags={"Front Api Students"},
     *     summary="Get Students Highest Scores",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function studentsHighestScores(){
        $highestScores = Student::join('quiz_attempts', 'students.id', '=', 'quiz_attempts.student_id')
    ->select('students.id', DB::raw('SUM( case when quiz_attempts.status = "successful" then quiz_attempts.score else 0 END) as total_score'))
    ->groupBy('students.id')
    ->orderByDesc('total_score')->limit(10)
    ->get();
    $rankedStudents = [];
    foreach ($highestScores as $index => $highestScores) {
        $student = Student::find($highestScores->id);
        $rankedStudents[$index] = [
            "id" => $student->id,
            "name" => $student->name,
            "email" => $student->email,
            "image_url" => $student->image_url,
            "total_score" => $highestScores->total_score,
            "rank" => $index + 1,
        ];
    }

        return $this->sendResponse("",['rankedStudents' => $rankedStudents]);
    }
}
