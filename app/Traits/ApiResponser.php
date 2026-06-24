<?php

namespace App\Traits;

trait ApiResponser
{
    protected function successResponse($data = [],  $code = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code, $data = null): \Illuminate\Http\JsonResponse
    {
        return response()->json(['message' => $message, 'status' => false, 'code' => $code, 'data' => $data], $code);
    }

    protected function showAll($collection, $message = null, $code = 200)
    {
        return $this->successResponse(['data' => $collection,'message' => $message, 'status' => true, 'code' => $code], $code);
    }

    protected function showPaginated($collection, $message = null, $code = 200)
    {
        return $this->successResponse([
            'data' => $collection,
            'message' => $message,
            'status' => true,
            'code' => $code,
            'pagination' => [
                'total' => $collection->total(),
                'per_page' => $collection->perPage(),
                'current_page' => $collection->currentPage(),
                'last_page' => $collection->lastPage(),
            ],
        ], $code);
    }
    protected function showOne($instance, $message = null, $code = 200)
    {
        return $this->successResponse(['data' => $instance, 'message' => $message, 'status' => true, 'code' => $code], $code);
    }

    protected function showMessages($message, $code = 200)
    {
        return $this->successResponse(['message' => $message, 'status' => true, 'code' => $code], $code);
    }

    protected function showError(\Throwable $th, $code = 500)
    {
        return response()->json([
            'message' => $th->getMessage(),
            'status' => false,
            'code' => $code,
        ], $code);
    }
}
