<?php

namespace App\Http\Controllers\Front;

use App\Models\Course;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController as BaseController;

class CourseController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['checkSubscription'])->only('courseProgress');
    }
    /**
     * @OA\Get(
     *     path="/api/courses",
     *      tags={"Front Api Courses"},
     *     summary="get all courses",
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
     * @OA\Parameter(
     *         name="level_id",
     *         in="query",
     *         description="filter courses with level",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     * )
     */
    public function index(Request $request)
    {
        $courses = Course::query();

        $courses->with(['user:id,name,email','level:id,name','category:id,name']);
        $courses->where('publish', "publish");
        if($request->has('level_id')){
            $courses->where('level_id', $request->input('level_id'));
        }
        // Filter by course name
        if ($request->has('search')) {
            $search = $request->input('search');
            $courses->where(function($query) use ($search) {
                  $query->where('name', 'LIKE', '%'.$search.'%')
                    ->orWhereHas('category', function($q) use ($search){
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('user', function($q) use ($search){
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('level', function($q) use ($search){
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        $courses->where('type', "online");

        $courses = $courses->withCount(['lessons','students'])->latest('created_at')->get();

        return $this->sendResponse("",['courses' => $courses]);
    }

        /**
     * @OA\Get(
     *     path="/api/courses/{course_id}",
     *      tags={"Front Api Courses"},
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
     *       @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function show(Course $course)
    {
        if ($course->publish != "publish") {
            return $this->sendError('Record not found.');
        }

        $course->user;
        $course->level;
        $course->category;
        $lessons = $course->lessons()->select(["id","name","description","order"])->orderBy('order')->get();
        return $this->sendResponse("",['course' => $course,'lessons'=>$lessons]);
    }

        /**
     * @OA\Get(
     *     path="/api/courses/{course_id}/progress",
     *      tags={"Front Api Courses"},
     *     summary="progress course",
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
     *       @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function courseProgress(Request $request,Course $course)
    {
        if ($course->publish != "publish") {
            return $this->sendError('Record not found.');
        }
        $student = $request->user();
        $user = Auth::user();
        $quizAttempt = $user->hasCurrentCourse($course->id);
        $previousLessons = $course->lessons()->select(["id","name","description","order"])->orderBy('order')->get();
        $previousLessonProgress = true;
        foreach ($previousLessons as $previousLesson) {
            if(!$user->hasCurrentLesson($previousLesson->id) ||
            ($user->hasCurrentLesson($previousLesson->id)->is_passed == false
            && $previousLessonProgress == true)){
                $previousLessonProgress = false;
            }
        }
        $is_quiz = false;
        if($course->quizzes()->first()){
            $is_quiz = true;
        }
        return $this->sendResponse("",['course' => $course,
        'previousLessonProgress'=>$previousLessonProgress,'is_quiz'=>$is_quiz,'quizAttempt'=>$quizAttempt]);
    }

}
