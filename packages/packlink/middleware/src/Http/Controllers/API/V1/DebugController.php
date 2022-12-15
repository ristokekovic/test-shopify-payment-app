<?php

namespace Packlink\Middleware\Http\Controllers\API\V1;

use Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\TaskExecution\QueueItem;
use Packlink\BusinessLogic\ShippingMethod\Models\ShippingMethod;
use Packlink\Middleware\Http\Controllers\API\ApiController;
use Packlink\Middleware\Model\Repository\BaseRepository;
use Packlink\Middleware\Model\Repository\QueueItemRepository;
use Packlink\Shopify\Model\Repository\TenantSpecificRepository;

/**
 * Class DebugController
 *
 * @package Packlink\Middleware\Http\Controllers
 */
abstract class DebugController extends ApiController
{
    public const SYSTEM_INFO_FILE_NAME = 'packlink-debug-data.zip';
    protected const INTEGRATION_INFO_FILE_NAME = '';
    protected const USER_INFO_FILE_NAME = 'user-settings.json';
    protected const QUEUE_INFO_FILE_NAME = 'queue.json';
    protected const SERVICE_INFO_FILE_NAME = 'services.json';
    protected const TENANT_SPECIFIC_TABLE_FILE_NAME = 'tenant-specific-table.json';

    /**
     * Returns system info file.
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Exception
     */
    public function getSystemInfo()
    {
        $file = tempnam(sys_get_temp_dir(), 'packlink_system_info');

        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::CREATE);

        $zip->addFromString(static::QUEUE_INFO_FILE_NAME, $this->getQueue());
        $zip->addFromString(static::USER_INFO_FILE_NAME, $this->getUserSettings());
        $zip->addFromString(static::SERVICE_INFO_FILE_NAME, $this->getServicesInfo());
        $zip->addFromString(static::TENANT_SPECIFIC_TABLE_FILE_NAME, $this->getTenantEntityTable());

        $integrationInfo = $this->getIntegrationInfo();

        if (!empty($integrationInfo)) {
            $zip->addFromString(static::INTEGRATION_INFO_FILE_NAME, $integrationInfo);
        }

        $zip->close();

        return response()->download($file, self::SYSTEM_INFO_FILE_NAME);
    }

    /**
     * Returns integration specific information.
     *
     * @return string
     */
    protected function getIntegrationInfo(): string
    {
        return '';
    }

    /**
     * Returns parcel and warehouse information.
     *
     * @return string
     */
    protected function getUserSettings(): string
    {
        $result = array();
        /** @noinspection NullPointerExceptionInspection */
        $result['User'] = $this->getConfigService()->getUserInfo()->toArray();
        $result['User']['API key'] = $this->getConfigService()->getAuthorizationToken();
        $result['Default parcel'] = $this->getConfigService()->getDefaultParcel() ?: array();
        $result['Default warehouse'] = $this->getConfigService()->getDefaultWarehouse() ?: array();
        $result['Order status mappings'] = $this->getConfigService()->getOrderStatusMappings() ?: array();

        return $this->jsonEncode($result);
    }

    /**
     * Returns service info.
     *
     * @return string
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    protected function getServicesInfo(): string
    {
        $result = [];

        try {
            /** @var BaseRepository $repository */
            $repository = RepositoryRegistry::getRepository(ShippingMethod::CLASS_NAME);
            $result = $repository->select();
        } catch (RepositoryNotRegisteredException $e) {
        }

        return $this->formatJsonOutput($result);
    }

    /**
     * Returns current queue for current tenant.
     *
     * @return string
     */
    protected function getQueue(): string
    {
        $result = [];

        try {
            /** @var QueueItemRepository $repository */
            $repository = RepositoryRegistry::getRepository(QueueItem::CLASS_NAME);

            $query = new QueryFilter();
            $query->where('context', Operators::EQUALS, $this->getConfigService()->getContext());

            $result = $repository->select($query);
        } catch (RepositoryNotRegisteredException $e) {
        } catch (QueryFilterInvalidParamException $e) {
        }

        return $this->formatJsonOutput($result);
    }

    /**
     * Returns all records from Packlink tenant specific entity table.
     *
     * @return string
     */
    protected function getTenantEntityTable(): string
    {
        $repository = new TenantSpecificRepository();

        return $this->jsonEncode(json_decode($repository->encodeAllEntities(), true));
    }

    /**
     * Encodes the given data.
     *
     * @param $data
     *
     * @return string
     */
    protected function jsonEncode($data): string
    {
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Formats json output.
     *
     * @param array $items
     *
     * @return string
     */
    private function formatJsonOutput(array &$items): string
    {
        $response = array();
        foreach ($items as $item) {
            $response[] = $item->toArray();
        }

        return $this->jsonEncode($response);
    }
}
