<?php

namespace App\Console\Commands;

use App\Http\Controllers\NotificationController;
use App\Models\Conversation;
use Illuminate\Console\Command;

/**
 * Sends in-app rating reminder notifications to participants of conversations
 * that were accepted 24+ hours ago but have not yet been rated.
 *
 * Intended to be scheduled hourly via the Laravel scheduler.
 */
class SendRatingReminders extends Command
{
    protected $signature = 'conversations:send-rating-reminders';
    protected $description = 'Send rating reminder notifications for conversations accepted 24+ hours ago';

    public function handle()
    {
        // Find conversations accepted more than 24 hours ago
        $conversations = Conversation::whereNotNull('accepted_at')
            ->where('accepted_at', '<=', now()->subHours(24))
            ->get();

        $sent = 0;

        foreach ($conversations as $conversation) {
            foreach ([$conversation->designer_1_id, $conversation->designer_2_id] as $designerId) {
                if ($conversation->shouldSendRatingReminder($designerId)) {
                    NotificationController::createNotification(
                        $designerId,
                        'rating_reminder',
                        __('How was your conversation?'),
                        __('You have a conversation that hasn\'t been rated yet. Share your feedback to help improve the community!'),
                        ['conversation_id' => $conversation->id]
                    );
                    $sent++;
                }
            }
        }

        $this->info("Sent {$sent} rating reminder notification(s).");

        return Command::SUCCESS;
    }
}
