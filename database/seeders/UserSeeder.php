<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.johnson@example.com',
                'password' => bcrypt('password'),
                'title' => 'Brand Designer & Strategist',
                'bio' => 'Passionate brand designer with 10+ years of experience creating meaningful visual identities for businesses worldwide.',
                'location' => 'Palestine',
                'website' => 'https://sarahjohnson.com',
                'verified' => true,
                'followers_count' => 15200,
                'following_count' => 428,
                'projects_count' => 124,
                'avatar' => 'https://images.unsplash.com/photo-1475118258341-d2a655a5b11a?crop=entropy&cs=tinysrgb&fit=max&fm=jpg&ixid=M3w3Nzg4Nzd8MHwxfHNlYXJjaHwxfHxwcm9mZXNzaW9uYWwlMjBkZXNpZ25lciUyMHBvcnRyYWl0fGVufDF8fHx8MTc2MjA4ODcxM3ww&ixlib=rb-4.1.0&q=80&w=1080',
                'cover_image' => 'https://images.unsplash.com/photo-1627577741083-506d0b15a56a',
            ],
            [
                'name' => 'Michael Chen',
                'email' => 'michael.chen@example.com',
                'password' => bcrypt('password'),
                'title' => 'UI/UX Designer',
                'bio' => 'Creating user-centered digital experiences that combine aesthetics with functionality.',
                'location' => 'Palestine',
                'website' => 'https://michaelchen.design',
                'verified' => true,
                'followers_count' => 12800,
                'following_count' => 356,
                'projects_count' => 98,
                'avatar' => 'https://images.unsplash.com/photo-1532620161677-a1ca7d5d530f',
                'cover_image' => 'https://images.unsplash.com/photo-1618761714954-0b8cd0026356',
            ],
            [
                'name' => 'Emma Wilson',
                'email' => 'emma.wilson@example.com',
                'password' => bcrypt('password'),
                'title' => 'Commercial Photographer',
                'bio' => 'Specializing in product photography and creative visual storytelling for brands.',
                'location' => 'Palestine',
                'website' => 'https://emmawilson.photo',
                'verified' => true,
                'followers_count' => 18500,
                'following_count' => 521,
                'projects_count' => 156,
                'avatar' => 'https://images.unsplash.com/photo-1475118258341-d2a655a5b11a',
                'cover_image' => 'https://images.unsplash.com/photo-1611930022073-b7a4ba5fcccd',
            ],
            [
                'name' => 'Alex Rodriguez',
                'email' => 'alex.rodriguez@example.com',
                'password' => bcrypt('password'),
                'title' => 'Illustrator & Visual Artist',
                'bio' => 'Digital illustrator bringing ideas to life through colorful and engaging artwork.',
                'location' => 'Palestine',
                'verified' => false,
                'followers_count' => 9700,
                'following_count' => 234,
                'projects_count' => 87,
                'avatar' => 'https://images.unsplash.com/photo-1532620161677-a1ca7d5d530f',
                'cover_image' => 'https://images.unsplash.com/photo-1700605295478-2478ac29d2ec',
            ],
            [
                'name' => 'David Park',
                'email' => 'david.park@example.com',
                'password' => bcrypt('password'),
                'title' => 'Architectural Designer',
                'bio' => 'Contemporary architecture and visualization specialist.',
                'location' => 'Palestine',
                'verified' => true,
                'followers_count' => 11200,
                'following_count' => 398,
                'projects_count' => 76,
                'avatar' => 'https://images.unsplash.com/photo-1532620161677-a1ca7d5d530f',
                'cover_image' => 'https://images.unsplash.com/photo-1664819766323-78308c6c434c',
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@example.com',
                'password' => bcrypt('password'),
                'title' => 'Fashion Designer',
                'bio' => 'Sustainable fashion designer creating conscious and beautiful collections.',
                'location' => 'Palestine',
                'verified' => true,
                'followers_count' => 14300,
                'following_count' => 445,
                'projects_count' => 93,
                'avatar' => 'https://images.unsplash.com/photo-1475118258341-d2a655a5b11a',
                'cover_image' => 'https://images.unsplash.com/photo-1557777586-f6682739fcf3',
            ],
            [
                'name' => 'Ryan Taylor',
                'email' => 'ryan.taylor@example.com',
                'password' => bcrypt('password'),
                'title' => '3D Artist & Animator',
                'bio' => 'Creating stunning 3D digital artwork and animations.',
                'location' => 'Palestine',
                'verified' => true,
                'followers_count' => 16800,
                'following_count' => 512,
                'projects_count' => 112,
                'avatar' => 'https://images.unsplash.com/photo-1532620161677-a1ca7d5d530f',
                'cover_image' => 'https://images.unsplash.com/photo-1661246627162-feb0269e0c07',
            ],
            [
                'name' => 'Nina Martinez',
                'email' => 'nina.martinez@example.com',
                'password' => bcrypt('password'),
                'title' => 'Portrait Photographer',
                'bio' => 'Capturing authentic moments and creative portraits.',
                'location' => 'Palestine',
                'verified' => false,
                'followers_count' => 13500,
                'following_count' => 387,
                'projects_count' => 145,
                'avatar' => 'https://images.unsplash.com/photo-1475118258341-d2a655a5b11a',
                'cover_image' => 'https://images.unsplash.com/photo-1586734073732-fd664fbd85c8',
            ],
        ];

        foreach ($users as $user) {
            \App\Models\User::create($user);
        }
    }
}
