<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param mixed $data
     * @param string|null $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, ?string $message = null, int $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $statusCode
     * @param array|null $errors
     * @param string|null $errorCode
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message,
        int $statusCode = 400,
        ?array $errors = null,
        ?string $errorCode = null
    ): JsonResponse {
        $response = [
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $errorCode ?? $this->getErrorCode($statusCode),
            ],
        ];

        if ($errors !== null) {
            $response['error']['details'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return a validation error response
     *
     * @param array $errors
     * @return JsonResponse
     */
    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return $this->errorResponse(
            'Validation failed',
            422,
            $errors,
            'VALIDATION_ERROR'
        );
    }

    /**
     * Return a not found error response
     *
     * @param string $resource
     * @return JsonResponse
     */
    protected function notFoundResponse(string $resource = 'Resource'): JsonResponse
    {
        return $this->errorResponse(
            "{$resource} not found",
            404,
            null,
            'NOT_FOUND'
        );
    }

    /**
     * Return an unauthorized error response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function unauthorizedResponse(string $message = 'Authentication required'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            401,
            null,
            'UNAUTHORIZED'
        );
    }

    /**
     * Return a forbidden error response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function forbiddenResponse(string $message = 'Access denied'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            403,
            null,
            'FORBIDDEN'
        );
    }

    /**
     * Return a conflict error response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function conflictResponse(string $message): JsonResponse
    {
        return $this->errorResponse(
            $message,
            409,
            null,
            'CONFLICT'
        );
    }

    /**
     * Return a server error response
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function serverErrorResponse(string $message = 'Internal server error'): JsonResponse
    {
        return $this->errorResponse(
            $message,
            500,
            null,
            'SERVER_ERROR'
        );
    }

    /**
     * Get error code based on HTTP status code
     *
     * @param int $statusCode
     * @return string
     */
    private function getErrorCode(int $statusCode): string
    {
        $codes = [
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            429 => 'TOO_MANY_REQUESTS',
            500 => 'SERVER_ERROR',
            503 => 'SERVICE_UNAVAILABLE',
        ];

        return $codes[$statusCode] ?? 'ERROR';
    }
}
