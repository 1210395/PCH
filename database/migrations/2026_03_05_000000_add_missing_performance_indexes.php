<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Conversation lookup indexes for messaging system
        if (Schema::hasTable('conversations')) {
            Schema::table('conversations', function (Blueprint $table) {
                if (!$this->hasIndex('conversations', 'conversations_designer_1_id_index')) {
                    $table->index('designer_1_id');
                }
                if (!$this->hasIndex('conversations', 'conversations_designer_2_id_index')) {
                    $table->index('designer_2_id');
                }
                if (!$this->hasIndex('conversations', 'conversations_last_message_at_index')) {
                    $table->index('last_message_at');
                }
            });
        }

        // Designer admin/active filtering
        if (Schema::hasTable('designers')) {
            Schema::table('designers', function (Blueprint $table) {
                if (!$this->hasIndex('designers', 'designers_is_admin_index')) {
                    $table->index('is_admin');
                }
                if (!$this->hasIndex('designers', 'designers_is_active_is_admin_index')) {
                    $table->index(['is_active', 'is_admin']);
                }
            });
        }

        // Academic content approval status indexes
        if (Schema::hasTable('academic_trainings')) {
            Schema::table('academic_trainings', function (Blueprint $table) {
                if (!$this->hasIndex('academic_trainings', 'academic_trainings_approval_status_index')) {
                    $table->index('approval_status');
                }
            });
        }

        if (Schema::hasTable('academic_workshops')) {
            Schema::table('academic_workshops', function (Blueprint $table) {
                if (!$this->hasIndex('academic_workshops', 'academic_workshops_approval_status_index')) {
                    $table->index('approval_status');
                }
            });
        }

        if (Schema::hasTable('academic_announcements')) {
            Schema::table('academic_announcements', function (Blueprint $table) {
                if (!$this->hasIndex('academic_announcements', 'academic_announcements_approval_status_index')) {
                    $table->index('approval_status');
                }
            });
        }

        // Message requests lookup
        if (Schema::hasTable('message_requests')) {
            Schema::table('message_requests', function (Blueprint $table) {
                if (!$this->hasIndex('message_requests', 'message_requests_to_designer_id_status_index')) {
                    $table->index(['to_designer_id', 'status']);
                }
                if (!$this->hasIndex('message_requests', 'message_requests_from_designer_id_status_index')) {
                    $table->index(['from_designer_id', 'status']);
                }
            });
        }
    }

    public function down(): void
    {
        $indexes = [
            'conversations' => ['conversations_designer_1_id_index', 'conversations_designer_2_id_index', 'conversations_last_message_at_index'],
            'designers' => ['designers_is_admin_index', 'designers_is_active_is_admin_index'],
            'academic_trainings' => ['academic_trainings_approval_status_index'],
            'academic_workshops' => ['academic_workshops_approval_status_index'],
            'academic_announcements' => ['academic_announcements_approval_status_index'],
            'message_requests' => ['message_requests_to_designer_id_status_index', 'message_requests_from_designer_id_status_index'],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) use ($tableIndexes) {
                    foreach ($tableIndexes as $index) {
                        if ($this->hasIndex($table->getTable(), $index)) {
                            $table->dropIndex($index);
                        }
                    }
                });
            }
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn($idx) => $idx['name'] === $index);
    }
};
