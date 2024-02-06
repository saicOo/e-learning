<?php

namespace App\Http\Controllers\Front;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController as BaseController;

class LessonController extends BaseController
{

    public function __construct()
    {
        $this->middleware(['checkSubscription']);
        $this->middleware(['checkLessonProgress'])->only('show');
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

        if ($course->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        $user = Auth::user();
        $sessions = $user->hasSessions($course->id);

        $lessons = Lesson::query();
        $lessons->select(["id","name","description"]);
        $lessons->with(["quizzes:id,title,questions_count","attempts"]);

        $lessons->where('course_id', $course->id);
        // Filter by course name
        if ($request->has('search')) {
            $lessons->where('name', 'like', '%' . $request->input('search') . '%');
        }

        $lessons->where('publish', "publish");

        $lessons = $lessons->orderBy('order')->get();

        $lessonsData = [];
        foreach ($lessons as $lesson) {
            $quizAttempt = null;
            $is_quiz = false;
            if($lesson->quizzes()->first()){
                $is_quiz = true;
            }

            $quizAttempt = $lesson->attempts()->where('student_id', $user->id)->first();

            $quizAttempt = $user->hasCurrentLesson($lesson->id);
            $lessonData = [
                'id' => $lesson->id,
                'name' => $lesson->name,
                'description' => $lesson->description,
                'is_quiz' => $is_quiz,
                'quiz_attempt' => $quizAttempt ? $quizAttempt : null,
            ];
            $lessonsData[] = $lessonData;
        }

        return $this->sendResponse("",['lessons' => $lessonsData,'sessions'=>$sessions]);

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
        if ($lesson->publish != "publish" || $lesson->course->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        $user = Auth::user();
        $quizAttempt = $user->hasCurrentLesson($lesson->id);
        $is_quiz = false;
        if($lesson->quizzes()->first()){
            $is_quiz = true;
        }
        return $this->sendResponse("",['lesson' => $lesson,'is_quiz'=>$is_quiz,'quizAttempt'=> $quizAttempt]);
    }
}
