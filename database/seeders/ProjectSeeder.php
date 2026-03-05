<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            [
                'user_id' => 1, // Sarah Johnson
                'category_id' => 1, // Branding
                'title' => 'Modern Brand Identity System',
                'image' => 'https://images.unsplash.com/photo-1652805363265-b8fbf9bbdfac',
                'featured' => true,
                'likes_count' => 1243,
                'views_count' => 15200,
                'comments_count' => 87,
            ],
            [
                'user_id' => 2, // Michael Chen
                'category_id' => 2, // UI/UX
                'title' => 'E-Commerce UI/UX Design',
                'image' => 'https://images.unsplash.com/photo-1618761714954-0b8cd0026356',
                'featured' => true,
                'likes_count' => 892,
                'views_count' => 12450,
                'comments_count' => 54,
            ],
            [
                'user_id' => 3, // Emma Wilson
                'category_id' => 3, // Photography
                'title' => 'Premium Product Photography',
                'image' => 'https://images.unsplash.com/photo-1611930022073-b7a4ba5fcccd',
                'featured' => false,
                'likes_count' => 2341,
                'views_count' => 28900,
                'comments_count' => 143,
            ],
            [
                'user_id' => 4, // Alex Rodriguez
                'category_id' => 4, // Illustration
                'title' => 'Abstract Digital Art Series',
                'image' => 'https://images.unsplash.com/photo-1700605295478-2478ac29d2ec',
                'featured' => false,
                'likes_count' => 1567,
                'views_count' => 19800,
                'comments_count' => 92,
            ],
            [
                'user_id' => 5, // David Park
                'category_id' => 5, // Architecture
                'title' => 'Contemporary Architecture',
                'image' => 'https://images.unsplash.com/photo-1664819766323-78308c6c434c',
                'featured' => false,
                'likes_count' => 1098,
                'views_count' => 14300,
                'comments_count' => 67,
            ],
            [
                'user_id' => 6, // Lisa Anderson
                'category_id' => 6, // Fashion
                'title' => 'Sustainable Fashion Collection',
                'image' => 'https://images.unsplash.com/photo-1557777586-f6682739fcf3',
                'featured' => true,
                'likes_count' => 1876,
                'views_count' => 22100,
                'comments_count' => 108,
            ],
            [
                'user_id' => 7, // Ryan Taylor
                'category_id' => 7, // Digital Art
                'title' => '3D Digital Artwork',
                'image' => 'https://images.unsplash.com/photo-1661246627162-feb0269e0c07',
                'featured' => false,
                'likes_count' => 2109,
                'views_count' => 25600,
                'comments_count' => 124,
            ],
            [
                'user_id' => 8, // Nina Martinez
                'category_id' => 3, // Photography
                'title' => 'Creative Portrait Series',
                'image' => 'https://images.unsplash.com/photo-1586734073732-fd664fbd85c8',
                'featured' => false,
                'likes_count' => 1654,
                'views_count' => 18700,
                'comments_count' => 95,
            ],
        ];

        foreach ($projects as $project) {
            \App\Models\Project::create($project);
        }
    }
}
