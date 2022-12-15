<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class ConfigurationController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\ConfigurationController
     */
    private $controller;

    /**
     * ConfigurationController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\ConfigurationController $controller
     */
    public function __construct(\Packlink\BusinessLogic\Controllers\ConfigurationController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Provides data for the configuration page.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json(
            [
                'helpUrl' => $this->controller->getHelpLink(),
                'version' => config('app.version', '1.0.0'),
            ]
        );
    }
}