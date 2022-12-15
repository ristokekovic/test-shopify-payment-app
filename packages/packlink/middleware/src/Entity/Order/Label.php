<?php

namespace Packlink\Middleware\Entity\Order;

use Logeecom\Infrastructure\Data\DataTransferObject;

class Label extends DataTransferObject
{
    /**
     * @var boolean
     */
    protected $isPrinted;
    /**
     * @var boolean
     */
    protected $isAvailable;

    /**
     * @return bool
     */
    public function isPrinted(): bool
    {
        return $this->isPrinted;
    }

    /**
     * @param bool $isPrinted
     */
    public function setIsPrinted(bool $isPrinted): void
    {
        $this->isPrinted = $isPrinted;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     */
    public function setIsAvailable(bool $isAvailable): void
    {
        $this->isAvailable = $isAvailable;
    }

    public function toArray()
    {
        return [
            'isPrinted' => $this->isPrinted(),
            'isAvailable' => $this->isAvailable(),
        ];
    }
}