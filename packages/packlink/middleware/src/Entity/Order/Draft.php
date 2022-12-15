<?php

namespace Packlink\Middleware\Entity\Order;

use Logeecom\Infrastructure\Data\DataTransferObject;

class Draft extends DataTransferObject
{
    /**
     * @var string
     */
    protected $draftStatus;
    /**
     * @var string
     */
    protected $draftLabel;
    /**
     * @var string
     */
    protected $draftLink;
    /**
     * @var Shipment | null
     */
    protected $draftDetails;
    /**
     * @var Label | null
     */
    protected $label;

    /**
     * @return string
     */
    public function getDraftStatus(): string
    {
        return $this->draftStatus;
    }

    /**
     * @param string $draftStatus
     */
    public function setDraftStatus(string $draftStatus): void
    {
        $this->draftStatus = $draftStatus ?? "";
    }

    /**
     * @return string
     */
    public function getDraftLabel(): string
    {
        return $this->draftLabel ?? '';
    }

    /**
     * @param string $draftLabel
     */
    public function setDraftLabel(string $draftLabel): void
    {
        $this->draftLabel = $draftLabel;
    }

    /**
     * @return string
     */
    public function getDraftLink(): string
    {
        return $this->draftLink ?? '';
    }

    /**
     * @param string $draftLink
     */
    public function setDraftLink(string $draftLink): void
    {
        $this->draftLink = $draftLink;
    }

    /**
     * @return \Packlink\Middleware\Entity\Order\Shipment|null
     */
    public function getDraftDetails(): ?Shipment
    {
        return $this->draftDetails;
    }

    /**
     * @param \Packlink\Middleware\Entity\Order\Shipment|null $draftDetails
     */
    public function setDraftDetails(?Shipment $draftDetails): void
    {
        $this->draftDetails = $draftDetails;
    }

    /**
     * @return \Packlink\Middleware\Entity\Order\Label|null
     */
    public function getLabel(): ?Label
    {
        return $this->label;
    }

    /**
     * @param \Packlink\Middleware\Entity\Order\Label|null $label
     */
    public function setLabel(?Label $label): void
    {
        $this->label = $label;
    }

    /**
     * Transforms data transfer object to array.
     *
     * @return array Array representation of data transfer object.
     */
    public function toArray()
    {
        return [
            'draftStatus' => $this->getDraftStatus(),
            'draftLabel' => $this->getDraftLabel(),
            'draftLink' => $this->getDraftLink(),
            'label' => $this->getLabel() ? $this->getLabel()->toArray() : null,
            'draftDetails' => $this->getDraftDetails() ? $this->getDraftDetails()->toArray() : null,
        ];
    }
}