<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds missing columns to academic_workshops table that are referenced
 * in the create/edit form and show views but were not in the original schema.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_workshops', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_workshops', 'objectives')) {
                $table->text('objectives')->nullable()->after('description');
            }
            if (!Schema::hasColumn('academic_workshops', 'is_online')) {
                $table->boolean('is_online')->default(false)->after('location');
            }
            if (!Schema::hasColumn('academic_workshops', 'instructor')) {
                $table->string('instructor', 255)->nullable()->after('is_online');
            }
            if (!Schema::hasColumn('academic_workshops', 'is_free')) {
                $table->boolean('is_free')->default(true)->after('price');
            }
            if (!Schema::hasColumn('academic_workshops', 'registration_link')) {
                $table->string('registration_link', 500)->nullable()->after('has_certificate');
            }
        });
    }

    public function down(): void
    {
        Schema::table('academic_workshops', function (Blueprint $table) {
            $columns = ['objectives', 'is_online', 'instructor', 'is_free', 'registration_link'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('academic_workshops', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
