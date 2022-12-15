<?php


namespace Packlink\Middleware\Service\Infrastructure;

use Exception;
use Logeecom\Infrastructure\Logger\LogContextData;
use Logeecom\Infrastructure\Logger\Logger;
use Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\TaskExecution\AsyncProcessStarterService as BaseAsyncProcessStarterService;
use Logeecom\Infrastructure\TaskExecution\Process;
use Packlink\Middleware\BrandDetection\BrandDetectionRegistry;

/**
 * Class AsyncProcessStarterService
 *
 * @package Packlink\Middleware\Service\Infrastructure
 */
class AsyncProcessStarterService extends BaseAsyncProcessStarterService
{
    /**
     * Process entity repository.
     *
     * @var RepositoryInterface
     */
    private $processRepository;

    /**
     * AsyncProcessStarterService constructor.
     *
     * @throws RepositoryNotRegisteredException
     */
    public function __construct()
    {
        parent::__construct();

        $this->processRepository = RepositoryRegistry::getRepository(Process::CLASS_NAME);
    }

    /**
     * Runs a process with provided identifier.
     *
     * @param string $guid Identifier of process.
     */
    public function runProcess($guid): void
    {
        try {
            $filter = new QueryFilter();
            $filter->where('guid', '=', $guid);

            /** @var Process $process */
            $process = $this->processRepository->selectOne($filter);
            if ($process !== null) {
                $brandDetector = BrandDetectionRegistry::get(get_class($process->getRunner()));
                $brandDetector->detect($process->getRunner());
                $process->getRunner()->run();
                $this->processRepository->delete($process);
            }
        } catch (Exception $e) {
            Logger::logError($e->getMessage(), 'Core', [new LogContextData('guid', $guid)]);
        }
    }
}