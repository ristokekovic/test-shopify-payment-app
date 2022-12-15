<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\BusinessLogic\Controllers\DefaultParcelController;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class ParcelController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\DefaultParcelController
     */
    private $controller;

    /**
     * ParcelController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\DefaultParcelController $controller
     */
    public function __construct(DefaultParcelController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Provides default parcel.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(): JsonResponse
    {
        $parcel = $this->controller->getDefaultParcel();

        return response()->json($parcel ? $parcel->toArray() : []);
    }

    /**
     * Updates default parcel.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Packlink\BusinessLogic\DTO\Exceptions\FrontDtoValidationException
     */
    public function submit(Request $request): JsonResponse
    {
        $this->controller->setDefaultParcel($request->input());

        return $this->get();
    }
}