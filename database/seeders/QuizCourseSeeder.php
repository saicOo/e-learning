<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class QuizCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = Course::all();
        foreach ($courses as $course) {
            $quiz = $course->category->quizzes()->inRandomOrder()->first();
            $quiz_course = $course->quizzes()->attach($quiz->id,["duration"=>5]);
        }
    }
}
