<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\BusinessLogic\Configuration;
use Packlink\BusinessLogic\DTO\Exceptions\FrontDtoValidationException;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class WarehouseController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\WarehouseController
     */
    private $controller;

    /**
     * WarehouseController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\WarehouseController $controller
     */
    public function __construct(\Packlink\BusinessLogic\Controllers\WarehouseController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Provides warehouse.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(): JsonResponse
    {
        $warehouse = $this->controller->getWarehouse();

        return response()->json($warehouse ? $warehouse->toArray() : []);
    }

    /**
     * Updates warehouse.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Packlink\BusinessLogic\DTO\Exceptions\FrontDtoNotRegisteredException
     */
    public function submit(Request $request): JsonResponse
    {
        $payload = $request->input();

        try {
            $warehouse = $this->controller->updateWarehouse($payload);
        } catch (FrontDtoValidationException $e) {
            return $this->transformEntitiesToJsonResponse($e->getValidationErrors());
        }

        return response()->json($warehouse->toArray());
    }

    /**
     * Returns supported warehouse countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSupportedCountries(): JsonResponse
    {
        $controller = new \Packlink\BusinessLogic\Controllers\WarehouseController();

        Configuration::setUICountryCode(\App::getLocale());

        return response()->json($controller->getWarehouseCountries());
    }
}