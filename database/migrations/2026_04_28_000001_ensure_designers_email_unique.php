<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * H-10: ensure `designers.email` has a UNIQUE index.
 *
 * The original `users` table (since dropped) declared the uniqueness;
 * no migration ever declared it on `designers`. On any prod DB where
 * the unique key was never propagated, two designers can register
 * the same email.
 *
 * Pre-flight checks before adding the constraint:
 *   1) bail out if the unique index already exists (idempotent)
 *   2) bail out + log if duplicate emails exist (would fail anyway,
 *      and we must not silently lose rows)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('designers') || !Schema::hasColumn('designers', 'email')) {
            return;
        }

        // Idempotent: skip if a unique index already covers `email`.
        $existing = collect(DB::select("SHOW INDEXES FROM `designers`"))
            ->where('Column_name', 'email')
            ->where('Non_unique', 0);
        if ($existing->isNotEmpty()) {
            return;
        }

        // Pre-flight duplicate check. If any duplicates exist, do NOT
        // try to ALTER (it would fail with a generic error message
        // and leave operators guessing). Log loudly and exit cleanly
        // so the operator can dedupe before re-running.
        $dupes = DB::select("
            SELECT email, COUNT(*) AS n
            FROM `designers`
            WHERE email IS NOT NULL AND email != ''
            GROUP BY email
            HAVING n > 1
            LIMIT 10
        ");
        if (!empty($dupes)) {
            $msg = "ensure_designers_email_unique: skipped — found duplicate emails:\n";
            foreach ($dupes as $d) {
                $msg .= "  {$d->email} ({$d->n} rows)\n";
            }
            $msg .= "Dedupe these rows manually, then re-run `php artisan migrate`.";
            \Log::warning($msg);
            // Surface to the operator running migrate.
            if (function_exists('fwrite')) {
                fwrite(STDERR, $msg . "\n");
            }
            return;
        }

        Schema::table('designers', function ($table) {
            $table->unique('email', 'designers_email_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('designers')) {
            return;
        }
        $exists = collect(DB::select("SHOW INDEXES FROM `designers`"))
            ->contains('Key_name', 'designers_email_unique');
        if ($exists) {
            Schema::table('designers', function ($table) {
                $table->dropUnique('designers_email_unique');
            });
        }
    }
};
