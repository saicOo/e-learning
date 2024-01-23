<?php

namespace Database\Seeders;
use Carbon\Carbon;
use App\Models\Course;
use App\Models\Student;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i=0; $i < 100; $i++) {
            $student = Student::inRandomOrder()->first();
            $course = Course::inRandomOrder()->first();
            $current = Carbon::today()->subDays(rand(0, 50));
            // $addMonth = $current;

            $check = Subscription::where('student_id',$student->id)
            ->where('course_id',$course->id)->first();

            // while ($check) {
            //     $check = Subscription::where('student_id',$student->id)
            //     ->where('course_id',$course->id)->where('start_date',$current)->first();
            //     $current = Carbon::today()->subDays(rand(0, 100));
            // }
            if(!$check){
                $subscription = Subscription::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'price' => $course->price,
                    'start_date' => $current,
                    'end_date' => $current->copy()->addYear()
                ]);
            }
        }
    }
}
