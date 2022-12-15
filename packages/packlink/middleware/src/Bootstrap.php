<?php

namespace Packlink\Middleware;

use Logeecom\Infrastructure\Configuration\ConfigEntity;
use Logeecom\Infrastructure\Http\AsyncSocketHttpClient;
use Logeecom\Infrastructure\Http\HttpClient;
use Logeecom\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\ServiceRegister;
use Logeecom\Infrastructure\TaskExecution\Process;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Packlink\BusinessLogic\BootstrapComponent;
use Packlink\BusinessLogic\Scheduler\Models\Schedule;
use Packlink\Middleware\Model\Repository\BaseRepository;
use Packlink\Middleware\Model\Repository\QueueItemRepository;
use Packlink\Middleware\Service\BusinessLogic\TenantService;
use Packlink\Middleware\Service\Infrastructure\LoggerService;
use Packlink\Middleware\Service\Required\MaintenanceModeService as MaintenanceModeServiceInterface;
use Packlink\Middleware\Service\Infrastructure\MaintenanceModeService;
use Packlink\Shopify\Model\Repository\ConfigRepository;

/**
 * Class Bootstrap
 *
 * @package Packlink\Middleware
 */
class Bootstrap extends BootstrapComponent
{
    /**
     * @inheritDoc
     */
    protected static function initServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(
            ShopLoggerAdapter::class,
            static function () {
                return LoggerService::getInstance();
            }
        );

        ServiceRegister::registerService(
            HttpClient::CLASS_NAME,
            function () {
                return new AsyncSocketHttpClient();
            }
        );

        ServiceRegister::registerService(
            TenantService::CLASS_NAME,
            static function () {
                return TenantService::getInstance();
            }
        );

        ServiceRegister::registerService(
            MaintenanceModeServiceInterface::CLASS_NAME,
            static function () {
                return MaintenanceModeService::getInstance();
            }
        );
    }

    /**
     * Initializes repositories.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryClassException
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();
    }
}
