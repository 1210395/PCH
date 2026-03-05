<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sarah Johnson - Brand Designer
        DB::table('user_skills')->insert([
            ['user_id' => 1, 'skill_id' => 1], // Branding
            ['user_id' => 1, 'skill_id' => 2], // Logo Design
            ['user_id' => 1, 'skill_id' => 3], // Visual Identity
        ]);

        // Michael Chen - UI/UX Designer
        DB::table('user_skills')->insert([
            ['user_id' => 2, 'skill_id' => 4], // UI Design
            ['user_id' => 2, 'skill_id' => 5], // UX Research
            ['user_id' => 2, 'skill_id' => 6], // Prototyping
        ]);

        // Emma Wilson - Photographer
        DB::table('user_skills')->insert([
            ['user_id' => 3, 'skill_id' => 8], // Product Photography
            ['user_id' => 3, 'skill_id' => 9], // Retouching
            ['user_id' => 3, 'skill_id' => 10], // Lighting
        ]);

        // Alex Rodriguez - Illustrator
        DB::table('user_skills')->insert([
            ['user_id' => 4, 'skill_id' => 12], // Illustration
            ['user_id' => 4, 'skill_id' => 13], // Digital Art
            ['user_id' => 4, 'skill_id' => 14], // Character Design
        ]);
    }
}
