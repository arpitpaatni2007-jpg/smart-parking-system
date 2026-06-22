<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

/**
 * ============================================================
 * ApiResponse Trait
 * ============================================================
 *
 * WHY THIS TRAIT EXISTS:
 * Without this, every controller method would manually call
 * response()->json([...]) with a different structure each time.
 * This leads to inconsistent API responses — some say "status",
 * some say "success", some include "data", some don't.
 *
 * This trait provides a single, consistent response envelope
 * for the ENTIRE API. The Flutter app can always expect:
 *
 * SUCCESS:
 * {
 *   "success": true,
 *   "message": "User registered successfully.",
 *   "data": { ... }
 * }
 *
 * ERROR:
 * {
 *   "success": false,
 *   "message": "Invalid credentials.",
 *   "data": null
 * }
 *
 * USAGE IN CONTROLLERS:
 *   use App\Traits\ApiResponse;
 *   ...
 *   return $this->successResponse($data, 'Login successful.');
 *   return $this->errorResponse('Invalid credentials.', 401);
 *   return $this->validationErrorResponse($errors);
 *
 * Including this trait in a BaseController (or directly in
 * AuthController) makes these methods available everywhere.
 */
trait ApiResponse
{
    /**
     * Return a successful JSON response.
     *
     * @param  mixed  $data     The payload to return (array, Resource, Collection, null)
     * @param  string $message  Human-readable success message
     * @param  int    $status   HTTP status code (default: 200 OK)
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(
        mixed $data = null,
        string $message = 'Success.',
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Return a created (201) JSON response.
     * Semantic shortcut for POST endpoints that create a resource.
     *
     * @param  mixed  $data     The newly created resource
     * @param  string $message  Human-readable message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createdResponse(
        mixed $data = null,
        string $message = 'Resource created successfully.'
    ): JsonResponse {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return an error JSON response.
     *
     * @param  string $message  Human-readable error description
     * @param  int    $status   HTTP status code (default: 400 Bad Request)
     * @param  mixed  $errors   Optional validation or detailed error data
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(
        string $message = 'Something went wrong.',
        int $status = 400,
        mixed $errors = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        // Only include 'errors' key if there are errors to report
        if (! is_null($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    /**
     * Return a 401 Unauthorized response.
     * Used when authentication fails (wrong credentials, no token).
     *
     * @param  string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse(
        string $message = 'Unauthorized. Please login to continue.'
    ): JsonResponse {
        return $this->errorResponse($message, 401);
    }

    /**
     * Return a 403 Forbidden response.
     * Used when an authenticated user lacks permission for an action.
     *
     * @param  string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbiddenResponse(
        string $message = 'You do not have permission to perform this action.'
    ): JsonResponse {
        return $this->errorResponse($message, 403);
    }

    /**
     * Return a 404 Not Found response.
     *
     * @param  string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function notFoundResponse(
        string $message = 'The requested resource was not found.'
    ): JsonResponse {
        return $this->errorResponse($message, 404);
    }

    /**
     * Return a 422 Validation Error response.
     * Used when request data fails business logic validation
     * (separate from FormRequest which handles field-level validation).
     *
     * @param  mixed  $errors   Validation errors array
     * @param  string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function validationErrorResponse(
        mixed $errors = null,
        string $message = 'Validation failed.'
    ): JsonResponse {
        return $this->errorResponse($message, 422, $errors);
    }

    /**
     * Return a 500 Server Error response.
     * Use in catch blocks for unexpected exceptions.
     *
     * @param  string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function serverErrorResponse(
        string $message = 'An unexpected error occurred. Please try again later.'
    ): JsonResponse {
        return $this->errorResponse($message, 500);
    }
}