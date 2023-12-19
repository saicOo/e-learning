<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\Question;
use App\Models\QuizResult;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function submitQuiz(Request $request, Quiz $quiz)
    {
        //Validated
        $validate = Validator::make($request->all(),
        [
            'answers' => 'required|array|min:1',
        ]);


        if($validate->fails()){
            return response()->json([
                'success' => false,
                'status_code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => 'validation error',
                'errors' => $validate->errors()
            ], 200);
        }

        $answers = $request->input('answers');

        // Example: Assign grades based on correct answers
        $responses = [];
        foreach ($answers as $questionId => $userAnswer) {
            // Replace this logic with your grading criteria
            $dataQuestion->correctAnswer = $this->getCorrectAnswerForQuestion($questionId); // Replace with your actual logic
            $grade = ($userAnswer == $dataQuestion->correctAnswer) ? $dataQuestion->grade : 0;
            $responses[$questionId] = [
                'answer' => $userAnswer, // index
                'grade' => $grade,
            ];
        }

        // Calculate overall score based on grades
        $score = $this->calculateScore($grades);
        return $responses;
        // Save quiz data to the database
        $quiz->quizResult()->create([
            'student_id' => auth()->user()->id, // Assuming you have authentication set up
            'course_id' => $courseId,
            'responses' => $responses,
            'score' => $score,
        ]);

        // return redirect()->route('quiz.result', ['quizId' => $quiz->id]);
    }

    // Add helper methods as needed
    private function getCorrectAnswerForQuestion($questionId)
    {
        return Question::find($questionId)->first();
    }

    private function calculateScore($responses)
    {
        $totalQuestions = count($responses);
        $totalScore = 0;

        foreach ($responses as $response) {
            // Assuming grades are integers (e.g., 0 for incorrect, 1 for partially correct, 2 for correct)
            $grade = $response['grade'];

            // Add the grade to the total score
            $totalScore += $grade;
        }

        // Calculate the overall score as a percentage
        $overallScore = ($totalScore / ($totalQuestions * 2)) * 100; // Assuming grades range from 0 to 2

        return $overallScore;
    }
}
