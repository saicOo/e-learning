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
        $student = Student::create([
            'name' => 'sayed',
            'email' => 'sayed@app.com',
            'attendance_type' => 'online',
            'phone' => '01157656',
            'level_id' => 1,
            'password' => Hash::make('1234')
        ]);
    }
}
