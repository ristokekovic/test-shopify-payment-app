<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\BusinessLogic\ShipmentDraft\OrderSendDraftTaskMapService;
use Packlink\BusinessLogic\ShipmentDraft\ShipmentDraftService;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Service\BusinessLogic\ApiOrdersService;

class DraftsController extends ApiController
{
    /**
     * @var ApiOrdersService
     */
    private $orderService;

    /**
     * DraftsController constructor.
     *
     * @param \Packlink\Middleware\Service\BusinessLogic\ApiOrdersService $service
     */
    public function __construct(ApiOrdersService $service)
    {
        $this->orderService = $service;
    }

    /**
     * Enqueues send draft task.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     * @throws \Packlink\BusinessLogic\ShipmentDraft\Exceptions\DraftTaskMapExists
     * @throws \Packlink\BusinessLogic\ShipmentDraft\Exceptions\DraftTaskMapNotFound
     */
    public function create(Request $request)
    {
        if (empty($orderId = $request->input('orderId'))) {
            return $this->error('Order id not provided.', 400);
        }

        $this->getService()->enqueueCreateShipmentDraftTask((string)$orderId);

        return response()->json(['success' => true]);
    }

    /**
     * Returns draft information linked with the order identified by its ID from the request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function get(Request $request): JsonResponse
    {
        if (!$request->has('orderId')) {
            return $this->error('Order id not provided.', 400);
        }

        /** @var OrderSendDraftTaskMapService $draftTaskMapService */
        $draftTaskMapService = ServiceRegister::getService(OrderSendDraftTaskMapService::CLASS_NAME);
        $orderTaskMap = $draftTaskMapService->getOrderTaskMap((int)$request->get('orderId'));

        if ($orderTaskMap === null) {
            return $this->error('Not found!', 404);
        }

        $draft = $this->orderService->getOrderDraft((int)$request->get('orderId'));

        return response()->json($draft ? $draft->toArray() : []);
    }

    /**
     * Retrieves shipment draft service.
     *
     * @return ShipmentDraftService | object
     */
    private function getService()
    {
        return ServiceRegister::getService(ShipmentDraftService::CLASS_NAME);
    }
}