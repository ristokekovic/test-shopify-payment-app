<?php

namespace Packlink\Middleware\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logeecom\Infrastructure\Logger\Logger;
use Packlink\BusinessLogic\Language\Translator;
use Packlink\BusinessLogic\WebHook\WebHookEventHandler;

/**
 * Class WebhooksController
 *
 * @package Packlink\Middleware\Http\Controllers
 */
class WebhooksController extends BaseController
{
    /**
     * Handles webhook event.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        if ($request->has('context') && !empty($request->getContent())) {
            Logger::logDebug('Webhook from Packlink received.', 'Integration', ['payload' => $request->getContent()]);

            $this->getConfigService()->setContext($request->get('context'));
            $webhookHandler = WebHookEventHandler::getInstance();

            return response()->json(
                [
                    'success' => $webhookHandler->handle($request->getContent()),
                ]
            );
        }

        return response()->json(
            [
                'success' => false,
                'message' => Translator::translate('components.invalid_payload'),
            ]
        );
    }
}
