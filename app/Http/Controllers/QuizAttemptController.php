<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Support\Facades\Validator;

class QuizAttemptController extends BaseController
{

    /**
     * @OA\Get(
     *     path="/api/quiz-attempts",
     *      tags={"Front Api Attempts"},
     *     summary="get all attempts of student",
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     * )
     */
    public function index(Request $request)
    {
        $attempts = $request->user()->attempts;
        return $this->sendResponse("",['attempts' => $attempts]);
    }

    /**
     * @OA\Get(
     *     path="/api/quiz-attempts/{quizAttempt_id}",
     *      tags={"Front Api Attempts"},
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
        foreach ($attempt->answers as $answer) {
            $answer->question;
        }

        return $this->sendResponse("",['attempt' => $attempt]);
    }

    /**
     * @OA\Post(
     *     path="/api/quiz-attempts/{quiz_id}",
     *      tags={"Front Api Quiz Attempt"},
     *     summary="update user",
     * @OA\Parameter(
     *          name="quiz_id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     * @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="answers", type="object",example={"10":0,"25":1}
     *            ),
     *         ),
     *     ),
     *     @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|integer',
        ]);

        if($validate->fails()){
            return $this->sendError('validation error' ,$validate->errors(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        // Save quiz data to the database
        $attempt = $quiz->attempts()->create([
            'student_id' => auth()->user()->id,
        ]);
        $answers = $request->input('answers');

        // Example: Assign grades based on correct answers
        $responses = [];
        $maxGrade = 0;
        $grade = 0;
        $answer = "";
        foreach ($answers as $questionId => $studentAnswer) {
            // Replace this logic with your grading criteria
            $dataQuestion = $this->getCorrectAnswerForQuestion($questionId); // Replace with your actual logic
            $grade = ($studentAnswer == $dataQuestion->correct_option) ? $dataQuestion->grade : 0;
            $responses[$questionId] = [
                'answer' => $studentAnswer, // string
                'grade' => $grade,
            ];

            $answer = $studentAnswer;
            if($dataQuestion->type != 3){
                $answer = $dataQuestion->options[$studentAnswer];
            }

            $attempt->answers()->create([
                'question_id' => $questionId,
                'answer' =>  $answer,
                'grade' => $grade,
            ]);
            $maxGrade += $dataQuestion->grade;
        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($responses, $maxGrade);

        $attempt->update(['score'=>$score]);

        $quiz->attempts;
        return $this->sendResponse("Quiz Created Successfully",['quiz' => $quiz]);
    }

    // Add helper methods as needed
    private function getCorrectAnswerForQuestion($questionId)
    {
        return Question::find($questionId);
    }

    private function calculateScore($responses ,$maxGrade = 10)
    {
        $totalQuestions = count($responses);
        $totalScore = 0;
        foreach ($responses as $response) {
            // Add each grade to the total score
            $totalScore += $response['grade'];
        }

        // Calculate the overall score as a percentage
        $overallScore = $totalQuestions > 0 ? (($totalScore / $maxGrade) * 100) : 0;

        return $overallScore;
    }
}
