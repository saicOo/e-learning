<?php

namespace Database\Seeders;
use App\Models\Level;
use Illuminate\Database\Seeder;

class LevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $levels = ['الصف الاول الاعدادي','الصف الثاني الاعدادي','الصف الثالث الاعدادي','الصف الاول الثانوي','الصف الثاني الثانوي','الصف الثالث الثانوي'];
        foreach ($levels as $level) {
            # code...
            Level::create([
                'name' => $level,
            ]);
        }

    }
}
