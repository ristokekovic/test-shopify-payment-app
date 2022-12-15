<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\ServiceRegister;
use Logeecom\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Service\BusinessLogic\ConfigurationService;
use Packlink\Shopify\Http\Controllers\ShopifyWebhooksController;

/**
 * Class SupportController.
 *
 * @package Packlink\Middleware\Http\Controllers
 */
class SupportController extends ApiController
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    public function get(Request $request): JsonResponse
    {
        $this->wakeup();

        $result = $this->getConfigParameters($request);

        return response()->json($result);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function post(Request $request): JsonResponse
    {
        return response()->json($this->update($request));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $configService = $this->getConfigService();

        $allowedContexts = [
            'packlink-development.myshopify.com',
            'packlink-development-support.myshopify.com',
            'packlink-pro.myshopify.com',
        ];
        if ($request->get('user_domain') && in_array($configService->getContext(), $allowedContexts)) {
            $configService->setContext($request->get('user_domain'));

            /** @var ShopifyWebhooksController $shopifyWebhooksController */
            $shopifyWebhooksController = app(ShopifyWebhooksController::class);
            $shopifyWebhooksController->appDeleted();
        }

        return response()->json(['success' => true]);
    }

    /**
     * Wakes up the task runner if needed.
     */
    private function wakeup(): void
    {
        /** @var TaskRunnerWakeup $taskRunnerWakeup */
        $taskRunnerWakeup = ServiceRegister::getService(TaskRunnerWakeup::CLASS_NAME);
        $taskRunnerWakeup->wakeup();
    }

    /**
     * Returns all configuration parameters for diagnostics purposes.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    private function getConfigParameters(Request $request): array
    {
        $configService = $this->getConfigService();

        $result = [
            'context' => $configService->getContext(),
            'isDefaultLoggerEnabled' => $configService->isDefaultLoggerEnabled(),
            'debugModeEnabled' => $configService->isDebugModeEnabled(),
            'app.url' => config('app.url'),
            'shopify_assets.version' => config('shopify_assets.version'),
        ];

        // get global debug mode
        $context = $configService->getContext();
        $configService->setContext('');
        $result['globalDebugModeEnabled'] = $configService->isDebugModeEnabled();
        $configService->setContext($context);

        $configKeys = [
            'minLogLevel',
            'maxStartedTasksLimit',
            'taskRunnerWakeupDelay',
            'taskRunnerMaxAliveTime',
            'schedulerTimeThreshold',
            'maxTaskExecutionRetries',
            'maxTaskInactivityPeriod',
            'taskRunnerStatus',
            'syncRequestTimeout',
            'asyncRequestTimeout',
            'asyncRequestWithProgress',
        ];

        foreach ($configKeys as $key) {
            $ucKey = ucfirst($key);
            foreach (['get' . $ucKey, 'is' . $ucKey] as $getter) {
                if (method_exists($configService, $getter)) {
                    $result[$key] = $configService->$getter();
                }
            }
        }

        if ($request->get('with_queue')) {
            $items = $this->getQueueItems($configService);

            $result['currentActiveQueueCount'] = count($items);
            $result['currentActiveQueue'] = $items;
        }

        return $result;
    }

    /**
     * Updates configuration from POST request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    private function update(Request $request): array
    {
        $configService = $this->getConfigService();

        foreach ($request->post() as $key => $value) {
            if ($key === 'globalDebugModeEnabled') {
                $context = $configService->getContext();
                $configService->setContext('');
                $configService->setDebugModeEnabled($value);
                $configService->setContext($context);
                continue;
            }

            $setter = 'set' . ucfirst($key);
            if (method_exists($configService, $setter)) {
                $configService->$setter($value);
            }
        }

        return array('message' => 'Successfully updated config values!');
    }

    /**
     * @param \Packlink\Middleware\Service\BusinessLogic\ConfigurationService $configService
     *
     * @return array
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryClassException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueItemDeserializationException
     */
    private function getQueueItems(ConfigurationService $configService): array
    {
        $queue = RepositoryRegistry::getQueueItemRepository();
        $items = [];
        $filter = new QueryFilter();
        $filter->where('status', Operators::NOT_EQUALS, QueueItem::COMPLETED);
        $filter->where('context', Operators::EQUALS, $configService->getContext());

        foreach ($queue->select($filter) as $item) {
            $items[] = [
                'type' => $item->getTaskType(),
                'status' => $item->getStatus(),
                'startedAt' => date('c', $item->getStartTimestamp()),
                'progress' => $item->getProgressBasePoints(),
                'retries' => $item->getRetries(),
                'failure' => $item->getFailureDescription(),
            ];
        }

        return $items;
    }
}
