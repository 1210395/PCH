<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base controller for all academic portal controllers.
 * Provides shared JSON response helpers (successResponse / errorResponse) and convenience
 * accessors (getAccount / getAccountId) for the authenticated academic guard user.
 * All academic controllers extend this class instead of the root Controller.
 */
class AcademicBaseController extends Controller
{
    /**
     * Return a raw JSON response.
     *
     * @param  mixed  $data
     * @param  int    $status  HTTP status code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($data, $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    /**
     * Return a success JSON response with an optional data payload.
     *
     * @param  string  $message
     * @param  array   $data
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @param  string  $message
     * @param  int     $status  HTTP status code (default 400)
     * @param  array   $errors  Field-level error details
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @return \App\Models\AcademicAccount|null
     */
    protected function getAccount()
    {
        return auth('academic')->user();
    }

    /**
     * Get the authenticated academic account ID.
     *
     * @return int|null
     */
    protected function getAccountId()
    {
        return auth('academic')->id();
    }
}
