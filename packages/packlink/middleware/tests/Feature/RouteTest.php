<?php

namespace Packlink\Middleware\Tests\Feature;

use Orchestra\Testbench\TestCase;
use Packlink\Middleware\PacklinkMiddlewareServiceProvider;

class RouteTest extends TestCase
{
    public function testHttpRequest()
    {
        $response = $this->get('test');
        $response->assertStatus(200);
        $response->assertSee('Test page');
    }

    protected function getPackageProviders($app): array
    {
        return [
            PacklinkMiddlewareServiceProvider::class,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        include __DIR__ . '/../../src/routes/test.php';
    }
}