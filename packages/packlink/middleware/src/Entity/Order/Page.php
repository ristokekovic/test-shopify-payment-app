<?php

namespace Packlink\Middleware\Entity\Order;

use Logeecom\Infrastructure\Data\DataTransferObject;
use Logeecom\Infrastructure\Data\Transformer;

class Page extends DataTransferObject
{
    /**
     * @var string | int
     */
    protected $previous;
    /**
     * @var string | int
     */
    protected $next;
    /**
     * @var \Packlink\Middleware\Entity\Order\Order[]
     */
    protected $orders;

    /**
     * @return int|string
     */
    public function getPrevious()
    {
        return $this->previous;
    }

    /**
     * @param int|string $previous
     */
    public function setPrevious($previous): void
    {
        $this->previous = $previous;
    }

    /**
     * @return int|string
     */
    public function getNext()
    {
        return $this->next;
    }

    /**
     * @param int|string $next
     */
    public function setNext($next): void
    {
        $this->next = $next;
    }

    /**
     * @return \Packlink\Middleware\Entity\Order\Order[]
     */
    public function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * @param \Packlink\Middleware\Entity\Order\Order[] $orders
     */
    public function setOrders(array $orders): void
    {
        $this->orders = $orders;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'previous' => $this->getPrevious(),
            'next' => $this->getNext(),
            'orders' => Transformer::batchTransform($this->getOrders()),
        ];
    }
}