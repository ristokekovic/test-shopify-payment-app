<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\Request;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\BusinessLogic\Order\OrderService;
use Packlink\BusinessLogic\OrderShipmentDetails\Models\OrderShipmentDetails;
use Packlink\BusinessLogic\OrderShipmentDetails\OrderShipmentDetailsService;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Service\Required\LabelsService;

class LabelsController extends ApiController
{
    /**
     * @var \Packlink\Middleware\Service\Required\LabelsService
     */
    private $service;

    /**
     * LabelsController constructor.
     *
     * @param \Packlink\Middleware\Service\Required\LabelsService $service
     */
    public function __construct(LabelsService $service)
    {
        $this->service = $service;
    }

    /**
     * Marks label as printed.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Packlink\BusinessLogic\OrderShipmentDetails\Exceptions\OrderShipmentDetailsNotFound
     */
    public function print(Request $request)
    {
        if (empty($orderId = $request->input('orderId'))) {
            return $this->error('Order id not provided.', 400);
        }

        $details = $this->getDetailsService()->getDetailsByOrderId((string)$orderId);
        if ($details === null) {
            return $this->error('Label not found', 404);
        }

        if (empty($labels = $details->getShipmentLabels())) {
            $labels = $this->fetch($details);
        }

        if (empty($labels[0])) {
            return $this->error('Label not found', 404);
        }

        $labels[0]->setPrinted(true);
        $this->getDetailsService()->setLabelsByReference($details->getReference(), $labels);

        $this->service->print($orderId);

        return response()->json(['url' => $labels[0]->getLink()]);
    }

    /**
     * Provides shipment labels.
     *
     * @param \Packlink\BusinessLogic\OrderShipmentDetails\Models\OrderShipmentDetails $details
     *
     * @return array
     */
    private function fetch(OrderShipmentDetails $details): array
    {
        if (!$this->getOrderService()->isReadyToFetchShipmentLabels($details->getShippingStatus())) {
            return [];
        }

        return $this->getOrderService()->getShipmentLabels($details->getReference());
    }

    /**
     * Provides order shipment details service.
     *
     * @return OrderShipmentDetailsService | object
     */
    private function getDetailsService()
    {
        return ServiceRegister::getService(OrderShipmentDetailsService::CLASS_NAME);
    }

    /**
     * Provides order service.
     *
     * @return OrderService | object
     */
    private function getOrderService()
    {
        return ServiceRegister::getService(OrderService::CLASS_NAME);
    }
}