<?php

namespace Database\Seeders;
use App\Models\Course;
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
        $course = Course::create([
            'name'=>'math',
            'description'=>'fo afoj mapef aefp apekfop',
            'price'=>60,
            'semester'=>'first semester',
            'image'=>'image.png',
            'user_id'=>1,
            'level_id'=>1,
         ]);
    }
}
