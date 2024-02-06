<?php

namespace Database\Seeders;
use Faker\Factory;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Category;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $quizzes_name = ['نموذج أ','نموذج ب','نموذج ج','نموذج د','اختبار علي الوحدة الاولي','اختبار علي الوحدة الثانية'];
        $faker = Factory::create();
        for ($i=0; $i < 100; $i++) {
            $category = Category::inRandomOrder()->first();
            $questions =  $category->inRandomOrder()->limit(5)->get()->pluck('id');

            $quiz = $category->quizzes()->create([
                "title" => $quizzes_name[rand(0,count($quizzes_name) - 1)],
                "questions_count" => 3,
            ]);

            foreach ($questions as $question_id) {
                    $quiz->questions()->attach($question_id);
            }
        }
        // $quizzes_name = ['نموذج أ','نموذج ب','نموذج ج','نموذج د','اختبار علي الوحدة الاولي','اختبار علي الوحدة الثانية'];
        // $types = ['course','lesson'];
        // $faker = Factory::create();
        // for ($i=0; $i < 100; $i++) {
        //     $course = Course::inRandomOrder()->first();
        //     $lessons = $course->lessons;
        //     $lesson_count = $lessons->count();
        //     $lesson_index = rand(0,$lesson_count - 1);
        //     $lesson_id = $lessons[$lesson_index] ? $lessons[$lesson_index]->id : null;
        //     $questions_count =  $lessons[$lesson_index]->questions()->count();
        //     $questions =  $lessons[$lesson_index]->questions()->pluck('id');
        //     $type = $types[rand(0,1)];

        //     $quiz = $course->quizzes()->create([
        //         "title" => $quizzes_name[rand(0,count($quizzes_name) - 1)],
        //         "type" => $type,
        //         "questions_count" => $questions_count,
        //         "lesson_id" => $type == "lesson" ? $lesson_id : null,
        //         'publish'=>"publish"
        //     ]);

        //     foreach ($questions as $question_id) {
        //             $quiz->questions()->attach($question_id);
        //     }
        // }
    }
}
