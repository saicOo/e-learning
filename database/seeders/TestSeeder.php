<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Database\Seeder;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();

           $course =  Course::create([
                'name'=>"كورس التجريبي",
                'description'=>'first semester',
                'price'=>rand(50,800),
                'type'=> 'online',
                'semester'=>"first semester",
                'user_id'=>User::whereRoleIs('teacher')->inRandomOrder()->first()->id,
                'level_id'=>Level::inRandomOrder()->first()->id,
                'category_id'=>Category::inRandomOrder()->first()->id,
                'publish'=>"publish"
             ]);

             $lesson1 = Lesson::create([
                'name'=>"الدرس الاول",
                'description'=>$faker->sentence(20),
                'video'=>'video/zSsEJPGdHgCgYqQNqV27S2mouiQAbFpl8r01QSbW.mp4',
                'attached'=>'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf',
                'order'=>1,
                'publish'=>"publish",
                'course_id'=>$course->id,
            ]);

            $lesson1 = Lesson::create([
                'name'=>"الدرس الثاني",
                'description'=>$faker->sentence(20),
                'video'=>'video/zSsEJPGdHgCgYqQNqV27S2mouiQAbFpl8r01QSbW.mp4',
                'attached'=>'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf',
                'order'=>2,
                'publish'=>"publish",
                'course_id'=>$course->id,
            ]);

            $question1 = Question::create([
                "title" => $faker->sentence(2),
                "grade" => 5,
                "type" => 1,
                "image" => null,
                "correct_option" => 0,
                "options" => ["True","False"],
                "lesson_id" =>$lesson1->id,
            ]);

            $question2 = Question::create([
                "title" => $faker->sentence(2),
                "grade" => 5,
                "type" => 1,
                "image" => null,
                "correct_option" => 0,
                "options" => ["True","False"],
                "lesson_id" =>$lesson2->id,
            ]);
    }
}
