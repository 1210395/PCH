<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'user_id' => 1,
                'title' => 'Complete UI Kit Pro',
                'description' => 'A comprehensive UI kit with over 500 components, perfect for building modern web applications. Includes buttons, forms, cards, navigation, and more.',
                'price' => 79.00,
                'category' => 'UI Kits',
                'image' => 'https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=400',
                'features' => ['500+ Components', 'Figma & Sketch Files', 'Free Updates', 'Premium Support'],
                'rating' => 4.9,
                'reviews_count' => 234,
                'downloads' => 1523,
                'likes_count' => 892,
                'featured' => true,
            ],
            [
                'user_id' => 2,
                'title' => 'E-commerce Dashboard Template',
                'description' => 'Professional dashboard template designed specifically for e-commerce businesses. Track sales, inventory, and customer data with beautiful charts.',
                'price' => 49.00,
                'category' => 'Templates',
                'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=400',
                'features' => ['Analytics Dashboard', 'Sales Reports', 'Inventory Management', 'Customer Insights'],
                'rating' => 4.7,
                'reviews_count' => 156,
                'downloads' => 987,
                'likes_count' => 654,
                'featured' => true,
            ],
            [
                'user_id' => 3,
                'title' => 'Icon Pack - 2000+ Icons',
                'description' => 'Massive collection of hand-crafted icons in multiple formats. Perfect for any design project. Includes SVG, PNG, and icon fonts.',
                'price' => 39.00,
                'category' => 'Icons',
                'image' => 'https://images.unsplash.com/photo-1611532736597-de2d4265fba3?w=400',
                'features' => ['2000+ Icons', 'SVG & PNG Formats', 'Icon Font Included', 'Regular Updates'],
                'rating' => 4.8,
                'reviews_count' => 412,
                'downloads' => 3241,
                'likes_count' => 1567,
                'featured' => true,
            ],
            [
                'user_id' => 1,
                'title' => 'Mobile App UI Kit',
                'description' => 'Beautiful mobile app UI kit with 100+ screens. Designed for iOS and Android with modern design principles.',
                'price' => 89.00,
                'category' => 'UI Kits',
                'image' => 'https://images.unsplash.com/photo-1512941937669-90a1b58e7e9c?w=400',
                'features' => ['100+ Screens', 'iOS & Android', 'Figma Files', 'Auto Layout'],
                'rating' => 4.6,
                'reviews_count' => 89,
                'downloads' => 654,
                'likes_count' => 423,
                'featured' => false,
            ],
            [
                'user_id' => 4,
                'title' => 'Brand Identity Mockups',
                'description' => 'Professional mockup templates for showcasing brand identity designs. Includes business cards, letterheads, and packaging.',
                'price' => 29.00,
                'category' => 'Mockups',
                'image' => 'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=400',
                'features' => ['50+ Mockups', 'PSD Files', 'Smart Objects', 'High Resolution'],
                'rating' => 4.5,
                'reviews_count' => 67,
                'downloads' => 432,
                'likes_count' => 287,
                'featured' => false,
            ],
            [
                'user_id' => 2,
                'title' => 'Website Wireframe Kit',
                'description' => 'Complete wireframing kit for website projects. Speed up your design process with pre-built components and layouts.',
                'price' => 35.00,
                'category' => 'Templates',
                'image' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?w=400',
                'features' => ['200+ Components', 'Multiple Layouts', 'Easy Customization', 'Documentation'],
                'rating' => 4.4,
                'reviews_count' => 45,
                'downloads' => 321,
                'likes_count' => 198,
                'featured' => false,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
