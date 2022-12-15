<?php

namespace Packlink\Middleware\Tests\Unit\Service;

use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\Logger\Interfaces\DefaultLoggerAdapter;
use Logeecom\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\Serializer\Concrete\JsonSerializer;
use Logeecom\Infrastructure\Serializer\Serializer;
use Logeecom\Infrastructure\ServiceRegister;
use Logeecom\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;
use Logeecom\Infrastructure\TaskExecution\QueueService;
use Logeecom\Infrastructure\Utility\Events\EventBus;
use Logeecom\Infrastructure\Utility\TimeProvider;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Logger\TestDefaultLogger;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Logger\TestShopLogger;
use Logeecom\Tests\Infrastructure\Common\TestComponents\ORM\MemoryRepository;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\FooTask;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestQueueService;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TaskExecution\TestTaskRunnerWakeupService;
use Logeecom\Tests\Infrastructure\Common\TestComponents\TestShopConfiguration;
use Logeecom\Tests\Infrastructure\Common\TestComponents\Utility\TestTimeProvider;
use Logeecom\Tests\Infrastructure\Common\TestServiceRegister;
use Packlink\BusinessLogic\Scheduler\Models\Schedule;
use Packlink\BusinessLogic\Scheduler\Models\WeeklySchedule;
use Packlink\Middleware\Service\BusinessLogic\TenantService;
use PHPUnit\Framework\TestCase;

class TenantServiceTest extends TestCase
{
    /** @var Configuration */
    private $configService;

    /**
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryClassException
     */
    protected function setUp(): void
    {
        parent::setUp();

        RepositoryRegistry::registerRepository(Schedule::CLASS_NAME, MemoryRepository::getClassName());
        $timeProvider = new TestTimeProvider();
        $queue = new TestQueueService();
        $shopLogger = new TestShopLogger();
        $shopConfiguration = new TestShopConfiguration();
        $serializer = new JsonSerializer();

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
                TenantService::class => function () {
                    return TenantService::getInstance();
                },
            ]
        );

        $this->configService = $shopConfiguration;

        // Initialize logger component with new set of log adapters
        Logger::resetInstance();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Logger::resetInstance();
        TenantService::resetInstance();
    }

    /**
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testDeleteTenantSchedules(): void
    {
        $this->configService->setContext('text-context');

        /** @var TenantService $service */
        $service = ServiceRegister::getService(TenantService::CLASS_NAME);

        $repository = RepositoryRegistry::getRepository(Schedule::CLASS_NAME);
        $initialCount = $repository->count();

        $schedule = new WeeklySchedule(new FooTask(), 'test-queue', $this->configService->getContext());
        $repository->save($schedule);

        $this->assertEquals($initialCount + 1, $repository->count());

        $service->deleteTenantSpecificData();

        $this->assertEquals($initialCount, $repository->count());

        $schedule = new WeeklySchedule(new FooTask(), 'test-queue', 'other-context');
        $repository->save($schedule);
        $schedule = new WeeklySchedule(new FooTask(), 'test-queue', $this->configService->getContext());
        $repository->save($schedule);

        $this->assertEquals($initialCount + 2, $repository->count());

        $service->deleteTenantSpecificData();
        $this->assertEquals($initialCount + 1, $repository->count());
    }

    /**
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function testDeleteTenantSchedulesWithHugeDatabase(): void
    {
        $this->configService->setContext('text-context');

        /** @var TenantService $service */
        $service = ServiceRegister::getService(TenantService::CLASS_NAME);

        $repository = RepositoryRegistry::getRepository(Schedule::CLASS_NAME);
        $initialCount = $repository->count();

        // add tenant schedules
        $tenantScheduleCount = 540;
        for ($i = 0; $i < $tenantScheduleCount; $i++) {
            $schedule = new WeeklySchedule(new FooTask(), 'test-queue', $this->configService->getContext());
            $repository->save($schedule);
        }

        // add non-tenant schedules
        $nonTenantScheduleCount = 540;
        for ($i = 0; $i < $nonTenantScheduleCount; $i++) {
            $schedule = new WeeklySchedule(new FooTask(), 'test-queue', 'context-' . $i);
            $repository->save($schedule);
        }

        $nonTenantScheduleCount += $initialCount;

        $this->assertEquals($tenantScheduleCount + $nonTenantScheduleCount, $repository->count());

        $service->deleteTenantSpecificData();
        $this->assertEquals($nonTenantScheduleCount, $repository->count());
    }
}
