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
        $courses_name = ["كورس كيمياء","كورس رياضات","كورس جولوجيا","كورس علوم"
        ,"كورس برمجة","كورس رسم","كورس دراسات","كورس اللغة العربية","كورس اللغة الانجليزية","كورس اللغة الفرنسية"];
        $semester = ['first semester' , 'second semester' , 'full semester'];
        for ($i=0; $i < count($courses_name) - 1; $i++) {
            Course::create([
                'name'=>$courses_name[$i],
                'description'=>$semester[rand(0,count($semester) - 1)],
                'price'=>rand(50,800),
                'type'=> $i > 6 ? 'offline' : 'online',
                'semester'=>$semester[rand(0,2)],
                'user_id'=>User::whereRoleIs('teacher')->inRandomOrder()->first()->id,
                'level_id'=>Level::inRandomOrder()->first()->id,
                'category_id'=>Category::inRandomOrder()->first()->id,
                'publish'=>"publish"
             ]);
        }
    }
}
