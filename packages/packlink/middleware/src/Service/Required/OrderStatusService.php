<?php

namespace Packlink\Middleware\Service\Required;

interface OrderStatusService
{
    /**
     * Provides system system specific order statuses.
     *
     * @return array [ 'status_value_1' => 'Status label 1', 'status_value_2' => 'Status label 2', ...]
     */
    public function getSystemStatuses(): array;

    /**
     * Return the list of packlink statuses that are mappable.
     *
     * @return array
     */
    public function getPacklinkStatuses(): array;
}