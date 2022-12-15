<?php

namespace Packlink\Middleware\Http\Controllers;

use Illuminate\Routing\Controller as LaravelController;
use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\Middleware\Service\BusinessLogic\ConfigurationService;

/**
 * Class BaseController
 *
 * @package Packlink\Middleware\Http\Controllers
 */
class BaseController extends LaravelController
{
    /**
     * @var ConfigurationService
     */
    protected $configService;

    /**
     * Returns an instance of configuration service.
     *
     * @return ConfigurationService
     */
    protected function getConfigService(): ConfigurationService
    {
        if ($this->configService === null) {
            $this->configService = ServiceRegister::getService(Configuration::class);
        }

        return $this->configService;
    }
}