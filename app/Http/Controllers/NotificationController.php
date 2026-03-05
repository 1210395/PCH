<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\CacheService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get notifications for the current user (last 10)
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
     * Get unread notification count
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
     * Mark a notification as read
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
     * Mark all notifications as read
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
     * Create a notification (internal helper - can be called from other controllers)
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
