<?php


namespace Logeecom\Tests\Brands\Packlink;


use Logeecom\Tests\Infrastructure\Common\TestServiceRegister;
use Packlink\Brands\Packlink\PacklinkConfigurationService;
use Packlink\BusinessLogic\Brand\BrandConfigurationService;

class BaseTestWithServices extends \Logeecom\Tests\BusinessLogic\Common\BaseTestWithServices
{
    protected function setUp()
    {
        parent::setUp();

        TestServiceRegister::registerService(
            BrandConfigurationService::CLASS_NAME,
            function () {
                return new PacklinkConfigurationService();
            });
    }
}