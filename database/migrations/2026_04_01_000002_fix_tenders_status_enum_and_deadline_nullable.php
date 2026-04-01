<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix two issues in the tenders table:
     * 1. Status enum uses 'Open','Closing Soon','Closed' but all code uses 'open','closing_soon','closed'
     * 2. Deadline is NOT NULL but webhook tenders may not have a deadline
     */
    public function up(): void
    {
        // Step 1: Change enum values and make deadline nullable
        DB::statement("ALTER TABLE `tenders` MODIFY `status` enum('open','closing_soon','closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'open'");
        DB::statement("ALTER TABLE `tenders` MODIFY `deadline` date NULL DEFAULT NULL");

        // Step 2: Migrate existing data to new enum values
        DB::statement("UPDATE `tenders` SET `status` = 'open' WHERE `status` = 'Open'");
        DB::statement("UPDATE `tenders` SET `status` = 'closing_soon' WHERE `status` = 'Closing Soon'");
        DB::statement("UPDATE `tenders` SET `status` = 'closed' WHERE `status` = 'Closed'");
    }

    public function down(): void
    {
        DB::statement("UPDATE `tenders` SET `status` = 'Open' WHERE `status` = 'open'");
        DB::statement("UPDATE `tenders` SET `status` = 'Closing Soon' WHERE `status` = 'closing_soon'");
        DB::statement("UPDATE `tenders` SET `status` = 'Closed' WHERE `status` = 'closed'");
        DB::statement("ALTER TABLE `tenders` MODIFY `status` enum('Open','Closing Soon','Closed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Open'");
        DB::statement("ALTER TABLE `tenders` MODIFY `deadline` date NOT NULL");
    }
};
