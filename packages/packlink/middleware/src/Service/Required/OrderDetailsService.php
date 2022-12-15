<?php

namespace Packlink\Middleware\Service\Required;

use Packlink\Middleware\Entity\Order\Page;
use Packlink\Middleware\Entity\Order\Shop\Order;

interface OrderDetailsService
{
    /**
     * Provides page containing basic shop order information.
     *
     * @param string | int $page
     * @param int $limit
     *
     * @return \Packlink\Middleware\Entity\Order\Page
     */
    public function list($page, int $limit = 10): Page;

    /**
     * Provides orders identified by id containing basic shop order information.
     *
     * @param array ids
     *
     * @return \Packlink\Middleware\Entity\Order\Shop\Order[]
     */
    public function getByIds(array $ids): array;

    /**
     * Provides basic order information for an order.
     *
     * @param int $orderId
     *
     * @return \Packlink\Middleware\Entity\Order\Shop\Order|null
     */
    public function get(int $orderId): ?Order;

    /**
     * Provides system order count.
     *
     * @return int
     */
    public function count(): int;
}