<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use App\Models\Course;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends BaseController
{
    public function __construct()
    {

        $this->middleware(['permission:quizzes_revision'])->only('store');
        $this->middleware(['checkApiAffiliation'])->only("index");
        $this->middleware(['checkRevisionQuiz'])->only('store');
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/courses/{course_id}/quiz-attempts",
     *      tags={"Dashboard Api Attempts"},
     *     summary="get all attempts of student",
     * @OA\Parameter(
     *         name="course_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="is_visited",
     *         in="query",
     *         description="1=>visited or 0=> not visited",
     *         required=false,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     * @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="failed or successful",
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
    public function index(Course $course,Request $request)
    {
        $attempts = QuizAttempt::query();
        $attempts->with(['student','lesson']);
        if ($request->has('is_visited')) {
            $attempts->where('is_visited', $request->input('is_visited'));
        }
        if ($request->has('status')) {
            $attempts->where('status', $request->input('status'));
        }
        $attempts->where('course_id', $course->id)->where("is_submit",1);

        $attempts = $attempts->latest('created_at')->get();
        return $this->sendResponse("",['attempts' => $attempts]);
    }

    /**
     * @OA\Get(
     *     path="/api/dashboard/quiz-attempts/{quizAttempt_id}",
     *      tags={"Dashboard Api Attempts"},
     *     summary="show quiz attempt and answers",
     *     @OA\Parameter(
     *         name="quizAttempt_id",
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
     * )
     */
    public function show(QuizAttempt $quizAttempt)
    {
        $attempt = $quizAttempt;
        $attempt->student;
        $attempt->questions;

        return $this->sendResponse("",['attempt' => $attempt]);
    }

/**
     * @OA\Put(
     *     path="/api/dashboard/quiz-attempts/{quizAttempt_id}",
     *      tags={"Dashboard Api Attempts"},
     *     summary="Updated Attempts",
     * @OA\Parameter(
     *          name="quizAttempt_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="note", type="string", example="string"),
     *             @OA\Property(property="grades", type="object",example={"10":0,"25":1}
     *            ),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function update(Request $request, QuizAttempt $quizAttempt)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'note' => 'nullable|string|max:255',
            'grades' => 'required|array|min:1',
            'grades.*' => 'required|integer',
        ]);


        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $answers = $quizAttempt->answers;
        $grades = $request->input('grades');
        $totalScore = 0;
        $maxGrade = 0;

        foreach ($answers as $index => $answer) {
            $questionId = $answer->question_id;
            $question = $answer->question;
            $grade = $answer->grade;

            if (isset($grades[$questionId]) && $question->type == 3) {
                $grade = $grades[$questionId];
            }
            $totalScore += $grade;

            $answer->update([
                'grade' => $grade,
            ]);

            $maxGrade += $question->grade;
        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($totalScore, $maxGrade);

        $status = $this->quizProcess($score);

        $quizAttempt->update([
            'score'=> $score,
            'status'=> $status,
            'is_visited'=> true,
        ]);

        return $this->sendResponse("Quiz Grades Update Successfully", ['attempt' => $quizAttempt]);
        }

        private function calculateScore($totalScore ,$maxGrade = 10)
        {
            // Calculate the overall score as a percentage
            $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

            return $overallScore;
        }

        private function quizProcess($score)
        {
            if($score >= 50){
                $status = "successful";
            }else{
                $status = "failed";
            }

            return $status;
        }
}
