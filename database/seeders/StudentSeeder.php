<?php

namespace Database\Seeders;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendance_type = ['online','offline','mix'];
        for ($i=1; $i < 100; $i++) {
            Student::create([
                'name' => 'st'.$i,
                'email' => $i.'st@app.com',
                'attendance_type' => $attendance_type[rand(0,2)],
                'phone' => '01157656'.$i,
                'password' => Hash::make('1234')
            ]);
        }
    }
}
