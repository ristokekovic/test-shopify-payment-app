<?php

namespace Packlink\Middleware\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Packlink\Middleware\Http\Controllers\BaseController;

abstract class ApiController extends BaseController
{
    /**
     * Converts DTOs to array and returns a JSON response.
     *
     * @param \Logeecom\Infrastructure\Data\DataTransferObject[] $entities
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function transformEntitiesToJsonResponse(array $entities): JsonResponse
    {
        $response = [];

        foreach ($entities as $entity) {
            $response[] = $entity->toArray();
        }

        return response()->json($response);
    }

    /**
     * Prepares error response.
     *
     * @param string $message
     * @param int $status
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function error(string $message, int $status = 400) {
        return response()->json(['error' => $message], $status);
    }
}