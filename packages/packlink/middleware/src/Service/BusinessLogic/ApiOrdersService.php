<?php

namespace Packlink\Middleware\Service\BusinessLogic;

use Logeecom\Infrastructure\Configuration\Configuration;
use Logeecom\Infrastructure\ORM\QueryFilter\Operators;
use Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter;
use Logeecom\Infrastructure\ORM\RepositoryRegistry;
use Logeecom\Infrastructure\ServiceRegister;
use Packlink\BusinessLogic\Brand\BrandConfigurationService;
use Packlink\BusinessLogic\CountryLabels\Interfaces\CountryService;
use Packlink\BusinessLogic\Order\OrderService;
use Packlink\BusinessLogic\OrderShipmentDetails\Models\OrderShipmentDetails;
use Packlink\BusinessLogic\Utility\CurrencySymbolService;
use Packlink\Middleware\Entity\Order\Draft;
use Packlink\Middleware\Entity\Order\Label;
use Packlink\Middleware\Entity\Order\Order;
use Packlink\Middleware\Entity\Order\Page;
use Packlink\Middleware\Entity\Order\Shipment;
use Packlink\Middleware\Service\Required\OrderDetailsService;
use Packlink\BusinessLogic\ShipmentDraft\ShipmentDraftService;

class ApiOrdersService
{
    /**
     * @var \Packlink\Middleware\Service\Required\OrderDetailsService
     */
    protected $systemOrderService;
    /**
     * @var OrderShipmentDetails[]
     */
    protected $details = [];
    protected $userInfo;

    /**
     * ApiOrdersService constructor.
     *
     * @param \Packlink\Middleware\Service\Required\OrderDetailsService $systemOrderService
     */
    public function __construct(OrderDetailsService $systemOrderService)
    {
        $this->systemOrderService = $systemOrderService;
    }

    /**
     * Provides orders.
     *
     * @param int | string $page
     * @param int $limit
     *
     * @return Page
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function list($page, int $limit = 10): Page
    {
        $this->userInfo = ServiceRegister::getService(Configuration::CLASS_NAME)->getUserInfo();
        $result = $this->systemOrderService->list($page, $limit);

        $ids = [];
        foreach ($result->getOrders() as $order) {
            $ids[] = $order->getId();
        }
        $this->loadDetails($ids);
        $result->setOrders(array_map([$this, 'formatOrder'], $result->getOrders()));

        return $result;
    }

    /**
     * Provides specific orders.
     *
     * @param array $orderIds
     *
     * @return \Packlink\Middleware\Entity\Order\Page
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function specific(array $orderIds)
    {
        $result = new Page();
        $result->setPrevious(0);
        $result->setNext(0);
        $orders = $this->systemOrderService->getByIds($orderIds);
        $this->loadDetails($orderIds);
        $result->setOrders(array_map([$this, 'formatOrder'], $orders));

        return $result;
    }

    /**
     * Provides orders with active shipments.
     *
     * @param int $page
     * @param int $limit
     *
     * @return Page
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getActive(int $page, int $limit = 10): Page
    {
        $this->userInfo = ServiceRegister::getService(Configuration::CLASS_NAME)->getUserInfo();
        $result = new Page();
        $result->setPrevious(max($page - 1, 0));
        $result->setNext($page + 1);
        $result->setOrders([]);

        $query = $this->getActiveShipmentsQuery();
        $query->setOffset($page * $limit);
        $query->setLimit($limit);
        /** @var OrderShipmentDetails[] $shipments */
        $shipments = $this->getOrderDetailsRepository()->select($query);
        if (empty($shipments)) {
            return $result;
        }

        $ids = [];
        foreach ($shipments as $shipment) {
            $ids[] = $shipment->getOrderId();
        }

        if (empty($ids)) {
            return $result;
        }

        $orders = $this->systemOrderService->getByIds($ids);
        $this->loadDetails($ids);
        $result->setOrders(array_map([$this, 'formatOrder'], $orders));

        return $result;
    }

    /**
     * Provides system order count.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->systemOrderService->count();
    }

    /**
     * Provides count of active orders.
     *
     * @return int
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getActiveCount(): int
    {
        return $this->getOrderDetailsRepository()->count($this->getActiveShipmentsQuery());
    }

    /**
     * Retrieves order details identified by order id.
     *
     * @param int $orderId
     *
     * @return \Packlink\Middleware\Entity\Order\Order|null
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function get(int $orderId): ?Order
    {
        $this->userInfo = ServiceRegister::getService(Configuration::CLASS_NAME)->getUserInfo();
        $systemOrder = $this->systemOrderService->get($orderId);
        if (!$systemOrder) {
            return null;
        }

        $this->loadDetails([$orderId]);

        return $this->formatOrder($systemOrder);
    }

    /**
     * Retrieves draft details identified by order ID.
     *
     * @param int $orderId
     *
     * @return Draft|null
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    public function getOrderDraft(int $orderId): ?Draft
    {
        $this->userInfo = ServiceRegister::getService(Configuration::CLASS_NAME)->getUserInfo();

        $this->loadDetails([$orderId]);

        return $this->getDraft($orderId);
    }

    /**
     * Formats order.
     *
     * @param \Packlink\Middleware\Entity\Order\Shop\Order $systemOrder
     *
     * @return \Packlink\Middleware\Entity\Order\Order
     */
    private function formatOrder(\Packlink\Middleware\Entity\Order\Shop\Order $systemOrder): Order
    {
        $order = new Order();
        $order->setIsPaid($systemOrder->isPaid());
        $order->setOrderNumber($systemOrder->getOrderNumber());
        $order->setCustomer($systemOrder->getCustomer());
        $order->setCurrency($systemOrder->getCurrency());
        $order->setCreatedAt($systemOrder->getCreatedAt());
        $order->setId($systemOrder->getId());
        $order->setTotal($systemOrder->getTotal());
        $order->setCarrierName($systemOrder->getCarrierName() ?? '');
        $order->setCarrierLogo($systemOrder->getCarrierLogo() ?? '');
        $order->setDraft($this->getDraft($order->getId()));

        return $order;
    }

    /**
     * Sets details related to the packlink shipment.
     *
     * @param int $orderId
     *
     * @return Draft
     */
    private function getDraft(int $orderId): Draft
    {
        $draft = new Draft();

        $draft->setDraftStatus($this->getDraftService()->getDraftStatus((string)$orderId)->status ?? '');
        $details = !empty($this->details[$orderId]) ? $this->details[$orderId] : null;
        $draft->setLabel($this->getLabel($details));

        if ($details !== null) {
            $draft->setDraftDetails($this->getDraftDetails($details));
        }

        if ($details && $details->getShippingStatus()) {
            /** @var CountryService $countryService */
            $countryService = ServiceRegister::getService(CountryService::class);

            $draft->setDraftLabel(
                $countryService->getLabel(
                    Configuration::getUICountryCode(),
                    'orderStatusMapping.' . $details->getShippingStatus()
                )
            );
        }

        if ($details && $details->getReference()) {
            $draft->setDraftLink($this->getPacklinkLink($details->getReference()));
        }

        return $draft;
    }

    /**
     * Retrieves label from shipment details.
     *
     * @param \Packlink\BusinessLogic\OrderShipmentDetails\Models\OrderShipmentDetails|null $details
     *
     * @return \Packlink\Middleware\Entity\Order\Label
     */
    private function getLabel(?OrderShipmentDetails $details) : Label
    {
        $label = new Label();
        $label->setIsAvailable(false);
        $label->setIsPrinted(false);

        if ($details === null) {
            return $label;
        }

        $label->setIsPrinted(!empty($details->getShipmentLabels()[0]) && $details->getShipmentLabels()[0]->isPrinted());
        $label->setIsAvailable($this->getOrderService()->isReadyToFetchShipmentLabels($details->getShippingStatus()));

        return $label;
    }

    /**
     * Provides draft details.
     *
     * @param \Packlink\BusinessLogic\OrderShipmentDetails\Models\OrderShipmentDetails $details
     *
     * @return \Packlink\Middleware\Entity\Order\Shipment
     */
    private function getDraftDetails(OrderShipmentDetails $details): Shipment
    {
        $shipment = new Shipment();

        $shipment->setTotal($details->getShippingCost() ?? 0.0);
        $shipment->setCurrency(CurrencySymbolService::getCurrencySymbol($details->getCurrency()));
        $shipment->setReference($details->getReference() ?? '');
        $shipment->setTrackingNumber(!empty($details->getCarrierTrackingNumbers()[0]) ? $details->getCarrierTrackingNumbers()[0] : null);

        return $shipment;
    }

    /**
     * Provides active shipments query.
     *
     * @return \Logeecom\Infrastructure\ORM\QueryFilter\QueryFilter
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     */
    private function getActiveShipmentsQuery(): QueryFilter
    {
        $query = new QueryFilter();
        $query->where('reference', Operators::NOT_NULL);
        $query->orderBy('orderId', 'DESC');

        return $query;
    }

    /**
     * Loads order details to local cache.
     *
     * @param array $orderIds
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function loadDetails(array $orderIds): void
    {
        $filter = new QueryFilter();
        $filter->where('orderId', Operators::IN, $orderIds);
        $filter->setLimit(10);
        $details = $this->getOrderDetailsRepository()->select($filter);
        /** @var OrderShipmentDetails $detail */
        foreach ($details as $detail) {
            $this->details[(int)$detail->getOrderId()] = $detail;
        }
    }

    /**
     * Provides link to Packlink order draft.
     *
     * @param string $reference
     *
     * @return string
     */
    private function getPacklinkLink(string $reference): string
    {
        /** @var BrandConfigurationService $brandService */
        $brandService = ServiceRegister::getService(BrandConfigurationService::class);

        $brandConfiguration = $brandService->get();
        $countryCode = 'en';

        if ($this->userInfo !== null &&
            in_array($this->userInfo->country, $brandConfiguration->platformCountries, true)) {
            $countryCode = $this->userInfo->country;
        }

        /** @var CountryService $countryService */
        $countryService = ServiceRegister::getService(CountryService::class);

        return $countryService->getLabel(strtolower($countryCode), 'orderListAndDetails.shipmentUrl') . $reference;
    }

    /**
     * Retrieves shipment draft service.
     *
     * @return ShipmentDraftService | object
     */
    private function getDraftService()
    {
        return ServiceRegister::getService(ShipmentDraftService::CLASS_NAME);
    }

    /**
     * Retrieves order service.
     *
     * @return OrderService | object
     */
    private function getOrderService()
    {
        return ServiceRegister::getService(OrderService::CLASS_NAME);
    }

    /**
     * Retrieves order shipment details repository.
     *
     * @return \Logeecom\Infrastructure\ORM\Interfaces\RepositoryInterface
     *
     * @throws \Logeecom\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException
     */
    private function getOrderDetailsRepository()
    {
        return RepositoryRegistry::getRepository(OrderShipmentDetails::getClassName());
    }
}