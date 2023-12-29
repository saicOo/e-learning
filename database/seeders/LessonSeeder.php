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
        $faker = Factory::create();
        for ($i=0; $i < 40; $i++) {
            Lesson::create([
                'name'=>$faker->sentence(2),
                'description'=>$faker->sentence(20),
                'video'=>'video/zSsEJPGdHgCgYqQNqV27S2mouiQAbFpl8r01QSbW.mp4',
                'attached'=>'attached/hasjhRZGDGT8ptnIBfyo4voFTFHvcOsnr5FRSlJA.pdf',
                'course_id'=>Course::inRandomOrder()->first()->id,
                'publish'=>"publish"
             ]);
        }
    }
}
