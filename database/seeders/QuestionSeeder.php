<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        for ($i=0; $i < 30; $i++) {
            $options = [];
            $type = rand(1,2); //1=>TrueFalse, 2=>Choice,3 =>Article'
            $optionCount = 2;
            $correct_option = rand(0,1);
            if ($type == 2) {
                $optionCount = 4;
                $correct_option =  rand(0,3);
            }
            for ($j=1; $j <= $optionCount; $j++) {
                array_push($options,$faker->sentence(rand(1,4)));
            }

            $course = Course::inRandomOrder()->first();
            $lesson_id = $course->lessons[0]->id ? $course->lessons[0]->id : null;
            $question = $course->questions()->create([
                "title" => $faker->sentence(rand(2,8)),
                "grade" => rand(1,10),
                "type" => $type,
                "image" => null,
                "correct_option" => $correct_option,
                "options" => $options,
                "lesson_id" =>$lesson_id,
            ]);
        }
    }
}
