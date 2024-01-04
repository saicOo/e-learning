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
        $options_name = ['الاختيار أ','الاختيار ب','الاختيار ج','الاختيار د','صح','غلط'];
        $questions_name = ['لماذا السؤال الاول','لماذا السؤال الثاني','لماذا السؤال الثالث'
        ,'لماذا السؤال الرابع','هل السؤال الاول','هل السؤال الثاني',];
        for ($i=0; $i < 150; $i++) {
            $options = [];
            $type = rand(1,3); //1=>TrueFalse, 2=>Choice,3 =>Article'
            $optionCount = 2;
            $correct_option = rand(0,1);
            if ($type == 2) {
                $optionCount = 4;
                $correct_option =  rand(0,3);
            }
            for ($j=1; $j <= $optionCount; $j++) {
                array_push($options,$options_name[rand(0,count($options_name) - 1)]);
            }

            $course = Course::inRandomOrder()->first();
            $lessons = $course->lessons;
            $lesson_count = $lessons->count();
            $lesson_index = rand(0,$lesson_count - 1);
            $lesson_id = $lessons[$lesson_index] ? $lessons[$lesson_index]->id : null;
            $question = $course->questions()->create([
                "title" => $questions_name[rand(0,count($questions_name) - 1)],
                "grade" => rand(1,10),
                "type" => $type,
                "image" => $type != 3 ?  null : "questions/exam.jpg",
                "correct_option" => $type != 3 ? $correct_option : null,
                "options" => $type != 3 ? $options : null,
                "lesson_id" =>$lesson_id,
            ]);
        }
    }
}
