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
        Schema::create('fab_labs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->string('city');
            $table->text('description');
            $table->text('short_description');
            $table->string('image');
            $table->string('cover_image');
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->integer('members')->default(0);
            $table->json('equipment')->nullable();
            $table->json('services')->nullable();
            $table->json('features')->nullable();
            $table->string('opening_hours');
            $table->enum('type', ['university', 'community', 'private', 'government']);
            $table->boolean('verified')->default(false);
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fab_labs');
    }
};
