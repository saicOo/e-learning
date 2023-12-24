<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as BaseController;

class LessonController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/courses/{course}/lessons",
     *      tags={"Front Api Lessons"},
     *     summary="get all lessons",
     *   @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         description="filter lessons with course",
     *         required=false,
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
        $lessons = Lesson::select("id","name","description")->with("quizzes:id,title,questions_count,lesson_id")->when($request->search,function ($query) use ($request){ // if search
            return $query->where('name','Like','%'.$request->search.'%')->OrWhere('description','Like','%'.$request->search.'%');
        })->where('course_id',$course->id)->where("publish","publish")->get();
        return $this->sendResponse("",['lessons' => $lessons]);
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
        return $this->sendResponse("",['lesson' => $lesson]);
    }
}
