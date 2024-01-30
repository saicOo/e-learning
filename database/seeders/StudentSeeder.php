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
        $attendance_type = ['online','offline'];
        for ($i=1; $i < 30; $i++) {
            Student::create([
                'name' => 'st'.$i,
                'email' => $i.'st@app.com',
                'attendance_type' => $attendance_type[rand(0,1)],
                'phone' => '01157656'.$i,
                'password' => Hash::make('1234')
            ]);
        }
    }
}
