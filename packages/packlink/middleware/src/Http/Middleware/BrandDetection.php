<?php

namespace Packlink\Middleware\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Packlink\Shopify\Exceptions\BrandNotSupportedException;

/**
 * Class BrandDetection
 *
 * @package Packlink\Middleware\Http\Middleware
 */
class BrandDetection
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     *
     * @throws BrandNotSupportedException
     */
    public function handle(Request $request, Closure $next)
    {
        $platform = strtoupper($request->route('platform'));

        $brandConfiguration = array_keys(config("brand.available"));

        if (!in_array($platform, $brandConfiguration, true)) {
            throw new BrandNotSupportedException($platform);
        }

        config()->set('brand.active', !empty($platform) ? $platform : null);

        return $next($request);
    }
}
