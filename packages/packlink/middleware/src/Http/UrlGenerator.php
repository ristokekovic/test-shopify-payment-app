<?php

namespace Packlink\Middleware\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;

/**
 * Class UrlGenerator. Overrides default Laravel URL generator to append debug information if needed.
 *
 * @package Packlink\Middleware\Http
 */
class UrlGenerator extends \Illuminate\Routing\UrlGenerator
{
    /** @noinspection SenselessProxyMethodInspection */
    /**
     * Create a new URL Generator instance.
     *
     * @param \Illuminate\Routing\RouteCollection $routes
     * @param \Illuminate\Http\Request $request
     * @param string $assetRoot
     *
     * @return void
     */
    public function __construct(RouteCollection $routes, Request $request, $assetRoot = null)
    {
        parent::__construct($routes, $request, $assetRoot);
    }

    /**
     * Get the base URL for the request. Uses URL from configuration in case of debug mode.
     *
     * @param string $scheme
     * @param string $root
     *
     * @return string
     */
    public function formatRoot($scheme, $root = null): string
    {
        if (config('app.debug')) {
            return rtrim(config('app.url'), '/');
        }

        return parent::formatRoot($scheme, $root);
    }
}
