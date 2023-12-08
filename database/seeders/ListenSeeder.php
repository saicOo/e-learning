<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\Course;
use App\Models\Listen;
use Illuminate\Database\Seeder;

class ListenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Factory::create();
        for ($i=0; $i < 80; $i++) {
            Listen::create([
                'name'=>$faker->sentence(2),
                'description'=>$faker->sentence(20),
                'video'=>'apifile/zNAS2X0zOi3RsC58jRqVf5gqmEodZl2DeYEsbGhr.mp4',
                'course_id'=>Course::inRandomOrder()->first()->id,
                'active'=>1
             ]);
        }
    }
}
