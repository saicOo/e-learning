<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['course','lesson'];
        $faker = Factory::create();
        for ($i=0; $i < 150; $i++) {
            $course = Course::inRandomOrder()->first();
            $lessons = $course->lessons;
            $lesson_count = $lessons->count();
            $lesson_index = rand(0,$lesson_count - 1);
            $lesson_id = $lessons[$lesson_index] ? $lessons[$lesson_index]->id : null;
            $questions_count =  $lessons[$lesson_index]->questions()->count();
            $questions =  $lessons[$lesson_index]->questions()->pluck('id');


            $quiz = $course->quizzes()->create([
                "title" => $faker->sentence(rand(2,8)),
                "type" => $lesson_id != null ? $types[1] : $types[0],
                "questions_count" => $questions_count,
                "lesson_id" =>$lesson_id,
                'publish'=>"publish"
            ]);

            foreach ($questions as $question_id) {
                    $quiz->questions()->attach($question_id);
            }
        }
    }
}
