<?php
namespace App\Traits;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
trait QuizProcess
{
    private function saveScore($attempt, $quiz, $score){
        $statusQuiz = "pending";
        $status = "failed";
        $questionIsArticle = $quiz->questions()->where("type", 3)->first();
        if(!$questionIsArticle){
            $status = $this->quizProcess($score);
            $statusQuiz = $status;
        }

        $attempt->update([
            'score'=>$score,
            'status'=>$status,
            'is_submit'=> true,
            'is_visited'=> $status == "successful" ? true : false,
        ]);

        return ['attempt' => $attempt,'status'=> $statusQuiz];
    }

    private function submitAnswers($attempt ,$quiz , $answers,$inputImages){
        $images = [];
        if ($inputImages) {
            $path = "answers";
            foreach($inputImages as $image){
                    $imageName = Str::random(20) . uniqid()  . '.webp';
                        Image::make($image)->encode('webp', 65)->resize(600, null, function ($constraint) {
                            $constraint->aspectRatio();
                            })->save(Storage::disk('public')->path($path.'/'.$imageName));
                            array_push($images, $path.'/'.$imageName);
                }
        }
        $totalScore = 0;
        $maxGrade = 0;
        $attempt->update([
            'images'=>$images,
        ]);

        foreach ($quiz->questions as $index => $question) {

                $answer = null;
                $grade = 0;
                $questionId = $question->id;

                if(isset($answers[$questionId])){
                    if($answers[$questionId] == $question->correct_option){
                        $grade = $question->grade;
                        $totalScore += $grade;
                    }
                    $answer = isset($question->options[$answers[$questionId]]) ? $question->options[$answers[$questionId]] : null;
                }

                $attempt->questions()->attach($questionId,[
                    'answer' =>  $answer,
                    'grade' => $grade,
                ]);
                $maxGrade += $question->grade;

        }
        return ["maxGrade"=> $maxGrade,"totalScore"=>$totalScore];
    }
    // Add helper methods as needed
    private function calculateScore($totalScore ,$maxGrade = 10)
    {
        // Calculate the overall score as a percentage
        $overallScore = $totalScore != 0 ? ($totalScore / $maxGrade) * 100 : 0;

        return $overallScore;
    }

    private function quizProcess($score)
    {
            $status = "";

            if($score >= 50){
                $status = "successful";
            }else{
                $status = "failed";
            }
            return $status;
    }
}
