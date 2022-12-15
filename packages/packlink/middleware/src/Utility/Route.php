<?php

namespace Packlink\Middleware\Utility;

class Route
{
    /**
     * Generates url to a named route.
     *
     * @param string $name
     * @param array $parameters
     *
     * @return string
     */
    public static function to(string $name, array $parameters = []): string
    {
        return rtrim(config('app.url'), '/') . '/' . ltrim(route($name, $parameters, false), '/');
    }
}