<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FollowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create some follow relationships
        $follows = [
            ['follower_id' => 1, 'following_id' => 2],
            ['follower_id' => 1, 'following_id' => 3],
            ['follower_id' => 2, 'following_id' => 1],
            ['follower_id' => 2, 'following_id' => 3],
            ['follower_id' => 3, 'following_id' => 1],
            ['follower_id' => 3, 'following_id' => 4],
            ['follower_id' => 4, 'following_id' => 1],
            ['follower_id' => 4, 'following_id' => 2],
        ];

        foreach ($follows as $follow) {
            DB::table('follows')->insert($follow);
        }
    }
}
