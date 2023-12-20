<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizResult;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/quizzes/{quiz_id}",
     *      tags={"Dashboard Api Quizzes"},
     *     summary="show quiz",
     *     @OA\Parameter(
     *         name="quiz_id",
     *         in="path",
     *         required=true,
     *         explode=true,
     *         @OA\Schema(
     *             type="integer",
     *         ),
     *     ),
     *       @OA\Response(response=200, description="OK"),
     *       @OA\Response(response=401, description="Unauthenticated"),
     *       @OA\Response(response=404, description="Resource Not Found")
     *    )
     */
    public function show(Quiz $quiz)
    {
        $quiz->questions;
        return response()->json([
            'status' => true,
            'data' => [
                'quiz' => $quiz,
            ]
        ], 200);
    }

    public function submitQuiz(Request $request, Quiz $quiz)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|integer',
        ]);

        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
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

            $attempt->answer()->create([
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
        return response()->json([
            'status' => true,
            'message' => 'Quiz Created Successfully',
            'data' => [
                'quiz' => $quiz,
            ]
        ], 200);
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
