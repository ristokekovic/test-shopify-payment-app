<?php

namespace Packlink\Middleware\Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Schema;
use Logeecom\Infrastructure\Serializer\Concrete\JsonSerializer;
use Logeecom\Infrastructure\Serializer\Serializer;
use Logeecom\Infrastructure\Utility\TimeProvider;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Logeecom\Tests\Infrastructure\Common\TestServiceRegister;
use Logeecom\Tests\Infrastructure\ORM\AbstractGenericQueueItemRepositoryTest;
use Packlink\Middleware\Tests\CreatesApplication;
use Packlink\Middleware\Tests\Unit\Repository\TestQueueItemRepository;

/**
 * Class GenericQueueItemRepositoryTest
 *
 * @package Packlink\Middleware\Tests\Unit
 */
class GenericQueueItemRepositoryTest extends AbstractGenericQueueItemRepositoryTest
{
    use DatabaseMigrations, CreatesApplication;

    /**
     * @inheritdoc
     */
    public static function tearDownAfterClass()
    {
        Schema::dropIfExists(TestQueueItemRepository::TABLE_NAME);
    }

    /**
     * @return string
     */
    public function getQueueItemEntityRepositoryClass(): string
    {
        return TestQueueItemRepository::class;
    }

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->createApplication();

        parent::setUp();

        TestQueueItemRepository::createTestEntityTable();

        new TestServiceRegister(
            [
                TimeProvider::class => function () {
                    return new TestTimeProvider();
                },
                Serializer::class => function () {
                    return new JsonSerializer();
                },
            ]
        );
    }

    /**
     * Cleans up all storage services used by repositories
     */
    public function cleanUpStorage()
    {
        $this->tearDownAfterClass();
    }
}
