<?php

namespace Packlink\Middleware\Http\Controllers;

use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ServiceRegister;
use Logeecom\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;

/**
 * Class AsyncProcessController
 *
 * @package Packlink\Middleware\Http\Controllers
 */
class AsyncProcessController extends BaseController
{
    public function run($guid)
    {
        Logger::logDebug('Received async process request.', 'Integration', ['guid' => $guid]);

        if (!$guid) {
            abort(401, 'guid is missing');
        }

        /** @var AsyncProcessService $asyncProcessService */
        $asyncProcessService = ServiceRegister::getService(AsyncProcessService::class);
        $asyncProcessService->runProcess($guid);

        return response(['success' => true], 200);
    }
}
