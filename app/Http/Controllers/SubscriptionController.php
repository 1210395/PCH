<?php

namespace App\Http\Controllers;

use App\Models\ProfileSubscription;
use App\Models\CategorySubscription;
use App\Models\Designer;
use App\Models\AcademicAccount;
use App\Helpers\DropdownHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    // ==========================================
    // Profile Subscriptions
    // ==========================================

    /**
     * Toggle profile subscription
     * POST /{locale}/subscriptions/profile/toggle
     */
    public function toggleProfileSubscription(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'subscribable_type' => 'required|in:designer,academic',
            'subscribable_id' => 'required|integer|min:1',
        ]);

        $subscriberType = $user['type'];
        $subscriberId = $user['id'];

        // Prevent self-subscription
        if ($subscriberType === $validated['subscribable_type'] &&
            $subscriberId == $validated['subscribable_id']) {
            return response()->json([
                'success' => false,
                'message' => __('Cannot subscribe to your own profile')
            ], 400);
        }

        // Verify the target profile exists
        $targetExists = $validated['subscribable_type'] === 'designer'
            ? Designer::where('id', $validated['subscribable_id'])->exists()
            : AcademicAccount::where('id', $validated['subscribable_id'])->exists();

        if (!$targetExists) {
            return response()->json(['success' => false, 'message' => __('Profile not found')], 404);
        }

        $isSubscribed = ProfileSubscription::toggleSubscription(
            $subscriberType,
            $subscriberId,
            $validated['subscribable_type'],
            $validated['subscribable_id']
        );

        return response()->json([
            'success' => true,
            'subscribed' => $isSubscribed,
            'message' => $isSubscribed
                ? __('Subscribed to notifications')
                : __('Unsubscribed from notifications')
        ]);
    }

    /**
     * Check profile subscription status
     * GET /{locale}/subscriptions/profile/check
     */
    public function checkProfileSubscription(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['subscribed' => false]);
        }

        $validated = $request->validate([
            'subscribable_type' => 'required|in:designer,academic',
            'subscribable_id' => 'required|integer|min:1',
        ]);

        $isSubscribed = ProfileSubscription::isSubscribed(
            $user['type'],
            $user['id'],
            $validated['subscribable_type'],
            $validated['subscribable_id']
        );

        return response()->json(['subscribed' => $isSubscribed]);
    }

    // ==========================================
    // Category Subscriptions
    // ==========================================

    /**
     * Get category subscription settings
     * GET /{locale}/subscriptions/category/{contentType}
     */
    public function getCategorySubscription($locale, string $contentType): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->isValidContentType($contentType)) {
            return response()->json(['success' => false, 'message' => 'Invalid content type'], 400);
        }

        $subscription = CategorySubscription::where('subscriber_type', $user['type'])
            ->where('subscriber_id', $user['id'])
            ->where('content_type', $contentType)
            ->first();

        // Also return available options for the UI
        $availableOptions = $this->getAvailableOptions($contentType);

        return response()->json([
            'success' => true,
            'subscription' => $subscription ? [
                'content_type' => $subscription->content_type,
                'categories' => $subscription->categories,
                'tags' => $subscription->tags,
                'types' => $subscription->types,
                'levels' => $subscription->levels,
                'is_active' => $subscription->is_active,
            ] : null,
            'available_options' => $availableOptions,
        ]);
    }

    /**
     * Save category subscription settings
     * POST /{locale}/subscriptions/category/{contentType}
     */
    public function saveCategorySubscription(Request $request, $locale, string $contentType): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->isValidContentType($contentType)) {
            return response()->json(['success' => false, 'message' => 'Invalid content type'], 400);
        }

        $validated = $request->validate([
            'categories' => 'nullable|array',
            'categories.*' => 'string|max:100',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:100',
            'types' => 'nullable|array',
            'types.*' => 'string|in:service,collaboration,showcase,opportunity',
            'levels' => 'nullable|array',
            'levels.*' => 'string|in:beginner,intermediate,advanced',
            'is_active' => 'boolean',
        ]);

        // Convert empty arrays to null (meaning "all")
        $categories = !empty($validated['categories']) ? $validated['categories'] : null;
        $tags = !empty($validated['tags']) ? $validated['tags'] : null;
        $types = !empty($validated['types']) ? $validated['types'] : null;
        $levels = !empty($validated['levels']) ? $validated['levels'] : null;

        $subscription = CategorySubscription::updateOrCreate(
            [
                'subscriber_type' => $user['type'],
                'subscriber_id' => $user['id'],
                'content_type' => $contentType,
            ],
            [
                'categories' => $categories,
                'tags' => $tags,
                'types' => $types,
                'levels' => $levels,
                'is_active' => $validated['is_active'] ?? true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => __('Notification preferences saved'),
            'subscription' => [
                'content_type' => $subscription->content_type,
                'categories' => $subscription->categories,
                'tags' => $subscription->tags,
                'types' => $subscription->types,
                'levels' => $subscription->levels,
                'is_active' => $subscription->is_active,
            ],
        ]);
    }

    /**
     * Delete category subscription
     * DELETE /{locale}/subscriptions/category/{contentType}
     */
    public function deleteCategorySubscription($locale, string $contentType): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        if (!$this->isValidContentType($contentType)) {
            return response()->json(['success' => false, 'message' => 'Invalid content type'], 400);
        }

        CategorySubscription::where('subscriber_type', $user['type'])
            ->where('subscriber_id', $user['id'])
            ->where('content_type', $contentType)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => __('Subscription removed'),
        ]);
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Get authenticated user from either guard
     */
    private function getAuthenticatedUser(): ?array
    {
        if ($designer = auth('designer')->user()) {
            return ['type' => 'designer', 'id' => $designer->id, 'user' => $designer];
        }
        if ($academic = auth('academic')->user()) {
            return ['type' => 'academic', 'id' => $academic->id, 'user' => $academic];
        }
        return null;
    }

    /**
     * Check if content type is valid
     */
    private function isValidContentType(string $contentType): bool
    {
        return in_array($contentType, ['marketplace', 'product', 'project', 'service', 'training']);
    }

    /**
     * Get available options for a content type from DropdownHelper
     */
    private function getAvailableOptions(string $contentType): array
    {
        $options = [
            'categories' => [],
            'tags' => [],
            'types' => [],
            'levels' => [],
        ];

        try {
            switch ($contentType) {
                case 'marketplace':
                    $options['categories'] = DropdownHelper::marketplaceCategories();
                    $options['tags'] = DropdownHelper::marketplaceTags();
                    $options['types'] = [
                        ['value' => 'service', 'label' => __('Service')],
                        ['value' => 'collaboration', 'label' => __('Collaboration')],
                        ['value' => 'showcase', 'label' => __('Showcase')],
                        ['value' => 'opportunity', 'label' => __('Opportunity')],
                    ];
                    break;

                case 'product':
                    $options['categories'] = DropdownHelper::productCategories();
                    break;

                case 'project':
                    $options['categories'] = DropdownHelper::projectCategories();
                    break;

                case 'service':
                    $options['categories'] = DropdownHelper::serviceCategories();
                    break;

                case 'training':
                    $options['categories'] = DropdownHelper::trainingCategories();
                    $options['levels'] = [
                        ['value' => 'beginner', 'label' => __('Beginner')],
                        ['value' => 'intermediate', 'label' => __('Intermediate')],
                        ['value' => 'advanced', 'label' => __('Advanced')],
                    ];
                    break;
            }
        } catch (\Exception $e) {
            // If DropdownHelper methods fail, return empty arrays
            \Log::warning('Failed to get dropdown options for ' . $contentType . ': ' . $e->getMessage());
        }

        return $options;
    }
}
