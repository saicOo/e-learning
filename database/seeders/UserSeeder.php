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
        User::create([
            'name'=>'manger',
            'email'=>'manger@app.com',
            'phone'=>'01231545',
            'password'=>bcrypt('1234'),
            'role'=>'manger',
        ]);

        User::create([
            'name'=>'teacher',
            'email'=>'teacher@app.com',
            'phone'=>'01291545',
            'password'=>bcrypt('1234'),
            'role'=>'teacher',
        ]);

        User::create([
            'name'=>'assistant1',
            'email'=>'assistant1@app.com',
            'phone'=>'02231545',
            'password'=>bcrypt('1234'),
            'role'=>'assistant',
            'user_id'=>2,
        ]);

        User::create([
            'name'=>'assistant2',
            'email'=>'assistant2@app.com',
            'phone'=>'01238545',
            'password'=>bcrypt('1234'),
            'role'=>'assistant',
            'user_id'=>2,
        ]);
    }
}
