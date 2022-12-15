<?php

namespace Packlink\Middleware\Tests\Unit;

use Logeecom\Infrastructure\Configuration\ConfigEntity;
use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Logeecom\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\Serializer\Concrete\JsonSerializer;
use Logeecom\Infrastructure\Serializer\Serializer;
use Logeecom\Infrastructure\TaskExecution\Interfaces\Priority;
use Logeecom\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Logeecom\Infrastructure\TaskExecution\QueueService;
use Logeecom\Infrastructure\Utility\Events\EventBus;
use Logeecom\Infrastructure\Utility\TimeProvider;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Logeecom\Tests\Infrastructure\Common\TestComponents\ORM\MemoryQueueItemRepository;
use Logeecom\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Logeecom\Tests\Infrastructure\Common\TestServiceRegister;
use PHPUnit\Framework\TestCase;

/**
 * Class MultipleQueuesTest
 *
 * @package Packlink\Middleware\Tests\Unit
 */
class MultipleQueuesTest extends TestCase
{
    /** @var \Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService */
    public $queue;
    /** @var MemoryQueueItemRepository */
    public $queueStorage;
    /** @var TestTimeProvider */
    public $timeProvider;
    /** @var \Logeecom\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger */
    public $logger;
    /** @var Configuration */
    public $shopConfiguration;

    /**
     * @throws \Exception
     */
    public function setUp()
    {
        RepositoryRegistry::registerRepository(QueueItem::CLASS_NAME, MemoryQueueItemRepository::getClassName());
        RepositoryRegistry::registerRepository(ConfigEntity::CLASS_NAME, MemoryRepository::getClassName());

        $timeProvider = new TestTimeProvider();
        $queue = new TestQueueService();
        $shopLogger = new TestShopLogger();
        $shopConfiguration = new TestShopConfiguration();
        $serializer = new JsonSerializer();
        $shopConfiguration->setIntegrationName('Shop1');

        new TestServiceRegister(
            [
                TimeProvider::class => function () use ($timeProvider) {
                    return $timeProvider;
                },
                TaskRunnerWakeup::class => function () {
                    return new TestTaskRunnerWakeupService();
                },
                QueueService::class => function () use ($queue) {
                    return $queue;
                },
                EventBus::class => function () {
                    return EventBus::getInstance();
                },
                DefaultLoggerAdapter::class => function () {
                    return new TestDefaultLogger();
                },
                ShopLoggerAdapter::class => function () use ($shopLogger) {
                    return $shopLogger;
                },
                Configuration::class => function () use ($shopConfiguration) {
                    return $shopConfiguration;
                },
                Serializer::class => function () use ($serializer) {
                    return $serializer;
                },
            ]
        );

        // Initialize logger component with new set of log adapters
        Logger::resetInstance();

        $this->queueStorage = RepositoryRegistry::getQueueItemRepository();
        $this->timeProvider = $timeProvider;
        $this->queue = $queue;
        $this->logger = $shopLogger;
        $this->shopConfiguration = $shopConfiguration;
    }

    /**
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testMultipleQueuesInOrder(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $this->queue->enqueue(
                'test' . $i % 6,
                new FooTask(),
                Priority::NORMAL
            );
        }

        $oldestItems = $this->queueStorage->findOldestQueuedItems(Priority::NORMAL, 5);
        self::assertEquals($oldestItems[0]->getQueueName(), 'test1');
        self::assertEquals($oldestItems[1]->getQueueName(), 'test2');
        self::assertEquals($oldestItems[2]->getQueueName(), 'test3');
        self::assertEquals($oldestItems[3]->getQueueName(), 'test4');
        self::assertEquals($oldestItems[4]->getQueueName(), 'test5');
    }

    /**
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\EntityClassException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException
     */
    public function testMultipleQueuesMixedOrder(): void
    {
        $seconds = 30;
        for ($i = 1; $i <= 5; $i++) {
            for ($j = 1; $j <= 3; $j++) {
                $this->timeProvider->setCurrentLocalTime(
                    \DateTime::createFromFormat('Y-m-d H:i:s', '2019-03-25 16:47:' . $seconds++)
                );
                $this->queue->enqueue(
                    'test' . $i,
                    new FooTask(),
                    Priority::NORMAL
                );
            }
        }

        $oldestItems = $this->queueStorage->findOldestQueuedItems(Priority::NORMAL, 5);
        self::assertEquals($oldestItems[0]->getQueueName(), 'test1');
        self::assertEquals($oldestItems[1]->getQueueName(), 'test2');
        self::assertEquals($oldestItems[2]->getQueueName(), 'test3');
        self::assertEquals($oldestItems[3]->getQueueName(), 'test4');
        self::assertEquals($oldestItems[4]->getQueueName(), 'test5');
    }
}
