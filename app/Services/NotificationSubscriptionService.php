<?php

namespace App\Services;

use App\Models\ProfileSubscription;
use App\Models\CategorySubscription;
use App\Models\Notification;
use App\Models\AcademicNotification;
use App\Models\Designer;
use App\Models\AcademicAccount;
use App\Http\Controllers\NotificationController;

/**
 * Dispatches in-app notifications to profile and category subscribers
 * when content is approved on the Palestine Creative Hub.
 *
 * All methods are static. Notifications are bulk-inserted in chunks of 100
 * to stay within MySQL packet size limits. Supports both designer and
 * academic account recipients via separate Notification models.
 */
class NotificationSubscriptionService
{
    /**
     * Notify profile subscribers when content is created/approved
     *
     * @param string $creatorType 'designer' or 'academic'
     * @param int $creatorId The ID of the content creator
     * @param string $contentType 'product', 'project', 'service', 'marketplace', 'training', 'workshop', 'announcement'
     * @param string $contentTitle Title of the content
     * @param int $contentId ID of the content
     * @param string|null $contentUrl Optional URL to the content
     */
    public static function notifyProfileSubscribers(
        string $creatorType,
        int $creatorId,
        string $contentType,
        string $contentTitle,
        int $contentId,
        ?string $contentUrl = null
    ): void {
        $subscriptions = ProfileSubscription::where('subscribable_type', $creatorType)
            ->where('subscribable_id', $creatorId)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        // Get creator name for notification message
        $creatorName = self::getCreatorName($creatorType, $creatorId);

        $title = self::getProfileNotificationTitle($contentType);
        $message = self::getProfileNotificationMessage($contentType, $contentTitle, $creatorName);

        $data = [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'creator_type' => $creatorType,
            'creator_id' => $creatorId,
            'url' => $contentUrl,
        ];

        // Batch notifications by recipient type
        $designerNotifications = [];
        $academicNotifications = [];

        foreach ($subscriptions as $subscription) {
            $notificationData = [
                'type' => 'profile_subscription_' . $contentType,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($subscription->subscriber_type === 'designer') {
                $notificationData['designer_id'] = $subscription->subscriber_id;
                $designerNotifications[] = $notificationData;
            } else {
                $notificationData['academic_account_id'] = $subscription->subscriber_id;
                $academicNotifications[] = $notificationData;
            }
        }

        // Bulk insert in chunks of 100
        if (!empty($designerNotifications)) {
            foreach (array_chunk($designerNotifications, 100) as $chunk) {
                Notification::insert($chunk);
            }
        }
        if (!empty($academicNotifications)) {
            foreach (array_chunk($academicNotifications, 100) as $chunk) {
                AcademicNotification::insert($chunk);
            }
        }
    }

    /**
     * Notify category subscribers when content is created/approved
     *
     * @param string $contentType 'marketplace', 'product', 'project', 'service', 'training'
     * @param string|null $category The content's category
     * @param string $contentTitle Title of the content
     * @param int $contentId ID of the content
     * @param array $tags Tags for marketplace posts
     * @param string|null $type Type for marketplace posts
     * @param string|null $level Level for training
     * @param int|null $excludeCreatorId Designer ID to exclude from notifications
     * @param string|null $contentUrl Optional URL to the content
     */
    public static function notifyCategorySubscribers(
        string $contentType,
        ?string $category,
        string $contentTitle,
        int $contentId,
        array $tags = [],
        ?string $type = null,
        ?string $level = null,
        ?int $excludeCreatorId = null,
        ?string $contentUrl = null
    ): void {
        $matchingSubscriptions = CategorySubscription::getMatchingSubscriptions(
            $contentType,
            $category,
            $tags,
            $type,
            $level,
            $excludeCreatorId
        );

        if ($matchingSubscriptions->isEmpty()) {
            return;
        }

        $title = self::getCategoryNotificationTitle($contentType);
        $message = self::getCategoryNotificationMessage($contentType, $contentTitle, $category);

        $data = [
            'content_type' => $contentType,
            'content_id' => $contentId,
            'category' => $category,
            'url' => $contentUrl,
        ];

        // Batch notifications by recipient type
        $designerNotifications = [];
        $academicNotifications = [];

        foreach ($matchingSubscriptions as $subscription) {
            $notificationData = [
                'type' => 'category_subscription_' . $contentType,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($subscription->subscriber_type === 'designer') {
                $notificationData['designer_id'] = $subscription->subscriber_id;
                $designerNotifications[] = $notificationData;
            } else {
                $notificationData['academic_account_id'] = $subscription->subscriber_id;
                $academicNotifications[] = $notificationData;
            }
        }

        // Bulk insert in chunks of 100
        if (!empty($designerNotifications)) {
            foreach (array_chunk($designerNotifications, 100) as $chunk) {
                Notification::insert($chunk);
            }
        }
        if (!empty($academicNotifications)) {
            foreach (array_chunk($academicNotifications, 100) as $chunk) {
                AcademicNotification::insert($chunk);
            }
        }
    }

    /**
     * Convenience wrapper: notifies both profile and category subscribers
     * when a content item is approved.
     *
     * Accepts any approvable model instance and extracts the creator type,
     * creator ID, content type, category, tags, and level automatically.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $content  Product, Project, Service, MarketplacePost, AcademicTraining, AcademicWorkshop, or AcademicAnnouncement
     * @return void
     */
    public static function notifyOnContentApproved($content): void
    {
        // Determine content type from model class
        $modelClass = class_basename(get_class($content));
        $contentTypeMap = [
            'Product' => 'product',
            'Project' => 'project',
            'Service' => 'service',
            'MarketplacePost' => 'marketplace',
            'AcademicTraining' => 'training',
            'AcademicWorkshop' => 'workshop',
            'AcademicAnnouncement' => 'announcement',
        ];

        $contentType = $contentTypeMap[$modelClass] ?? strtolower($modelClass);

        // Extract creator info
        $creatorType = isset($content->designer_id) ? 'designer' : 'academic';
        $creatorId = $content->designer_id ?? $content->academic_account_id ?? null;

        if (!$creatorId) {
            \Log::warning('Cannot notify subscribers: no creator ID found', [
                'model' => $modelClass,
                'id' => $content->id
            ]);
            return;
        }

        // Extract content info
        $contentTitle = $content->title ?? $content->name ?? 'Untitled';
        $contentId = $content->id;
        $category = $content->category ?? null;

        // For marketplace: extract tags and type
        $tags = [];
        $type = null;
        if ($contentType === 'marketplace') {
            $tags = is_array($content->tags) ? $content->tags : [];
            $type = $content->type ?? null;
        }

        // For training: extract level
        $level = null;
        if ($contentType === 'training') {
            $level = $content->level ?? null;
        }

        // Notify profile subscribers
        self::notifyProfileSubscribers(
            $creatorType,
            $creatorId,
            $contentType,
            $contentTitle,
            $contentId,
            null // contentUrl
        );

        // Notify category subscribers (exclude the creator)
        $excludeCreatorId = $creatorType === 'designer' ? $creatorId : null;

        self::notifyCategorySubscribers(
            $contentType,
            $category,
            $contentTitle,
            $contentId,
            $tags,
            $type,
            $level,
            $excludeCreatorId,
            null // contentUrl
        );
    }

    /**
     * Create notification using appropriate model
     */
    private static function createNotification(
        string $recipientType,
        int $recipientId,
        string $type,
        string $title,
        string $message,
        array $data
    ): void {
        if ($recipientType === 'designer') {
            // Use existing NotificationController helper for designers
            NotificationController::createNotification($recipientId, $type, $title, $message, $data);
        } else {
            // Create academic notification
            // Check for recent duplicate first
            $recent = AcademicNotification::where('academic_account_id', $recipientId)
                ->where('type', $type)
                ->where('created_at', '>', now()->subMinutes(5))
                ->first();

            if (!$recent) {
                AcademicNotification::create([
                    'academic_account_id' => $recipientId,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'data' => $data,
                    'read' => false,
                ]);
            }
        }
    }

    /**
     * Get creator name for notification message
     */
    private static function getCreatorName(string $creatorType, int $creatorId): string
    {
        if ($creatorType === 'designer') {
            $designer = Designer::find($creatorId);
            return $designer ? $designer->name : 'A designer';
        } else {
            $academic = AcademicAccount::find($creatorId);
            return $academic ? $academic->name : 'An institution';
        }
    }

    /**
     * Get notification title for profile subscription
     */
    private static function getProfileNotificationTitle(string $contentType): string
    {
        return match ($contentType) {
            'product' => 'New Product from Profile You Follow',
            'project' => 'New Project from Profile You Follow',
            'service' => 'New Service from Profile You Follow',
            'marketplace' => 'New Post from Profile You Follow',
            'training' => 'New Training Available',
            'workshop' => 'New Workshop Available',
            'announcement' => 'New Announcement',
            default => 'New Content from Profile You Follow',
        };
    }

    /**
     * Get notification message for profile subscription
     */
    private static function getProfileNotificationMessage(string $contentType, string $title, string $creatorName): string
    {
        $truncatedTitle = mb_substr($title, 0, 50);
        if (mb_strlen($title) > 50) {
            $truncatedTitle .= '...';
        }

        return match ($contentType) {
            'product' => "{$creatorName} added a new product: \"{$truncatedTitle}\"",
            'project' => "{$creatorName} shared a new project: \"{$truncatedTitle}\"",
            'service' => "{$creatorName} offers a new service: \"{$truncatedTitle}\"",
            'marketplace' => "{$creatorName} posted: \"{$truncatedTitle}\"",
            'training' => "{$creatorName} is offering a new training: \"{$truncatedTitle}\"",
            'workshop' => "{$creatorName} is hosting a workshop: \"{$truncatedTitle}\"",
            'announcement' => "{$creatorName}: \"{$truncatedTitle}\"",
            default => "{$creatorName} shared: \"{$truncatedTitle}\"",
        };
    }

    /**
     * Get notification title for category subscription
     */
    private static function getCategoryNotificationTitle(string $contentType): string
    {
        return match ($contentType) {
            'product' => 'New Product in Your Category',
            'project' => 'New Project in Your Category',
            'service' => 'New Service in Your Category',
            'marketplace' => 'New Marketplace Post',
            'training' => 'New Training in Your Category',
            default => 'New Content in Your Category',
        };
    }

    /**
     * Get notification message for category subscription
     */
    private static function getCategoryNotificationMessage(string $contentType, string $title, ?string $category): string
    {
        $truncatedTitle = mb_substr($title, 0, 50);
        if (mb_strlen($title) > 50) {
            $truncatedTitle .= '...';
        }

        $categoryText = $category ? " in {$category}" : '';

        return match ($contentType) {
            'product' => "New product{$categoryText}: \"{$truncatedTitle}\"",
            'project' => "New project{$categoryText}: \"{$truncatedTitle}\"",
            'service' => "New service{$categoryText}: \"{$truncatedTitle}\"",
            'marketplace' => "New post{$categoryText}: \"{$truncatedTitle}\"",
            'training' => "New training{$categoryText}: \"{$truncatedTitle}\"",
            default => "New content{$categoryText}: \"{$truncatedTitle}\"",
        };
    }
}
