<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Packlink\BusinessLogic\Controllers\RegistrationRegionsController;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class RegionsController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\RegistrationRegionsController
     */
    private $controller;

    /**
     * RegionsController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\RegistrationRegionsController $controller
     */
    public function __construct(RegistrationRegionsController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Retrieves registration regions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegions(): JsonResponse
    {
        return $this->transformEntitiesToJsonResponse($this->controller->getRegions());
    }
}