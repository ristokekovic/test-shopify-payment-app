<?php

namespace Logeecom\Tests\BusinessLogic\Common\TestComponents\Locations;

use Packlink\BusinessLogic\Location\LocationService;
use RuntimeException;

class MockLocationService extends LocationService
{
    protected static $instance;
    public $shouldFail = false;
    public $failMessage = 'This is test failure.';
    public $callHistory = array();
    public $searchLocationsResult = array();

    public function searchLocations($country, $query)
    {
        $this->callHistory[] = array('searchLocations' => array($country, $query));

        if ($this->shouldFail) {
            throw new RuntimeException($this->failMessage);
        }

        return $this->searchLocationsResult;
    }
}