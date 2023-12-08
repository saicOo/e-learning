<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $manger = User::create([
            'name'=>'manger',
            'email'=>'manger@app.com',
            'phone'=>'01231545',
            'password'=>bcrypt('1234'),
        ]);
        $manger->attachRole('manger');
        $teacher1 = User::create([
            'name'=>'teacher1',
            'email'=>'teacher1@app.com',
            'phone'=>'01241545',
            'password'=>bcrypt('1234'),
        ]);
        $teacher1->attachRole('teacher');
        $teacher2 = User::create([
            'name'=>'teacher2',
            'email'=>'teacher2@app.com',
            'phone'=>'01291545',
            'password'=>bcrypt('1234'),
        ]);
        $teacher2->attachRole('teacher');
        $assistant1 = User::create([
            'name'=>'assistant1',
            'email'=>'assistant1@app.com',
            'phone'=>'02231545',
            'password'=>bcrypt('1234'),
            'user_id'=>2,
        ]);
        $assistant1->attachRole('assistant');
        $assistant2 = User::create([
            'name'=>'assistant2',
            'email'=>'assistant2@app.com',
            'phone'=>'01238545',
            'password'=>bcrypt('1234'),
            'user_id'=>2,
        ]);
        $assistant2->attachRole('assistant');
    }
}
