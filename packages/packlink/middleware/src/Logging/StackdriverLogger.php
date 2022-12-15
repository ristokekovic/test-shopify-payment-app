<?php

namespace Packlink\Middleware\Logging;

use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;

/**
 * Class StackdriverLogger.
 *
 * @package Packlink\Middleware\Logging
 */
class StackdriverLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param array $config
     *
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logName = $config['logName'] ?? 'app';
        $psrLogger = LoggingClient::psrBatchLogger(
            $logName,
            ['clientConfig' => ['projectId' => config('services.gcloud.project_name')]]
        );

        $handler = new PsrHandler($psrLogger);

        return new Logger($logName, [$handler]);
    }
}
