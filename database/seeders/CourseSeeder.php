<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\User;
use App\Models\Course;
use App\Models\Level;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        $semester = ['first semester' , 'second semester' , 'full semester'];
        for ($i=0; $i < 20; $i++) {
            Course::create([
                'name'=>$faker->sentence(2),
                'description'=>$faker->sentence(20),
                'price'=>rand(50,800),
                'semester'=>$semester[rand(0,2)],
                'image'=>'https://www.goethe.de/prj/dlp/assets/images/default.png',
                'user_id'=>User::whereRoleIs('teacher')->inRandomOrder()->first()->id,
                'level_id'=>Level::inRandomOrder()->first()->id,
                'active'=>1
             ]);
        }
    }
}
