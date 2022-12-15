<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Packlink\Middleware\Http\Controllers\API\ApiController;

class LoginController extends ApiController
{
    /**
     * @var \Packlink\BusinessLogic\Controllers\LoginController
     */
    private $controller;

    /**
     * LoginController constructor.
     *
     * @param \Packlink\BusinessLogic\Controllers\LoginController $controller
     */
    public function __construct(\Packlink\BusinessLogic\Controllers\LoginController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Processes login request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function login(Request $request): JsonResponse
    {
        $status = $this->controller->login($request->input('apiKey', ''));

        return response()->json(['success' => $status]);
    }
}