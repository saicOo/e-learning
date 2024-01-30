<?php

namespace Database\Seeders;

use App\Models\Lesson;
use App\Models\Category;
use Illuminate\Database\Seeder;

class QuizLessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lessons = Lesson::all();
        foreach ($lessons as $lesson) {
            $quiz = $lesson->course->category->quizzes()->inRandomOrder()->first();
            $quiz_lesson = $lesson->quizzes()->attach($quiz->id,["duration"=>5]);
        }
    }
}
