<?php


namespace Packlink\Middleware\BrandDetection\BrandDetectors;

/**
 * Class NullDetector
 *
 * @package Packlink\Middleware\BrandDetection\BrandDetectors
 */
class NullDetector implements BrandDetector
{
    /**
     * @inheritDoc
     */
    public function detect($source): void
    {
    }
}