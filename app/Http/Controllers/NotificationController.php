<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\CacheService;
use Illuminate\Http\Request;

/**
 * Manages in-app notifications for designers: listing, unread count, mark-as-read, and a static creation helper.
 * The static createNotification() method is called by other controllers to create notifications without triggering duplicate entries within 5 minutes.
 */
class NotificationController extends Controller
{
    /**
     * Return the 10 most recent notifications for the authenticated designer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notifications = Notification::where('designer_id', $designer->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'read' => $notification->read,
                    'data' => $notification->data,
                    'time_ago' => $this->timeAgo($notification->created_at),
                    'created_at' => $notification->created_at->toISOString(),
                ];
            });

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    /**
     * Return the cached count of unread notifications for the authenticated designer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unreadCount(Request $request)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'count' => 0], 401);
        }

        $count = CacheService::getUnreadNotificationCount($designer->id);

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Mark a single notification as read and clear the cached unread count.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $locale
     * @param  int     $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead(Request $request, $locale, $id)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notification = Notification::where('id', $id)
            ->where('designer_id', $designer->id)
            ->first();

        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->update(['read' => true]);

        // Clear cached unread count
        CacheService::clearUnreadNotificationCount($designer->id);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all unread notifications as read for the authenticated designer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAllAsRead(Request $request)
    {
        $designer = auth('designer')->user();

        if (!$designer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        Notification::where('designer_id', $designer->id)
            ->where('read', false)
            ->update(['read' => true]);

        // Clear cached unread count
        CacheService::clearUnreadNotificationCount($designer->id);

        return response()->json(['success' => true]);
    }

    /**
     * Create a notification, skipping duplicates of the same type within the last 5 minutes.
     *
     * @param  int         $designerId   Recipient designer ID
     * @param  string      $type         Notification type identifier (e.g. 'profile_view')
     * @param  string      $title        Short notification title
     * @param  string      $message      Notification body text
     * @param  array|null  $data         Optional structured data (e.g. URL, related IDs)
     * @return \App\Models\Notification
     */
    public static function createNotification($designerId, $type, $title, $message, $data = null)
    {
        // Don't create duplicate notifications within 5 minutes
        $recent = Notification::where('designer_id', $designerId)
            ->where('type', $type)
            ->where('created_at', '>', now()->subMinutes(5))
            ->first();

        if ($recent) {
            return $recent;
        }

        return Notification::create([
            'designer_id' => $designerId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'read' => false,
        ]);
    }

    /**
     * Helper to format time ago
     */
    private function timeAgo($datetime)
    {
        $now = now();
        $diff = $now->diff($datetime);

        if ($diff->y > 0) {
            return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
        }
        if ($diff->m > 0) {
            return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
        }
        if ($diff->d > 0) {
            return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return 'Just now';
    }
}
