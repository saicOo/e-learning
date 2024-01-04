<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\StudentLessonProgress;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends BaseController
{
    public function __construct()
    {
        $this->middleware(['checkApiAffiliation'])->only("index");
    }
    /**
     * @OA\Get(
     *     path="/api/dashboard/quizzes/{quiz_id}/quiz-attempts",
     *      tags={"Dashboard Api Attempts"},
     *     summary="get all attempts of student",
     * @OA\Parameter(
     *         name="quiz_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Quiz $quiz)
    {
        $attempts = QuizAttempt::query();
        $attempts->with('student:id,name,email');
        $attempts->where('quiz_id', $quiz->id);
        $attempts = $attempts->get();
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
            'note' => 'required|string|max:255',
            'grades' => 'required|array|min:1',
            'grades.*' => 'required|integer',
        ]);


        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $lessonProgress = StudentLessonProgress::where('student_id', $quizAttempt->student_id)
            ->where('quiz_id', $quizAttempt->quiz_id)->first();

        if(!$lessonProgress){
            return $this->sendError('You can no longer add grades for this quiz' ,[], 403);
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

        $status = $this->quizProgress($score, $lessonProgress);

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

        private function quizProgress($score ,StudentLessonProgress $lessonProgress)
        {
            $status = "";

            if($score >= 50){

                $status = "successful";
                $lessonProgress->update([
                    'status' => 'stoped',
                    'is_passed' => true,
                ]);

            }else{

                $status = "failed";
                $lessonProgress->update([
                    'status' => 'repetition',
                    'is_passed' => false,
                ]);

            }

            return $status;
        }
}
