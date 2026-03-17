<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->id();
            $table->string('page_key', 60);          // e.g. 'home', 'projects', 'designer_profile'
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('designer_id')->nullable();
            $table->timestamps();

            $table->index(['page_key', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};
