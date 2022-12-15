<?php

namespace Packlink\Middleware\BrandDetection\BrandDetectors;

/**
 * Interface BrandDetector
 *
 * @package Packlink\Middleware\BrandDetection\BrandDetectors
 */
interface BrandDetector
{
    /**
     * Detects brand from passed $source parameter
     * and sets brand.active configuration value.
     *
     * @param $source
     */
    public function detect($source): void;
}