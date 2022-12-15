<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\BusinessLogic\Controllers\OrderStatusMappingController;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Service\Required\OrderStatusService;

class OrderStatusMapController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\OrderStatusMappingController
     */
    private $controller;
    /**
     * @var \Packlink\Middleware\Service\Required\OrderStatusService
     */
    private $service;

    /**
     * OrderStatusMapController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\OrderStatusMappingController $controller
     * @param \Packlink\Middleware\Service\Required\OrderStatusService $service
     */
    public function __construct(OrderStatusMappingController $controller, OrderStatusService $service)
    {
        $this->controller = $controller;
        $this->service = $service;
    }

    /**
     * Provides order status map information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json(
            [
                'systemName' => $this->getConfigService()->getIntegrationName(),
                'mappings' => $this->controller->getMappings(),
                'packlinkStatuses' => $this->service->getPacklinkStatuses(),
                'orderStatuses' => $this->service->getSystemStatuses(),
                'notifiedStatuses' => $this->getConfigService()->getOrderStatusNotifications() ?? [],
            ]
        );
    }

    /**
     * Sets notified order statuses.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setNotifiedStatuses(Request $request): JsonResponse
    {
        $this->getConfigService()->setOrderStatusNotifications($request->input());

        return response()->json([], 200);
    }
}