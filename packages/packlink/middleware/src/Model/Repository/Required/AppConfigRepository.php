<?php

namespace Packlink\Middleware\Model\Repository\Required;

/**
 * Interface AppConfigRepository
 *
 * @package Packlink\Middleware\Model\Repository\Required
 */
interface AppConfigRepository
{
    /**
     * Sets config value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return bool Result of the operation.
     */
    public function set(string $key, $value): bool;

    /**
     * Retrieves config value.
     *
     * @param string $key
     *
     * @return mixed | null
     */
    public function get(string $key);
}
