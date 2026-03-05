<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AcademicBaseController extends Controller
{
    /**
     * Return a JSON response.
     */
    protected function jsonResponse($data, $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    /**
     * Return a success JSON response.
     */
    protected function successResponse($message, $data = []): JsonResponse
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    /**
     * Return an error JSON response.
     */
    protected function errorResponse($message, $status = 400, $errors = []): JsonResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $status);
    }

    /**
     * Get the authenticated academic account.
     */
    protected function getAccount()
    {
        return auth('academic')->user();
    }

    /**
     * Get the authenticated academic account ID.
     */
    protected function getAccountId()
    {
        return auth('academic')->id();
    }
}
