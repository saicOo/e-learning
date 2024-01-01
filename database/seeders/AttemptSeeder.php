<?php

namespace Database\Seeders;
use App\Models\Quiz;
use App\Models\Student;
use App\Models\Question;
use App\Models\QuizAttempt;
use Illuminate\Database\Seeder;
use App\Models\StudentLessonProgress;

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
                        $this->createAttemptStudent($quiz,$student->id);
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
        ]);


        foreach ($quiz->questions as $index => $question) {
            $image = $question->type != 3 ? null : "answers/57.jpg";
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
                'image' =>  $image,
                'grade' => $grade,
            ]);
            $maxGrade += $question->grade;
        }
        // Calculate overall score based on grades
        $score = $this->calculateScore($totalScore, $maxGrade);

        $attempt->update([
            'score'=>$score,
        ]);
        $lessonProgress = StudentLessonProgress::where('student_id', $studentId)
        ->where('lesson_id', $quiz->lesson_id)
        ->first();
        if($lessonProgress){
            $lessonProgress->update([
                'quiz_id' => $quiz->id,
            ]);
        }else{
            StudentLessonProgress::create([
                'student_id' => $studentId,
                'lesson_id' => $quiz->lesson_id,
                'quiz_id' => $quiz->id,
                'status' => "started",
            ]);
        }

    }

    private function calculateScore($totalScore ,$maxGrade = 10)
    {
        // Calculate the overall score as a percentage
        $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

        return $overallScore;
    }
}
