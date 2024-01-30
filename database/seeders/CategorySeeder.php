<?php

namespace Database\Seeders;

use App\Models\Level;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['عربي','علوم','رياضيات'];
        foreach ($categories as $category) {
            # code...
            Category::create([
                'name' => $category,
                'level_id'=>Level::inRandomOrder()->first()->id,
            ]);
        }
    }
}
