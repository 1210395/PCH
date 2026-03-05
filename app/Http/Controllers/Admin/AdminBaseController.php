<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
}
