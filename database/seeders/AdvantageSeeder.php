<?php

namespace Database\Seeders;

use App\Models\Advantage;
use Illuminate\Database\Seeder;

class AdvantageSeeder extends Seeder
{
    public function run()
    {
        $advantage = Advantage::create(['image' => 'advantages/test.jpg']);
        $languages = ['ar', 'en', 'fr', 'tl', 'ur'];

        foreach ($languages as $lang) {
            $advantage->translations()->create([
                'locale' => $lang,
                'title' => "Test Title ($lang)",
                'description' => "Test Description ($lang)",
            ]);
        }
    }
}
