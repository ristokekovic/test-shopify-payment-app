<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\BusinessLogic\Controllers\SystemInfoController;

/**
 * Class SystemController
 *
 * @package Packlink\Middleware\Http\Controllers\API\V1
 */
class SystemController extends ApiController
{
    /**
     * @var SystemInfoController
     */
    private $controller;

    /**
     * SystemController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\SystemInfoController $controller
     */
    public function __construct(SystemInfoController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Returns system details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {
        return $this->transformEntitiesToJsonResponse($this->controller->get());
    }
}
