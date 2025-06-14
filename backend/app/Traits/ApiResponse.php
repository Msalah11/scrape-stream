<?php

namespace App\Traits;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

trait ApiResponse
{
    /**
     * Return a success response
     *
     * @param string $message
     * @param mixed $data Resource, ResourceCollection, array or null
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(string $message = 'Success', $data = null, int $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        
        // Handle different data types appropriately
        if ($data instanceof ResourceCollection) {
            // ResourceCollection already has its own structure
            $resourceData = $data->response()->getData(true);
            $response = array_merge($response, $resourceData);
        } elseif ($data instanceof JsonResource) {
            // Single resource
            $response['data'] = $data;
        } elseif (!is_null($data)) {
            // Regular array or other data
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
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
