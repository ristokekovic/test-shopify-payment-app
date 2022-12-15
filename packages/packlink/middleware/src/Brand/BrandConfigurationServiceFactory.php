<?php

namespace Packlink\Middleware\Brand;

use Packlink\BusinessLogic\Brand\BrandConfigurationService;

/**
 * Interface BrandConfigurationServiceFactory
 *
 * @package Packlink\Middleware\Brand
 */
interface BrandConfigurationServiceFactory
{
    /**
     * Returns BrandConfigurationService based on brand.active config value.
     *
     * @return BrandConfigurationService
     */
    public function getBrandConfigurationService(): BrandConfigurationService;
}