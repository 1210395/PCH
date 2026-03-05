<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;

abstract class AdminBaseController extends Controller
{
    /**
     * Validate and sanitize request data
     */
    protected function validateAndSanitize(Request $request, array $rules, array $messages = []): array
    {
        $validated = $request->validate($rules, $messages);

        // Sanitize string fields to prevent XSS
        foreach ($validated as $key => $value) {
            if (is_string($value)) {
                $validated[$key] = strip_tags($value);
            }
        }

        return $validated;
    }

    /**
     * Return a JSON response
     */
    protected function jsonResponse($data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    /**
     * Return a success JSON response
     */
    protected function successResponse(string $message, $data = null): JsonResponse
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Return an error JSON response
     */
    protected function errorResponse(string $message, int $status = 400, $errors = null): JsonResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }

    /**
     * Validate numeric ID parameter
     */
    protected function validateId($id): bool
    {
        return is_numeric($id) && $id > 0;
    }

    /**
     * Get the current admin designer
     */
    protected function getAdmin()
    {
        return auth('designer')->user();
    }

    /**
     * Get admin ID for tracking approvals
     */
    protected function getAdminId(): int
    {
        return auth('designer')->id();
    }

    /**
     * Approve a content item (product, project, service, marketplace post, etc.)
     *
     * @param string $modelClass  Fully-qualified model class (must use HasApprovalStatus)
     * @param mixed  $id          The model's primary key
     * @param string $label       Human-readable label for response messages (e.g. "Product")
     */
    protected function approveContent(string $modelClass, $id, string $label): JsonResponse
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse("Invalid {$label} ID", 400);
        }

        $item = $modelClass::findOrFail($id);
        $item->approve($this->getAdminId());

        return $this->successResponse("{$label} approved successfully", $item->fresh());
    }

    /**
     * Reject a content item with an optional reason.
     *
     * @param string  $modelClass  Fully-qualified model class (must use HasApprovalStatus)
     * @param mixed   $id          The model's primary key
     * @param string  $label       Human-readable label for response messages (e.g. "Product")
     * @param Request $request     The current request (used to extract rejection reason)
     */
    protected function rejectContent(string $modelClass, $id, string $label, Request $request): JsonResponse
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse("Invalid {$label} ID", 400);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $item = $modelClass::findOrFail($id);
        $item->reject($this->getAdminId(), $validated['reason'] ?? null);

        return $this->successResponse("{$label} rejected", $item->fresh());
    }

    /**
     * Toggle the featured flag on a content item.
     *
     * @param string $modelClass  Fully-qualified model class (must have a 'featured' column)
     * @param mixed  $id          The model's primary key
     * @param string $label       Human-readable label for response messages (e.g. "Product")
     */
    protected function toggleContentFeatured(string $modelClass, $id, string $label): JsonResponse
    {
        if (!$this->validateId($id)) {
            return $this->errorResponse("Invalid {$label} ID", 400);
        }

        $item = $modelClass::findOrFail($id);
        $item->featured = !$item->featured;
        $item->save();

        $state = $item->featured ? 'featured' : 'unfeatured';

        return $this->successResponse("{$label} {$state} successfully", $item->fresh());
    }
}
