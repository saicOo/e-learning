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
                    if(!$student->hasCurrentLesson($lesson->id) && $lesson->order == 1){
                        foreach ($lesson->quizzes as $quiz) {
                            $this->createAttemptStudent($quiz,$student->id,$lesson);
                        }
                    }
                }
            }
        }
    }

    public function createAttemptStudent($quiz,$studentId,$lesson){
        $totalScore = 0;
        $maxGrade = 0;

        $attempt = $quiz->attempts()->create([
            'student_id' => $studentId,
            'lesson_id' => $lesson->id,
            'course_id' => $lesson->course_id,
            'is_submit'=> true,
            'is_visited'=> true,
            'images'=> ["answers/57.jpg","answers/57.jpg","answers/57.jpg"],
        ]);

        foreach ($quiz->questions as $index => $question) {
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
            'status'=> $score >= 50 ? 'successful': 'failed',
        ]);

    }

    private function calculateScore($totalScore ,$maxGrade = 10)
    {
        // Calculate the overall score as a percentage
        $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

        return $overallScore;
    }
}
