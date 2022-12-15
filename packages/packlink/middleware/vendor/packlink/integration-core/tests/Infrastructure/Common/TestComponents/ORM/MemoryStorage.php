<?php

namespace Logeecom\Tests\Infrastructure\Common\TestComponents\ORM;

use Logeecom\Infrastructure\ORM\Entity;

/**
 * Class MemoryStorage.
 *
 * @package Logeecom\Tests\Infrastructure\Common\TestComponents\ORM\Entity
 */
class MemoryStorage
{
    private static $incrementId = 1;

    /**
     * @var Entity[]
     */
    public static $storage = array();

    /**
     * @return int
     */
    public static function generateId()
    {
        return static::$incrementId++;
    }

    /**
     * Empties storage.
     */
    public static function reset()
    {
        static::$incrementId = 1;
        static::$storage = array();
    }
}
