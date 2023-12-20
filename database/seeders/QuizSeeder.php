<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\Course;
use App\Models\Listen;
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
        $types = ['course','listen'];
        $faker = Factory::create();
        for ($i=0; $i < 30; $i++) {
            $course = Course::inRandomOrder()->first();
            $listen_id = $course->listens[0]->id ? $course->listens[0]->id : null;
            $questions_count =  $course->listens[0]->questions()->count();
            $questions =  $course->listens[0]->questions()->pluck('id');


            $quiz = $course->quizzes()->create([
                "title" => $faker->sentence(rand(2,8)),
                "type" => $listen_id != null ? $types[1] : $types[0],
                "questions_count" => $questions_count,
                "listen_id" =>$listen_id,
            ]);

            foreach ($questions as $question_id) {
                    $quiz->questions()->attach($question_id);
            }
        }
    }
}
