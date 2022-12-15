<?php

namespace Packlink\Middleware\Entity;

use Logeecom\Infrastructure\ORM\Entity;

/**
 * Class Tenant
 *
 * @package Packlink\Middleware\Entity
 */
abstract class Tenant extends Entity
{
    /**
     * Returns full class name.
     *
     * @return string Fully qualified class name.
     */
    public static function getClassName(): string
    {
        return static::class;
    }
}
