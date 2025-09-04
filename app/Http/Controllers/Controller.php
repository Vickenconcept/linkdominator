<?php

namespace App\Http\Controllers;

abstract class Controller
{
    /**
     * Success response helper
     */
    protected function successResponse($data = [], $message = 'Success', $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Error response helper
     */
    protected function errorResponse($message = 'Error', $status = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }
}
