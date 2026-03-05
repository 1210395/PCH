<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('designers', function (Blueprint $table) {
            $table->json('certifications')->nullable()->after('years_of_experience');
        });
    }

    public function down(): void
    {
        Schema::table('designers', function (Blueprint $table) {
            $table->dropColumn('certifications');
        });
    }
};
