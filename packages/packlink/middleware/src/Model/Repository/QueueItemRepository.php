<?php

namespace Packlink\Middleware\Model\Repository;

use Illuminate\Support\Facades\DB;
use Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Logeecom\Infrastructure\ORM\Interfaces\QueueItemRepository as QueueItemRepositoryInterface;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Packlink\Shopify\Model\Traits\BrandConditionalRead;
use Packlink\Shopify\Model\Traits\BrandWrite;

/**
 * Class QueueItemRepository
 *
 * @package Packlink\Middleware\Model\Repository
 */
class QueueItemRepository extends BaseRepository implements QueueItemRepositoryInterface
{
    use BrandWrite;
    use BrandConditionalRead;

    protected const TABLE_NAME = 'task_queue';
    protected const STATUS_INDEX = 'index_1';
    protected const QUEUE_NAME_INDEX = 'index_3';
    protected const PRIORITY_INDEX = 'index_8';

    /**
     * Finds list of earliest queued queue items per queue for given priority.
     * Following list of criteria for searching must be satisfied:
     *      - Queue must be without already running queue items
     *      - For one queue only one (oldest queued) item should be returned
     *      - Only queue items with given priority can be retrieved.
     *
     * @param int $priority Queue item priority.
     * @param int $limit Result set limit. By default max 10 earliest queue items will be returned
     *
     * @return QueueItem[] Found queue item list
     */
    public function findOldestQueuedItems($priority, $limit = 10)
    {
        $queuedItems = [];

        try {
            $runningQueueNames = $this->getRunningQueueNames();
            $queuedItems = $this->getQueuedItems($priority, $runningQueueNames, $limit);
        } catch (\Exception $e) {
            // In case of database exception return empty result set.
        }

        return $queuedItems;
    }

    /**
     * Creates or updates given queue item. If queue item id is not set, new queue item will be created otherwise
     * update will be performed.
     *
     * @param QueueItem $queueItem Item to save
     * @param array $additionalWhere List of key/value pairs that must be satisfied upon saving queue item. Key is
     *  queue item property and value is condition value for that property. Example for MySql storage:
     *  $storage->save($queueItem, array('status' => 'queued')) should produce query
     *  UPDATE queue_storage_table SET .... WHERE .... AND status => 'queued'
     *
     * @return int Id of saved queue item
     * @throws QueueItemSaveException if queue item could not be saved
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = array())
    {
        $savedItemId = null;

        try {
            $itemId = $queueItem->getId();
            if ($itemId === null || $itemId <= 0) {
                $savedItemId = $this->save($queueItem);
            } else {
                $this->updateQueueItem($queueItem, $additionalWhere);
            }
        } catch (\Exception $exception) {
            throw new QueueItemSaveException(
                'Failed to save queue item. Error: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        return $savedItemId ?: $itemId;
    }

    /**
     * Fetches brand of QueueItem.
     *
     * @param int $id
     *
     * @return string
     */
    public function fetchQueueItemBrandById(int $id): string
    {
        return (string)DB::table(static::TABLE_NAME)->where('id', $id)->value('brand');
    }

    /**
     * Returns names of queues containing items that are currently in progress.
     *
     * @return array Names of queues containing items that are currently in progress.
     */
    private function getRunningQueueNames(): array
    {
        // We are using raw DB queries
        // to avoid performance overhead introduced by ORM.
        $query = DB::table(static::TABLE_NAME);
        $query->select(self::QUEUE_NAME_INDEX)
            ->where(self::STATUS_INDEX, '=', QueueItem::IN_PROGRESS);

        $result = $query->get()->toArray();

        return array_column($result, self::QUEUE_NAME_INDEX);
    }

    /**
     * Returns all queued items.
     *
     * @param int $priority Queue item priority.
     * @param array $runningQueueNames Array of queues containing items that are currently in progress.
     * @param int $limit Maximum number of records that can be retrieved.
     *
     * @return array|\Logeecom\Infrastructure\ORM\Entity[]
     */
    private function getQueuedItems($priority, array $runningQueueNames, $limit): array
    {
        $baseQuery = DB::table(static::TABLE_NAME);
        $ids = $this->getQueueIdsForExecution($priority, $runningQueueNames, $limit);

        $records = $baseQuery->whereIn('id', $ids)
            ->orderBy('id')
            ->get()
            ->toArray();

        return !empty($records) ? $this->transformEntities($records) : [];
    }

    /**
     * Retrieves the list of queue item ids that can be executed.
     *
     * @param int $priority
     * @param array $runningQueueNames
     * @param int $limit
     *
     * @return array
     */
    private function getQueueIdsForExecution(int $priority, array $runningQueueNames, int $limit)
    {
        $query = DB::table(static::TABLE_NAME);
        $query->selectRaw('min(id) as id')
            ->where(self::PRIORITY_INDEX, '=', $priority)
            ->where(self::STATUS_INDEX, '=', QueueItem::QUEUED)
            ->groupBy([self::QUEUE_NAME_INDEX])
            ->limit($limit);

        if (!empty($runningQueueNames)) {
            $query->whereNotIn(self::QUEUE_NAME_INDEX, $runningQueueNames);
        }

        $result = $query->get()->toArray();

        return array_column($result, 'id');
    }

    /**
     * Updates queue item.
     *
     * @param QueueItem $queueItem Queue item entity.
     * @param array $additionalWhere Additional WHERE conditions.
     *
     * @throws QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException
     */
    private function updateQueueItem($queueItem, array $additionalWhere)
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $queueItem->getId());

        foreach ($additionalWhere as $name => $value) {
            if ($value === null) {
                $filter->where($name, Operators::NULL);
            } else {
                $filter->where($name, Operators::EQUALS, $value ?? '');
            }
        }

        /** @var QueueItem $item */
        $item = $this->selectOne($filter);
        if ($item === null) {
            throw new QueueItemSaveException("Can not update queue item with id {$queueItem->getId()} .");
        }

        $this->update($queueItem);
    }
}
