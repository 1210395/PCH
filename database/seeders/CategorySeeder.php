<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Branding', 'slug' => 'branding', 'description' => 'Brand identity, logo design, and visual identity systems', 'icon' => 'branding.svg', 'order' => 1],
            ['name' => 'UI/UX', 'slug' => 'ui-ux', 'description' => 'User interface and user experience design', 'icon' => 'ui-ux.svg', 'order' => 2],
            ['name' => 'Photography', 'slug' => 'photography', 'description' => 'Commercial, product, and creative photography', 'icon' => 'photography.svg', 'order' => 3],
            ['name' => 'Illustration', 'slug' => 'illustration', 'description' => 'Digital art, illustrations, and character design', 'icon' => 'illustration.svg', 'order' => 4],
            ['name' => 'Architecture', 'slug' => 'architecture', 'description' => 'Architectural design and visualization', 'icon' => 'architecture.svg', 'order' => 5],
            ['name' => 'Fashion', 'slug' => 'fashion', 'description' => 'Fashion design and styling', 'icon' => 'fashion.svg', 'order' => 6],
            ['name' => 'Digital Art', 'slug' => 'digital-art', 'description' => '3D modeling, digital painting, and creative art', 'icon' => 'digital-art.svg', 'order' => 7],
            ['name' => 'Graphic Design', 'slug' => 'graphic-design', 'description' => 'Print design, posters, and marketing materials', 'icon' => 'graphic-design.svg', 'order' => 8],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }
    }
}
