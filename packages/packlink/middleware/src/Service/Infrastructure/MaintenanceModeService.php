<?php

namespace Packlink\Middleware\Service\Infrastructure;

use Packlink\BusinessLogic\BaseService;
use Packlink\Middleware\Model\Repository\AppConfigRepository;
use Packlink\Middleware\Service\Required\MaintenanceModeService as MaintenanceModeServiceInterface;

/**
 * Class MaintenanceModeService
 *
 * @package Packlink\Middleware\Service\BusinessLogic
 */
class MaintenanceModeService extends BaseService implements MaintenanceModeServiceInterface
{
    /**
     * Maintenance mode key used in app config table.
     */
    protected const MAINTENANCE_MODE_KEY = 'maintenanceMode';
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Returns maintenance mode status.
     *
     * @return bool
     */
    public function getStatus(): bool
    {
        $repository = new AppConfigRepository();

        return (bool)$repository->get(static::MAINTENANCE_MODE_KEY);
    }

    /**
     * Sets maintenance mode status.
     *
     * @param bool $status
     *
     * @return bool Result of the operation
     */
    public function setStatus(bool $status): bool
    {
        $repository = new AppConfigRepository();

        return $repository->set(static::MAINTENANCE_MODE_KEY, $status);
    }
}
