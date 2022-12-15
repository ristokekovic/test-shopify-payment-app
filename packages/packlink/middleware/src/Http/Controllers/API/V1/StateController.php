<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Packlink\BusinessLogic\Controllers\ModuleStateController;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class StateController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\ModuleStateController
     */
    private $controller;

    /**
     * StateController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\ModuleStateController $controller
     */
    public function __construct(ModuleStateController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Retrieves current module state.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getState(): JsonResponse
    {
        return response()->json($this->controller->getCurrentState()->toArray());
    }
}