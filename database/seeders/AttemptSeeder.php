<?php

namespace Database\Seeders;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Database\Seeder;
use App\Models\QuizProcess;

class AttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $students = Student::with('courses')->get();
        foreach ($students as $student) {
            foreach ($student->courses as $course) {
                foreach ($course->lessons as $lesson) {
                    foreach ($lesson->quizzes as $quiz) {
                        if(rand(0,1) == 1){
                            $this->createAttemptStudent($quiz,$student->id);
                        }else{
                            $this->quizProcess($quiz,$student->id);
                        }
                    }
                }
            }
        }
    }

    public function createAttemptStudent($quiz,$studentId){
        $totalScore = 0;
        $maxGrade = 0;

        $attempt = $quiz->attempts()->create([
            'student_id' => $studentId,
            'images'=> ["answers/57.jpg","answers/57.jpg","answers/57.jpg"],
        ]);

        foreach ($quiz->questions as $index => $question) {
            // $images = $question->type != 3 ? null : ["answers/57.jpg","answers/57.jpg","answers/57.jpg"];
            $answer = $question->type != 3 ? rand(0,1) : null ;
            $grade = 0;
            $questionId = $question->id;
            if($question->type != 3){
                if($answer == $question->correct_option){
                        $grade = $question->grade;
                        $totalScore += $grade;
                    }
                    $answer = $question->options[$answer];
            }

            $attempt->questions()->attach($questionId,[
                'answer' =>  $answer,
                'grade' => $grade,
            ]);
            $maxGrade += $question->grade;
        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($totalScore, $maxGrade);

        $attempt->update([
            'score'=> $score,
        ]);

        $this->quizProcess($quiz, $studentId, $score);
    }

    private function calculateScore($totalScore ,$maxGrade = 10)
    {
        // Calculate the overall score as a percentage
        $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

        return $overallScore;
    }

    private function quizProcess($quiz ,$studentId, $score = null)
    {
        $quizProcess = QuizProcess::where('student_id', $studentId)
        ->where('lesson_id', $quiz->lesson_id)
        ->first();
        if($quizProcess){
            $quizProcess->update([
                'quiz_id' => $quiz->id,
            ]);
        }else{
            if($score != null){

                if($score < 30) $status = "repetition";

                if($score > 30) $status = "stoped";

            }else{
                $status = "started";
            }


            QuizProcess::create([
                'student_id' => $studentId,
                'lesson_id' => $quiz->lesson_id,
                'course_id' => $quiz->course_id,
                'quiz_id' => $quiz->id,
                'status' => $status,
            ]);
        }
    }
}
