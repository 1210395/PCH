<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            // Branding skills
            ['name' => 'Branding', 'slug' => 'branding'],
            ['name' => 'Logo Design', 'slug' => 'logo-design'],
            ['name' => 'Visual Identity', 'slug' => 'visual-identity'],
            // UI/UX skills
            ['name' => 'UI Design', 'slug' => 'ui-design'],
            ['name' => 'UX Research', 'slug' => 'ux-research'],
            ['name' => 'Prototyping', 'slug' => 'prototyping'],
            ['name' => 'Wireframing', 'slug' => 'wireframing'],
            // Photography skills
            ['name' => 'Product Photography', 'slug' => 'product-photography'],
            ['name' => 'Retouching', 'slug' => 'retouching'],
            ['name' => 'Lighting', 'slug' => 'lighting'],
            ['name' => 'Portrait Photography', 'slug' => 'portrait-photography'],
            // Illustration skills
            ['name' => 'Illustration', 'slug' => 'illustration'],
            ['name' => 'Digital Art', 'slug' => 'digital-art'],
            ['name' => 'Character Design', 'slug' => 'character-design'],
            ['name' => '3D Modeling', 'slug' => '3d-modeling'],
            // Web/App Design
            ['name' => 'Web Design', 'slug' => 'web-design'],
            ['name' => 'Mobile App Design', 'slug' => 'mobile-app-design'],
            ['name' => 'Responsive Design', 'slug' => 'responsive-design'],
            // Motion & Video
            ['name' => 'Motion Graphics', 'slug' => 'motion-graphics'],
            ['name' => 'Animation', 'slug' => 'animation'],
            ['name' => 'Video Editing', 'slug' => 'video-editing'],
        ];

        foreach ($skills as $skill) {
            \App\Models\Skill::create($skill);
        }
    }
}
