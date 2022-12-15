<?php

namespace Packlink\Middleware\BrandDetection;


use Packlink\Middleware\BrandDetection\BrandDetectors\BrandDetector;
use Packlink\Middleware\BrandDetection\BrandDetectors\NullDetector;

/**
 * Class BrandDetectionRegistry
 *
 * @package Packlink\Middleware\BrandDetection
 */
class BrandDetectionRegistry
{
    /**
     * @var array
     */
    private static $detectorRegister;

    /**
     * Registers brand detector for specified key.
     *
     * @param string $key
     * @param BrandDetector $detector
     */
    public static function register(string $key, BrandDetector $detector): void
    {
        static::$detectorRegister[$key] = $detector;
    }

    /**
     * Gets brand detector for specified key.
     *
     * @param string $key
     *
     * @return BrandDetector
     */
    public static function get(string $key): BrandDetector
    {
        if (empty(static::$detectorRegister[$key])) {
            return new NullDetector();
        }

        return static::$detectorRegister[$key];
    }
}