<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Service\BusinessLogic\ApiOrdersService;

class OrdersController extends ApiController
{
    /**
     * @var \Packlink\Middleware\Service\BusinessLogic\ApiOrdersService
     */
    private $service;

    /**
     * OrdersController constructor.
     *
     * @param \Packlink\Middleware\Service\BusinessLogic\ApiOrdersService $service
     */
    public function __construct(ApiOrdersService $service)
    {
        $this->service = $service;
    }

    /**
     * Provides order page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function list(Request $request): JsonResponse
    {
        $page = $this->service->list($request->input('page'), (int)$request->input('limit'));

        return response()->json($page->toArray());
    }

    /**
     * Provides list of specific orders.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function specific(Request $request)
    {
        $ids = $request->input('orderIds');
        if (empty($ids) || empty($ids = explode(',', $ids))) {
            return response()->json([]);
        }

        $page = $this->service->specific($ids);

        return response()->json($page->toArray());
    }

    /**
     * Provides active orders.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function active(Request $request): JsonResponse
    {
        $page = $this->service->getActive((int)$request->input('page'), (int)$request->input('limit'));

        return response()->json($page->toArray());
    }

    /**
     * Provides order.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function get(Request $request): JsonResponse
    {
        $order = $this->service->get((int)$request->get('orderId'));
        if ($order === null) {
            return $this->error('Not found!', 404);
        }

        return response()->json($order->toArray());
    }

    /**
     * Provides order count.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCount(): JsonResponse
    {
        return response()->json(['count' => $this->service->getCount()]);
    }

    /**
     * Provides count of active orders.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getActiveCount()
    {
        return response()->json(['count' => $this->service->getActiveCount()]);
    }
}