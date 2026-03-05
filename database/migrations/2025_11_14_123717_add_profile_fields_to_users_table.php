<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('cover_image')->nullable()->after('avatar');
            $table->string('title')->nullable()->after('name');
            $table->text('bio')->nullable()->after('cover_image');
            $table->string('location')->nullable()->after('bio');
            $table->string('website')->nullable()->after('location');
            $table->boolean('verified')->default(false)->after('website');
            $table->integer('followers_count')->default(0)->after('verified');
            $table->integer('following_count')->default(0)->after('followers_count');
            $table->integer('projects_count')->default(0)->after('following_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'cover_image',
                'title',
                'bio',
                'location',
                'website',
                'verified',
                'followers_count',
                'following_count',
                'projects_count'
            ]);
        });
    }
};
