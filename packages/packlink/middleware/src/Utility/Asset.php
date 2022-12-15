<?php

namespace Packlink\Middleware\Utility;

use Logeecom\Infrastructure\ServiceRegister;
use Packlink\BusinessLogic\Brand\BrandConfigurationService;

/**
 * Class Asset
 *
 * @package Packlink\Middleware\Utility
 */
class Asset
{
    /**
     * Retrieves asset url
     *
     * @param string $asset
     *
     * @return string
     */
    public static function getAssetUrl(string $asset): string
    {
        return rtrim(config('app.url'), '/')
            . '/'
            . ltrim($asset, '/')
            . '?'
            . http_build_query(['v' => config('app.asset_version')]);
    }

    /**
     * Retrieves brand-specific url
     *
     * @param string $asset
     *
     * @return string
     */
    public static function getBrandAssetUrl(string $asset): string
    {
        /** @var BrandConfigurationService $brandConfigurationService */
        $brandConfigurationService = ServiceRegister::getService(BrandConfigurationService::class);
        $brandConfiguration = $brandConfigurationService->get();

        return rtrim(config('app.url'), '/')
            . '/'
            . $brandConfiguration->platformCode
            . '/'
            . ltrim($asset, '/')
            . '?'
            . http_build_query(['v' => config('app.asset_version')]);
    }
}