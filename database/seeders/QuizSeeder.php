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
        for ($i=0; $i < 30; $i++) {
            $course = Course::inRandomOrder()->first();
            $lesson_id = $course->lessons[0] ? $course->lessons[0]->id : null;
            $questions_count =  $course->lessons[0]->questions()->count();
            $questions =  $course->lessons[0]->questions()->pluck('id');


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
