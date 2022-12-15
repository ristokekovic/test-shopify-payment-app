<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\BusinessLogic\Controllers\DebugController;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Utility\Route;

class SystemInfoController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\DebugController
     */
    private $controller;

    /**
     * SystemInfoController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\DebugController $controller
     */
    public function __construct(DebugController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Provides system info data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus(): JsonResponse
    {
        return response()->json(
            [
                'status' => $this->controller->getStatus(),
                'downloadUrl' => Route::to('platform.api.v1.debug.get', ['platform' => config('brand.active')]),
                'fileName' => DebugController::SYSTEM_INFO_FILE_NAME,
            ]
        );
    }

    /**
     * Updates system info debug status.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function submitStatus(Request $request)
    {
        $this->controller->setStatus($request->input('status'));
    }
}