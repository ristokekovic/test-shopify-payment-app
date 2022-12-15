<?php

namespace Packlink\Middleware\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Exceptions\MaintenanceModeException;
use Illuminate\Http\Request;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\Middleware\Service\Required\MaintenanceModeService as MaintenanceModeServiceInterface;

/**
 * Class CheckMaintenanceMode
 *
 * @package Packlink\Middleware\Http\Middleware
 */
class CheckMaintenanceMode
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
        if ($request->has('locale')) {
            $locale = explode('-', $request->get('locale'));
            \App::setLocale($locale[0]);
        }

        if ($this->isMaintenanceModeEnabled()) {
            throw new MaintenanceModeException(time());
        }

        return $next($request);
    }

    /**
     * Returns whether the maintenance mode has been enabled in the app.
     *
     * @return bool
     */
    private function isMaintenanceModeEnabled(): bool
    {
        $maintenanceModeService = ServiceRegister::getService(MaintenanceModeServiceInterface::CLASS_NAME);

        return $maintenanceModeService->getStatus();
    }
}
