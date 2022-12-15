<?php

namespace Packlink\Middleware\Entity\Order;

use Logeecom\Infrastructure\Data\DataTransferObject;

class Shipment extends DataTransferObject
{
    /**
     * @var string
     */
    protected $reference;
    /**
     * @var string | null
     */
    protected $trackingNumber;
    /**
     * @var string | null
     */
    protected $total;
    /**
     * @var string
     */
    protected $currency;

    /**
     * @return string
     */
    public function getReference(): string
    {
        return $this->reference;
    }

    /**
     * @param string $reference
     */
    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    /**
     * @return string|null
     */
    public function getTrackingNumber(): ?string
    {
        return $this->trackingNumber;
    }

    /**
     * @param string|null $trackingNumber
     */
    public function setTrackingNumber(?string $trackingNumber): void
    {
        $this->trackingNumber = $trackingNumber;
    }

    /**
     * @return string|null
     */
    public function getTotal(): ?string
    {
        return $this->total;
    }

    /**
     * @param string|null $total
     */
    public function setTotal(?string $total): void
    {
        $this->total = $total;
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

    public function toArray()
    {
        return [
            'reference' => $this->getReference(),
            'trackingNumber' => $this->getTrackingNumber(),
            'total' => $this->getTotal(),
            'currency' => $this->getCurrency(),
        ];
    }
}