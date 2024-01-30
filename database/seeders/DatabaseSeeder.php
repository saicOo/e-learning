<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LaratrustSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(LevelSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(LessonSeeder::class);
        $this->call(StudentSeeder::class);
        $this->call(QuestionSeeder::class);
        $this->call(QuizSeeder::class);
        $this->call(SubscriptionSeeder::class);
        $this->call(QuizLessonSeeder::class);
        $this->call(QuizCourseSeeder::class);
        $this->call(AttemptSeeder::class);
    }
}
