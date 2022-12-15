<?php

namespace Packlink\Middleware\Service\BusinessLogic;

use Logeecom\Infrastructure\Logger\Logger;
use Packlink\BusinessLogic\Configuration;
use Packlink\Middleware\Entity\Tenant;

/**
 * Class ConfigurationService
 *
 * @package Packlink\Middleware\Service\BusinessLogic
 */
abstract class ConfigurationService extends Configuration
{
    /**
     * Minimal log level
     */
    public const MIN_LOG_LEVEL = Logger::WARNING;
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Returns default queue name.
     *
     * @return string Default queue name.
     */
    public function getDefaultQueueName(): string
    {
        return $this->getCurrentSystemId() ?? 'default';
    }

    /**
     * @inheritDoc
     */
    public function isAsyncRequestWithProgress()
    {
        // Turn on async requests with progress callback by default
        return (bool)$this->getConfigValue('asyncRequestWithProgress', false);
    }

    /**
     * Returns current system identifier.
     *
     * @return string Current system identifier.
     */
    public function getCurrentSystemId(): string
    {
        return $this->getContext() ?? '';
    }

    /**
     * Returns webhook callback URL for the current system.
     *
     * @return string Webhook callback URL.
     */
    public function getWebHookUrl(): string
    {
        $params = [
            'context' => $this->getContext(),
            'platform' => config('brand.active'),
        ];

        // if context is not set, do not return the url
        return $this->getContext() ? route('platform.webhook', $params) : '';
    }

    /**
     * Determines whether the configuration entry is system specific.
     *
     * @param string $name Configuration entry name.
     *
     * @return bool
     */
    public function isSystemSpecific($name): bool
    {
        return !\in_array(
            $name,
            [
                'maxStartedTasksLimit',
                'taskRunnerWakeupDelay',
                'taskRunnerMaxAliveTime',
                'maxTaskExecutionRetries',
                'maxTaskInactivityPeriod',
                'taskRunnerStatus',
                'syncRequestTimeout',
                'asyncRequestTimeout',
                'asyncRequestWithProgress',
                'defaultLoggerEnabled',
                'schedulerTimeThreshold',
            ],
            true
        );
    }

    /**
     * Returns async process starter url, always in http.
     *
     * @param string $guid Process identifier.
     *
     * @return string Formatted URL of async process starter endpoint.
     */
    public function getAsyncProcessUrl($guid): string
    {
        return route('async', ['guid' => $guid, 'XDEBUG_SESSION_START'=>'debug']);
    }

    /**
     * Returns tenant entity.
     *
     * @return Tenant|null
     */
    abstract public function getTenant(): ?Tenant;

    /**
     * Sets order status notification mapping.
     *
     * Expected mapping format:
     *
     * [
     *      'shipped' => true,
     *      'transit' => false,
     *      ...
     * ]
     *
     * Keys in submitted array are order statuses available on Packlink.
     * Values are whether the customer should be notified when the order transitions into that status.
     *
     * @param array $notifications As described above.
     */
    public function setOrderStatusNotifications(array $notifications)
    {
        $this->saveConfigValue('orderStatusNotifications', $notifications);
    }

    /**
     * Retrieves order status notification mapping.
     *
     * @return array | null  Order status notification mapping configuration.
     */
    public function getOrderStatusNotifications(): ?array
    {
        return $this->getConfigValue('orderStatusNotifications');
    }
}
