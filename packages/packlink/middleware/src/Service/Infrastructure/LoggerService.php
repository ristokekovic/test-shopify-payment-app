<?php

namespace Packlink\Middleware\Service\Infrastructure;

use Illuminate\Support\Facades\Log;
use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Logeecom\Infrastructure\Logger\LogData;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ServiceRegister;
use Logeecom\Infrastructure\Singleton;

/**
 * Class LoggerService
 *
 * @package Packlink\Middleware\Service\Infrastructure
 */
class LoggerService extends Singleton implements ShopLoggerAdapter
{
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * @inheritDoc
     */
    public function logMessage(LogData $data): void
    {
        /** @var \Packlink\Middleware\Service\BusinessLogic\ConfigurationService $configService */
        $configService = ServiceRegister::getService(Configuration::class);
        $minLogLevel = $configService->getMinLogLevel();
        $logLevel = $data->getLogLevel();

        if ($logLevel > $minLogLevel && !$configService->isDebugModeEnabled()) {
            return;
        }

        $logMessage = $data->getMessage();

        $context = $data->getContext();

        if (!empty($context)) {
            $contextData = array();
            foreach ($context as $item) {
                $contextData[$item->getName()] = print_r($item->getValue(), true);
            }

            $logMessage .= PHP_EOL . 'Context data: ' . print_r($contextData, true);
        }

        switch ($logLevel) {
            case Logger::ERROR:
                Log::error($logMessage);
                break;
            case Logger::WARNING:
                Log::warning($logMessage);
                break;
            case Logger::DEBUG:
                Log::debug($logMessage);
                break;
            default:
                Log::info($logMessage);
        }
    }
}
