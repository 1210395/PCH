<?php

namespace Database\Seeders;

use App\Models\FabLab;
use Illuminate\Database\Seeder;

class FabLabSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fabLabs = [
            [
                'name' => 'Birzeit University FabLab',
                'location' => 'Birzeit University Campus, Birzeit',
                'city' => 'Birzeit',
                'description' => 'A state-of-the-art fabrication laboratory equipped with the latest digital fabrication tools. We provide students and community members access to 3D printers, laser cutters, CNC machines, and more. Our mission is to democratize access to manufacturing tools and foster innovation.',
                'short_description' => 'Leading university fab lab with comprehensive digital fabrication facilities',
                'image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=400',
                'cover_image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=1200',
                'rating' => 4.8,
                'reviews_count' => 124,
                'members' => 450,
                'equipment' => ['3D Printers', 'Laser Cutters', 'CNC Mills', 'Electronics Workbench', 'Vinyl Cutter'],
                'services' => ['Prototyping', 'Training Workshops', 'Equipment Rental', 'Design Consultation'],
                'features' => ['Free for Students', 'Expert Staff', 'Open 7 Days', 'Project Storage'],
                'opening_hours' => 'Mon-Fri: 8AM-8PM, Sat-Sun: 10AM-6PM',
                'type' => 'university',
                'verified' => true,
                'phone' => '+970 2 298 2000',
                'email' => 'fablab@birzeit.edu',
                'website' => 'https://fablab.birzeit.edu',
            ],
            [
                'name' => 'Ramallah Innovation Hub',
                'location' => 'Al-Masyoun, Ramallah',
                'city' => 'Ramallah',
                'description' => 'Community-driven makerspace focused on fostering entrepreneurship and innovation. We offer co-working spaces, fabrication tools, and regular workshops on various topics including electronics, 3D printing, and product design.',
                'short_description' => 'Community makerspace promoting innovation and entrepreneurship',
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400',
                'cover_image' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=1200',
                'rating' => 4.6,
                'reviews_count' => 89,
                'members' => 320,
                'equipment' => ['3D Printers', 'Woodworking Tools', 'Sewing Machines', 'Electronics Lab'],
                'services' => ['Co-working Space', 'Workshops', 'Mentorship', 'Networking Events'],
                'features' => ['Monthly Membership', 'Startup Friendly', 'Community Events', 'Coffee Shop'],
                'opening_hours' => 'Mon-Sat: 9AM-9PM',
                'type' => 'community',
                'verified' => true,
                'phone' => '+970 2 295 0000',
                'email' => 'info@ramallahhub.ps',
                'website' => 'https://ramallahhub.ps',
            ],
            [
                'name' => 'TechPark Fab Studio',
                'location' => 'Tech Park Building, Rawabi',
                'city' => 'Rawabi',
                'description' => 'Professional fabrication studio offering industrial-grade equipment and services. We cater to businesses needing rapid prototyping, small batch production, and product development services.',
                'short_description' => 'Professional fabrication studio with industrial-grade equipment',
                'image' => 'https://images.unsplash.com/photo-1565043589221-1a6fd9ae45c7?w=400',
                'cover_image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=1200',
                'rating' => 4.9,
                'reviews_count' => 67,
                'members' => 180,
                'equipment' => ['Industrial 3D Printers', 'Metal CNC', 'Injection Molding', 'PCB Manufacturing'],
                'services' => ['Rapid Prototyping', 'Small Batch Production', 'Design Services', 'Material Testing'],
                'features' => ['Industrial Grade', 'Fast Turnaround', 'Quality Assurance', 'B2B Services'],
                'opening_hours' => 'Mon-Fri: 8AM-6PM',
                'type' => 'private',
                'verified' => true,
                'phone' => '+970 2 289 0000',
                'email' => 'contact@techparkfab.ps',
                'website' => 'https://techparkfab.ps',
            ],
            [
                'name' => 'Nablus Makers Space',
                'location' => 'An-Najah University Area, Nablus',
                'city' => 'Nablus',
                'description' => 'Government-supported makerspace aimed at providing youth with access to modern fabrication tools. We focus on education, skills development, and supporting local entrepreneurs.',
                'short_description' => 'Government-backed facility supporting youth innovation',
                'image' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?w=400',
                'cover_image' => 'https://images.unsplash.com/photo-1581092918056-0c4c3acd3789?w=1200',
                'rating' => 4.5,
                'reviews_count' => 156,
                'members' => 280,
                'equipment' => ['3D Printers', 'Laser Cutters', 'Basic Electronics', 'Hand Tools'],
                'services' => ['Free Training', 'Youth Programs', 'Competition Support', 'Startup Incubation'],
                'features' => ['Government Funded', 'Youth Focus', 'Free Programs', 'Career Support'],
                'opening_hours' => 'Sun-Thu: 9AM-7PM',
                'type' => 'government',
                'verified' => true,
                'phone' => '+970 9 234 0000',
                'email' => 'makers@nablus.gov.ps',
                'website' => 'https://makersspace.nablus.gov.ps',
            ],
        ];

        foreach ($fabLabs as $fabLab) {
            FabLab::create($fabLab);
        }
    }
}
