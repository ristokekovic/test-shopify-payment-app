<?php

namespace Packlink\Middleware\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Support\Facades\App;
use Logeecom\Infrastructure\Configuration\Configuration;

class InitApiLocale
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = $_SERVER['HTTP_X_PACKLINK_LOCALE'] ?? 'en';
        $locale = in_array($locale, ['en', 'fr', 'de', 'es', 'it'], true) ? $locale : 'en';
        App::setLocale($locale);
        Configuration::setUICountryCode($locale);

        return $next($request);
    }
}