<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class LocationsController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\LocationsController
     */
    private $controller;

    /**
     * LocationsController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\LocationsController $controller
     */
    public function __construct(\Packlink\BusinessLogic\Controllers\LocationsController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Performs a location search.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        return $this->transformEntitiesToJsonResponse($this->controller->searchLocations($request->input()));
    }
}