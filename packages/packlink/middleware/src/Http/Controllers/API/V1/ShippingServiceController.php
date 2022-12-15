<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Packlink\BusinessLogic\Controllers\DTO\ShippingMethodConfiguration;
use Packlink\BusinessLogic\Controllers\ShippingMethodController;
use Packlink\BusinessLogic\Controllers\UpdateShippingServicesTaskStatusController;
use Packlink\BusinessLogic\DTO\Exceptions\FrontDtoValidationException;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class ShippingServiceController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\ShippingMethodController
     */
    private $controller;

    /**
     * ShippingServiceController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\ShippingMethodController $controller
     */
    public function __construct(ShippingMethodController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Provides shipping service identified by the id query parameter.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        $id = $request->query->get('id');
        if (!$id) {
            $this->error('Not found!', 404);
        }

        $method = $this->controller->getShippingMethod($id);
        if (!$method) {
            $this->error('Not found!', 404);
        }

        return response()->json($method->toArray());
    }

    /**
     * Provides all shipping services.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(): JsonResponse
    {
        return $this->transformEntitiesToJsonResponse($this->controller->getAll());
    }

    /**
     * Provides active shipping services.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActive(): JsonResponse
    {
        return $this->transformEntitiesToJsonResponse($this->controller->getActive());
    }

    /**
     * Provides inactive shipping services.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInactive(): JsonResponse
    {
        return $this->transformEntitiesToJsonResponse($this->controller->getInactive());
    }

    /**
     * Updates shipping service.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $configuration = ShippingMethodConfiguration::fromArray($request->input());
            $response = $this->controller->save($configuration);
            $response = response()->json($response ? $response->toArray() : []);
        } catch (FrontDtoValidationException $e) {
            $response = $this->transformEntitiesToJsonResponse($e->getValidationErrors());
            $response->setStatusCode(400);
        }

        return $response;
    }

    /**
     * Activates shipping service identified by the id query parameter.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate(Request $request): JsonResponse
    {
        $status = false;
        if (!empty($id = $request->input('id'))) {
            $status = $this->controller->activate($id);
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Deactivates shipping service identified by the id query parameter.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deactivate(Request $request): JsonResponse
    {
        $status = false;
        if (!empty($id = $request->input('id'))) {
            $status = $this->controller->deactivate($id);
        }

        return response()->json(['status' => $status]);
    }

    /**
     * Provides update shipping method task status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaskStatus(): JsonResponse
    {
        if ( count( $this->controller->getAll() ) > 0 ) {
            return response()->json(['status' => QueueItem::COMPLETED]);
        }

        $controller = new UpdateShippingServicesTaskStatusController();
        try {
            $status = $controller->getLastTaskStatus();
        } catch ( \Exception $e ) {
            $status = QueueItem::FAILED;
        }

        return response()->json(['status' => $status]);
    }
}