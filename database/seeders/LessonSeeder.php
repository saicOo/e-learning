<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class LessonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $lessons_name = [1=>"الدرس الاول",2=>"الدرس الثاني"];
        // $lessons_name = [1=>"الدرس الاول",2=>"الدرس الثاني",3=>"الدرس الثالث",
        // 4=>"الدرس الرابع",5=>"الدرس الخامس",6=>"الدرس السادس"];
        $faker = Factory::create();
        $courses = Course::all();
        foreach ($courses as $course) {
            foreach ($lessons_name as $key => $lesson_name) {
                $course->lessons()->create([
                    'name'=>$lesson_name,
                    'description'=>$faker->sentence(20),
                    'video'=>'video/zSsEJPGdHgCgYqQNqV27S2mouiQAbFpl8r01QSbW.mp4',
                    'attached'=>'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf',
                    'order'=>$key,
                    'publish'=>"publish"
                ]);
            }
        }
    }
}
