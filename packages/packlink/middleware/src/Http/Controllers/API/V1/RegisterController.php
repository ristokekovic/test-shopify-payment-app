<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\BusinessLogic\Controllers\RegistrationController;
use Packlink\BusinessLogic\DTO\Exceptions\FrontDtoValidationException;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class RegisterController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\RegistrationController
     */
    private $controller;

    /**
     * RegisterController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\RegistrationController $controller
     */
    public function __construct(RegistrationController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Retrieves registration data used to prefill the login form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        if (($country = $request->query->get('country')) === null) {
            return $this->error('Not found!', 404);
        }

        return response()->json($this->controller->getRegisterData($country));
    }

    /**
     * Handles the register request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request): JsonResponse
    {
        $payload = $request->input();
        $payload['ecommerces'] = [$this->getConfigService()->getIntegrationName()];
        try {
            $status = $this->controller->register($payload);
        } catch (FrontDtoValidationException $e) {
            return $this->transformEntitiesToJsonResponse($e->getValidationErrors());
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode() ?: 400);
        }

        return response()->json(['success' => $status]);
    }
}