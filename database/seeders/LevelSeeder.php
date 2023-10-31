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
        for ($i=1; $i <= 6; $i++) {
            Level::create([
                'name' => 'Level '.$i,
            ]);
        }
    }
}
