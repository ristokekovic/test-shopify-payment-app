<?php

namespace Packlink\Middleware\Service\BusinessLogic;

use Logeecom\Infrastructure\Configuration\ConfigEntity;
use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\Exceptions\BaseException;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\BusinessLogic\BaseService;
use Packlink\BusinessLogic\Scheduler\Models\Schedule;
use Packlink\Middleware\Model\Repository\BaseRepository;

/**
 * Class TenantService.
 *
 * @package Packlink\Middleware\Service\BusinessLogic
 */
class TenantService extends BaseService
{
    /**
     * Fully qualified name of this interface.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * Singleton instance of this class.
     *
     * @var static
     */
    protected static $instance;

    /**
     * Deletes tenant specific data from the global repository.
     */
    public function deleteTenantSpecificData(): void
    {
        /** @var Configuration $configService */
        $configService = ServiceRegister::getService(Configuration::class);
        $context = $configService->getContext();
        $this->deleteConfigEntities($context);
        $this->deleteQueueItems($context);
        $this->deleteSchedules($context);
    }

    /**
     * Deletes all context specific configuration entities from main table.
     *
     * @param string $context Current context.
     */
    private function deleteConfigEntities($context): void
    {
        try {
            /** @var BaseRepository $repository */
            $repository = RepositoryRegistry::getRepository(ConfigEntity::class);
            $filter = new QueryFilter();
            $filter->where('systemId', Operators::EQUALS, $context);

            $configEntities = $repository->select($filter);

            foreach ($configEntities as $configEntity) {
                $repository->delete($configEntity);
            }
        } catch (BaseException $e) {
            Logger::logWarning("Could not delete config entities for context $context. Error: " . $e->getMessage());
        }
    }

    /**
     * Deletes queue items for the given context.
     *
     * @param string $context
     */
    private function deleteQueueItems($context): void
    {
        try {
            $queueRepo = RepositoryRegistry::getQueueItemRepository();
            $filter = new QueryFilter();
            $filter->where('context', Operators::EQUALS, $context);
            $items = $queueRepo->select($filter);
            foreach ($items as $item) {
                $queueRepo->delete($item);
            }
        } catch (BaseException $e) {
            Logger::logWarning("Could not delete queue items for context $context. Error: " . $e->getMessage());
        }
    }

    /**
     * Deletes all schedules for the given context.
     *
     * @param string $context
     */
    private function deleteSchedules(string $context): void
    {
        try {
            // Schedule entity does not have an index on "context" field so we cannot search by that field.
            // So, we need to iterate through all schedules and delete the ones for the given context.
            // This query should change once the core Schedule entity has the context field indexed.
            $scheduleRepo = RepositoryRegistry::getRepository(Schedule::CLASS_NAME);

            $total = $scheduleRepo->count();
            $batchSize = 100;
            $page = 1;
            $itemsToDelete = [];
            do {
                $offset = ($page - 1) * $batchSize;
                $filter = new QueryFilter();
                $filter->setOffset($offset)->setLimit($batchSize);

                /** @var Schedule[] $items */
                $items = $scheduleRepo->select($filter);
                foreach ($items as $item) {
                    if ($item->getContext() === $context) {
                        $itemsToDelete[] = $item;
                    }
                }

                $page++;
            } while ($offset + $batchSize < $total);

            foreach ($itemsToDelete as $item) {
                $scheduleRepo->delete($item);
            }
        } catch (BaseException $e) {
            Logger::logWarning("Could not delete schedules for context $context. Error: " . $e->getMessage());
        }
    }
}
