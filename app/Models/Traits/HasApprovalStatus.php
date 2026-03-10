<?php

namespace App\Models\Traits;

use App\Models\Designer;
use App\Models\Notification;
use App\Models\AcademicNotification;
use App\Models\AdminSetting;
use App\Services\NotificationSubscriptionService;

trait HasApprovalStatus
{
    /**
     * Boot the trait - set initial approval status based on admin settings
     */
    public static function bootHasApprovalStatus()
    {
        static::creating(function ($model) {
            // Only set default if approval_status is not already set
            if (empty($model->approval_status)) {
                $model->approval_status = static::getInitialApprovalStatus();
            }
        });
    }

    /**
     * Get the initial approval status based on admin settings
     */
    protected static function getInitialApprovalStatus(): string
    {
        // Determine the type from the model class name
        $className = class_basename(static::class);

        // Map model names to admin setting types
        // Note: Training and Tender are admin-managed and don't use approval workflow
        $typeMap = [
            'Product' => 'products',
            'Project' => 'projects',
            'Service' => 'services',
            'MarketplacePost' => 'marketplace',
        ];

        $settingType = $typeMap[$className] ?? null;

        // Check if auto-accept is enabled for this type
        if ($settingType && AdminSetting::isAutoAcceptEnabled($settingType)) {
            return 'approved';
        }

        return 'pending';
    }

    /**
     * Scope to get only pending items
     */
    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Scope to get only approved items
     */
    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope to get only rejected items
     */
    public function scopeRejected($query)
    {
        return $query->where('approval_status', 'rejected');
    }

    /**
     * Scope to get items visible to a specific designer
     * Shows approved items + designer's own items (any status)
     */
    public function scopeVisibleTo($query, $designerId = null)
    {
        if ($designerId) {
            return $query->where(function ($q) use ($designerId) {
                $q->where('approval_status', 'approved')
                  ->orWhere('designer_id', $designerId);
            });
        }
        return $query->where('approval_status', 'approved');
    }

    /**
     * Scope to get items visible to public (only approved)
     */
    public function scopePublicVisible($query)
    {
        return $query->where('approval_status', 'approved');
    }

    /**
     * Check if item is pending
     */
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    /**
     * Check if item is approved
     */
    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    /**
     * Check if item is rejected
     */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    /**
     * Approve the item
     */
    public function approve($adminId): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $adminId,
            'rejection_reason' => null,
        ]);

        // Trigger subscription notifications
        $this->sendSubscriptionNotifications();
    }

    /**
     * Send notifications to subscribers when content is approved
     */
    protected function sendSubscriptionNotifications(): void
    {
        try {
            NotificationSubscriptionService::notifyOnContentApproved($this);
        } catch (\Exception $e) {
            \Log::error('Failed to send subscription notifications', [
                'model' => get_class($this),
                'id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reject the item with optional reason
     */
    public function reject($adminId, $reason = null): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_at' => null,
            'approved_by' => $adminId,
        ]);

        // Send notification to the owner
        $this->sendRejectionNotification($reason);
    }

    /**
     * Send rejection notification to the item owner
     */
    protected function sendRejectionNotification($reason = null): void
    {
        // Determine the item type from the model class name
        $modelClass = class_basename($this);
        $itemType = strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $modelClass)); // Convert CamelCase to words

        // Get the item title/name for the notification
        $itemName = $this->title ?? $this->name ?? 'Item';

        // Check if this is academic content (uses academic_account_id instead of designer_id)
        if (!empty($this->academic_account_id)) {
            // Send notification to academic account
            AcademicNotification::create([
                'academic_account_id' => $this->academic_account_id,
                'type' => 'content_rejected',
                'title' => ucfirst($itemType) . ' Rejected',
                'message' => $reason
                    ? "Your {$itemType} \"{$itemName}\" has been rejected. Reason: {$reason}"
                    : "Your {$itemType} \"{$itemName}\" has been rejected. Please review and make necessary changes.",
                'read' => false,
                'data' => [
                    'item_type' => $modelClass,
                    'item_id' => $this->id,
                    'item_name' => $itemName,
                    'reason' => $reason,
                ],
            ]);
            return;
        }

        // Only create notification if designer_id exists
        if (empty($this->designer_id)) {
            return;
        }

        // Create the notification for designer-owned content
        Notification::create([
            'designer_id' => $this->designer_id,
            'type' => 'content_rejected',
            'title' => ucfirst($itemType) . ' Rejected',
            'message' => $reason
                ? "Your {$itemType} \"{$itemName}\" has been rejected. Reason: {$reason}"
                : "Your {$itemType} \"{$itemName}\" has been rejected. Please review and make necessary changes.",
            'read' => false,
            'data' => [
                'item_type' => $modelClass,
                'item_id' => $this->id,
                'item_name' => $itemName,
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Reset to pending status
     */
    public function resetToPending(): void
    {
        $this->update([
            'approval_status' => 'pending',
            'rejection_reason' => null,
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Get the admin who approved/rejected this item
     */
    public function approvedByAdmin()
    {
        return $this->belongsTo(Designer::class, 'approved_by');
    }

    /**
     * Get approval status badge color for UI
     */
    public function getApprovalBadgeColorAttribute(): string
    {
        return match($this->approval_status) {
            'approved' => 'green',
            'rejected' => 'red',
            default => 'yellow',
        };
    }

    /**
     * Get approval status label for UI
     */
    public function getApprovalLabelAttribute(): string
    {
        return __(ucfirst($this->approval_status ?? 'pending'));
    }
}
