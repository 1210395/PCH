<?php

namespace Database\Seeders;

use App\Models\MarketplacePost;
use Illuminate\Database\Seeder;

class MarketplacePostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = [
            [
                'user_id' => 1,
                'title' => 'Looking for UI/UX Designer for SaaS Project',
                'description' => 'We are building a new SaaS product and need an experienced UI/UX designer to help create the user interface. The project involves designing dashboards, user flows, and creating a design system.',
                'category' => 'Design',
                'image' => 'https://images.unsplash.com/photo-1581291518633-83b4ebd1d83e?w=400',
                'type' => 'collaboration',
                'tags' => ['UI Design', 'UX Design', 'SaaS', 'Remote'],
                'likes_count' => 45,
                'comments_count' => 12,
                'views_count' => 234,
                'bookmarks_count' => 23,
            ],
            [
                'user_id' => 2,
                'title' => 'Professional Logo Design Services',
                'description' => 'Offering professional logo design services for startups and small businesses. I specialize in minimalist and modern designs that stand out. Quick turnaround and unlimited revisions.',
                'category' => 'Branding',
                'image' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?w=400',
                'type' => 'service',
                'tags' => ['Logo Design', 'Branding', 'Graphic Design', 'Professional'],
                'likes_count' => 78,
                'comments_count' => 34,
                'views_count' => 567,
                'bookmarks_count' => 45,
            ],
            [
                'user_id' => 3,
                'title' => 'Showcasing My Latest 3D Product Renders',
                'description' => 'Just finished a series of 3D product renders for a tech startup. These renders showcase their new smart home devices with realistic lighting and materials.',
                'category' => '3D Design',
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400',
                'type' => 'showcase',
                'tags' => ['3D Rendering', 'Product Design', 'Visualization', 'Tech'],
                'likes_count' => 156,
                'comments_count' => 28,
                'views_count' => 892,
                'bookmarks_count' => 67,
            ],
            [
                'user_id' => 4,
                'title' => 'Internship Opportunity at Design Studio',
                'description' => 'Our design studio is looking for passionate design interns to join our team. Great opportunity to learn from experienced designers and work on real client projects.',
                'category' => 'Career',
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=400',
                'type' => 'opportunity',
                'tags' => ['Internship', 'Design Studio', 'Career', 'Learning'],
                'likes_count' => 234,
                'comments_count' => 56,
                'views_count' => 1234,
                'bookmarks_count' => 189,
            ],
            [
                'user_id' => 1,
                'title' => 'Web Development & Design Partnership',
                'description' => 'Full-stack developer looking to partner with designers for freelance projects. Let\'s combine our skills to deliver complete solutions to clients.',
                'category' => 'Development',
                'image' => 'https://images.unsplash.com/photo-1517180102446-f3ece451e9d8?w=400',
                'type' => 'collaboration',
                'tags' => ['Web Development', 'Partnership', 'Freelance', 'Full Stack'],
                'likes_count' => 67,
                'comments_count' => 19,
                'views_count' => 445,
                'bookmarks_count' => 34,
            ],
            [
                'user_id' => 5,
                'title' => 'Motion Graphics & Animation Services',
                'description' => 'Specializing in motion graphics, explainer videos, and UI animations. I can bring your designs to life with smooth and engaging animations.',
                'category' => 'Animation',
                'image' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400',
                'type' => 'service',
                'tags' => ['Motion Graphics', 'Animation', 'Video', 'UI Animation'],
                'likes_count' => 89,
                'comments_count' => 22,
                'views_count' => 634,
                'bookmarks_count' => 51,
            ],
        ];

        foreach ($posts as $post) {
            MarketplacePost::create($post);
        }
    }
}
