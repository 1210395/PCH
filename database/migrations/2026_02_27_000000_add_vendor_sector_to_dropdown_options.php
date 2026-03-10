<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if vendor sector already exists
        $exists = DB::table('dropdown_options')
            ->where('type', 'sector')
            ->where('value', 'vendor')
            ->exists();

        if ($exists) {
            return;
        }

        // Get the max sort_order for sectors to place vendor before guest
        $guestOrder = DB::table('dropdown_options')
            ->where('type', 'sector')
            ->where('value', 'guest')
            ->value('sort_order');

        $showroomOrder = DB::table('dropdown_options')
            ->where('type', 'sector')
            ->where('value', 'showroom')
            ->value('sort_order');

        // Place vendor after showroom, before guest
        $vendorOrder = $showroomOrder ? $showroomOrder + 1 : 5;

        // If guest exists, bump its sort_order up to make room
        if ($guestOrder !== null && $guestOrder <= $vendorOrder) {
            DB::table('dropdown_options')
                ->where('type', 'sector')
                ->where('value', 'guest')
                ->update(['sort_order' => $vendorOrder + 1]);
        }

        // Insert vendor sector
        $vendorId = DB::table('dropdown_options')->insertGetId([
            'type' => 'sector',
            'value' => 'vendor',
            'label' => 'Vendor/Supplier',
            'label_ar' => 'مورّد',
            'parent_id' => null,
            'sort_order' => $vendorOrder,
            'is_active' => true,
            'is_system' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert vendor subsectors
        $subsectors = [
            ['label' => 'Electrical Supplier', 'label_ar' => 'مورّد كهربائي'],
            ['label' => 'Wood Supplier', 'label_ar' => 'مورّد أخشاب'],
            ['label' => 'Mechanical Supplier', 'label_ar' => 'مورّد ميكانيكي'],
            ['label' => 'Lightning Supplier', 'label_ar' => 'مورّد إضاءة'],
            ['label' => 'General Supplier', 'label_ar' => 'مورّد عام'],
        ];

        foreach ($subsectors as $index => $subsector) {
            $value = strtolower(str_replace(' ', '_', $subsector['label']));
            $subExists = DB::table('dropdown_options')
                ->where('type', 'subsector')
                ->where('value', $value)
                ->exists();

            if (!$subExists) {
                DB::table('dropdown_options')->insert([
                    'type' => 'subsector',
                    'value' => $value,
                    'label' => $subsector['label'],
                    'label_ar' => $subsector['label_ar'],
                    'parent_id' => $vendorId,
                    'sort_order' => $index,
                    'is_active' => true,
                    'is_system' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Get vendor sector ID
        $vendorId = DB::table('dropdown_options')
            ->where('type', 'sector')
            ->where('value', 'vendor')
            ->value('id');

        if ($vendorId) {
            // Delete subsectors
            DB::table('dropdown_options')
                ->where('type', 'subsector')
                ->where('parent_id', $vendorId)
                ->delete();

            // Delete vendor sector
            DB::table('dropdown_options')
                ->where('id', $vendorId)
                ->delete();
        }
    }
};
