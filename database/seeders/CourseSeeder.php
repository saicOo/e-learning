<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\User;
use App\Models\Level;
use App\Models\Course;
use App\Models\Category;
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
        for ($i=0; $i < 6; $i++) {
            Course::create([
                'name'=>$faker->sentence(2),
                'description'=>$faker->sentence(20),
                'price'=>rand(50,800),
                'semester'=>$semester[rand(0,2)],
                'user_id'=>User::whereRoleIs('teacher')->inRandomOrder()->first()->id,
                'level_id'=>Level::inRandomOrder()->first()->id,
                'category_id'=>Category::inRandomOrder()->first()->id,
                'publish'=>"publish"
             ]);
        }
    }
}
