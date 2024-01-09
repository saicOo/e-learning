<?php

namespace App\Http\Controllers\Front;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class LessonController extends BaseController
{

    public function __construct()
    {
        $this->middleware(['checkSubscription','checkLessonProgress'])->only('show');
    }
    /**
     * @OA\Get(
     *     path="/api/courses/{course_id}/lessons",
     *      tags={"Front Api Lessons"},
     *     summary="get all lessons",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         description="filter lessons with course",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="filter search name or description",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request, Course $course)
    {

        $lessons = Lesson::query();
        $lessons->select(["id","name","description"]);
        $lessons->with(["quizzes:id,title,publish,questions_count,lesson_id","progress"]);

        $lessons->where('course_id', $course->id);
        // Filter by course name
        if ($request->has('search')) {
            $lessons->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $lessons->where('publish', "publish");

        $lessons = $lessons->orderBy('order')->get();

        $lessonsData = [];
        foreach ($lessons as $lesson) {
            $quiz = $lesson->quizzes()->inRandomOrder()->first();
            $progres = $lesson->progress()->where('student_id', $request->user()->id)->first();

            $lessonData = [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'description' => $lesson->description,
                'progres' => $progres ? [
                    'is_passed' => $progres->is_passed,
                    'status' => $progres->status
                    ] : null,
                'quiz' => $quiz ?  : null,
            ];
            $lessonsData[] = $lessonData;
        }

        return $this->sendResponse("",['lessons' => $lessonsData]);

    }

    /**
     * @OA\Get(
     *     path="/api/lessons/{lesson_id}",
     *      tags={"Front Api Lessons"},
     *     summary="show lesson",
     *     @OA\Parameter(
     *         name="lesson_id",
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
    public function show(Lesson $lesson)
    {
        if ($lesson->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        $quiz = $lesson->quizzes()->inRandomOrder()->first();
        return $this->sendResponse("",['lesson' => $lesson,'quiz'=> $quiz ]);
    }
}
