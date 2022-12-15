<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Packlink\BusinessLogic\Controllers\OnboardingController;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class OnboardingStateController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\OnboardingController
     */
    private $controller;

    /**
     * OnboardingStateController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\OnboardingController $controller
     */
    public function __construct(OnboardingController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Provides current onboarding state.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(): JsonResponse
    {
        return response()->json($this->controller->getCurrentState()->toArray());
    }
}