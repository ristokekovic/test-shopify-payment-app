<?php


namespace Packlink\Middleware\BrandDetection\BrandDetectors;

use Logeecom\Infrastructure\Exceptions\BaseException;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Logeecom\Infrastructure\ORM\Interfaces\QueueItemRepository;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\TaskExecution\QueueItemStarter;

/**
 * Class QueueItemStarterDetector
 *
 * @package Packlink\Middleware\BrandDetection\BrandDetectors
 */
class QueueItemStarterDetector implements BrandDetector
{
    /**
     * @var QueueItemRepository
     */
    protected $queueItemRepository;

    /**
     * Detects brand from passed $source parameter
     * and sets brand.active configuration value.
     *
     * @param QueueItemStarter $source
     */
    public function detect($source): void
    {
        $queueItemId = $source->getQueueItemId();
        try {
            $brand = $this->getQueueItemRepository()->fetchQueueItemBrandById($queueItemId);
        } catch (BaseException $e) {
            Logger::logError('Failed to fetch QueueItem\'s brand because: ' . $e->getMessage());
            $brand = '';
        }

        config()->set('brand.active', $brand);
    }

    /**
     * @return QueueItemRepository
     *
     * @throws RepositoryClassException
     * @throws RepositoryNotRegisteredException
     */
    protected function getQueueItemRepository(): QueueItemRepository
    {
        if ($this->queueItemRepository === null) {
            $this->queueItemRepository = RepositoryRegistry::getQueueItemRepository();
        }

        return $this->queueItemRepository;
    }
}