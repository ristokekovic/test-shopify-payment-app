<?php

namespace Packlink\Middleware\Service\Required;

/**
 * Interface MaintenanceModeService
 *
 * @package Packlink\Middleware\Service\Required
 */
interface MaintenanceModeService
{
    /**
     * Class name.
     */
    public const CLASS_NAME = __CLASS__;

    /**
     * Returns maintenance mode status.
     *
     * @return bool
     */
    public function getStatus(): bool;

    /**
     * Sets maintenance mode status.
     *
     * @param bool $status
     *
     * @return bool Result of the operation
     */
    public function setStatus(bool $status): bool;
}
