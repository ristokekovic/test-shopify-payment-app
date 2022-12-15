<?php

namespace Packlink\Middleware\Entity\Order;

use Logeecom\Infrastructure\Data\DataTransferObject;

class Order extends DataTransferObject
{
    /**
     * @var int
     */
    protected $id;
    /**
     * @var string
     */
    protected $orderNumber;
    /**
     * @var \DateTime
     */
    protected $createdAt;
    /**
     * Full customer name.
     *
     * @var string
     */
    protected $customer;
    /**
     * Currency symbol.
     *
     * @var string
     */
    protected $currency;
    /**
     * @var boolean
     */
    protected $isPaid;
    /**
     * @var float
     */
    protected $total;
    /**
     * @var string
     */
    protected $carrierName;
    /**
     * @var string
     */
    protected $carrierLogo;
    /**
     * @var Draft
     */
    protected $draft;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getCustomer(): string
    {
        return $this->customer;
    }

    /**
     * @param string $customer
     */
    public function setCustomer(string $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    /**
     * @param bool $isPaid
     */
    public function setIsPaid(bool $isPaid): void
    {
        $this->isPaid = $isPaid;
    }

    /**
     * @return float
     */
    public function getTotal(): float
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal(float $total): void
    {
        $this->total = $total;
    }

    /**
     * @return string
     */
    public function getCarrierName(): string
    {
        return $this->carrierName;
    }

    /**
     * @param string $carrierName
     */
    public function setCarrierName(string $carrierName): void
    {
        $this->carrierName = $carrierName;
    }

    /**
     * @return string
     */
    public function getCarrierLogo(): string
    {
        return $this->carrierLogo;
    }

    /**
     * @param string $carrierLogo
     */
    public function setCarrierLogo(string $carrierLogo): void
    {
        $this->carrierLogo = $carrierLogo;
    }

    /**
     * @return Draft
     */
    public function getDraft(): Draft
    {
        return $this->draft;
    }

    /**
     * @param Draft $draft
     */
    public function setDraft(Draft $draft): void
    {
        $this->draft = $draft;
    }

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'orderNumber' => $this->getOrderNumber(),
            'createdAt' => $this->getCreatedAt()->format('M d, Y H:i'),
            'customer' => $this->getCustomer(),
            'currency' => $this->getCurrency(),
            'isPaid' => $this->isPaid(),
            'total' => $this->getTotal(),
            'carrierName' => $this->getCarrierName(),
            'carrierLogo' => $this->getCarrierLogo(),
            'draft' => $this->getDraft() ? $this->getDraft()->toArray() : null,
        ];
    }
}