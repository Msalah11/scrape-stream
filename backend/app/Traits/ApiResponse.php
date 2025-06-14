<?php

namespace App\Traits;

trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param string $message
     * @param array $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(string $message = 'Success', array $data = [], int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return an error response
     *
     * @param string $message
     * @param int $statusCode
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message = 'Error', int $statusCode = 400, array $errors = [])
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
