<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SkillSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class,
            UserSkillSeeder::class,
            FollowSeeder::class,
            ProductSeeder::class,
            MarketplacePostSeeder::class,
            FabLabSeeder::class,
        ]);
    }
}
