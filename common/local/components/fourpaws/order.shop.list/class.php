<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\App\Application;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Helpers\DeliveryTimeHelper;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\SaleBundle\Service\OrderService;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Collection\StoreCollection;
use FourPaws\StoreBundle\Service\StoreService;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Delivery\CalculationResult;

CBitrixComponent::includeComponentClass('fourpaws:shop.list');

class FourPawsOrderShopListComponent extends FourPawsShopListComponent
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var StoreService
     */
    protected $storeService;
    
    /**
     * FourPawsOrderShopListComponent constructor.
     *
     * @param null $component
     */
    public function __construct($component = null)
    {
        parent::__construct($component);
        $serviceContainer = Application::getInstance()->getContainer();
        $this->orderService = $serviceContainer->get(OrderService::class);
        $this->deliveryService = $serviceContainer->get('delivery.service');
        $this->storeService = $serviceContainer->get('store.service');
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TYPE'] = 'N';

        return parent::onPrepareComponentParams($params);
    }

    /**
     * @throws \RuntimeException
     */
    protected function prepareResult()
    {
        $serviceContainer = Application::getInstance()->getContainer();

        /** @var BasketService $basketService */
        $basketService = $serviceContainer->get(BasketService::class);

        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return;
        }

        /** @var StockResultCollection $stockResult */
        if (!$stockResult = $this->getStockResult()) {
            return;
        }

        $offerIds = [];
        $basket = $basketService->getBasket()->getOrderableItems();
        /** @var BasketItem $item */
        foreach ($basket as $item) {
            if (!$item->getProductId()) {
                continue;
            }

            $offerIds[] = $item->getProductId();
        }

        if ($basket->isEmpty() || empty($offerIds)) {
            throw new \RuntimeException('Basket is empty');
        }

        $offers = (new OfferQuery())->withFilterParameter('ID', $offerIds)->exec();
        if ($offers->isEmpty()) {
            throw new \RuntimeException('Offers not found');
        }

        /** @var \Symfony\Bundle\FrameworkBundle\Routing\Router $router */
        $router = Application::getInstance()->getContainer()->get('router');
        /** @var Symfony\Component\Routing\RouteCollection $routeCollection */
        $storeListUrlRoute = null;
        if ($routeCollection = $router->getRouteCollection()) {
            $storeListUrlRoute = $routeCollection->get('fourpaws_sale_ajax_order_storesearch');
        }
        $this->arResult['DELIVERY'] = $pickupDelivery;
        $this->arResult['DELIVERY_CODE'] = $pickupDelivery->getData()['DELIVERY_CODE'];
        $this->arResult['STORE_LIST_URL'] = $storeListUrlRoute ? $storeListUrlRoute->getPath() : '';
    }

    /**
     * @param array $filter
     * @param array $order
     *
     * @return array
     */
    public function getStores(array $filter = [], array $order = [], $returnActiveServices = false): array
    {
        $result = [];

        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return $result;
        }

        $stores = $this->getStoreList($filter, $order);
        if ($stores->isEmpty()) {
            $stockResult = $this->getStockResult();
            list($servicesList, $metroList) = $this->getFullStoreInfo($stores);

            $avgGpsN = 0;
            $avgGpsS = 0;

            $resultByStore = [];

            /** @var Store $store */
            foreach ($stores as $store) {
                $resultByStore[$store->getXmlId()] = $this->getStoreData($pickupDelivery, $stockResult, $store);
            }

            /**
             * 1) По убыванию % от суммы товаров заказа в наличии в магазине или на складе
             * 2) По возрастанию даты готовности заказа к выдаче
             * 3) По адресу магазина в алфавитном порядке
             */
            $sortFunc = function ($shop1, $shop2) use ($resultByStore) {
                /** @var Store $shop1 */
                /** @var Store $shop2 */
                $shopData1 = $resultByStore[$shop1->getXmlId()];
                $shopData2 = $resultByStore[$shop2->getXmlId()];

                if ($shopData1['AVAILABLE_AMOUNT'] != $shopData2['AVAILABLE_AMOUNT']) {
                    return ($shopData1['AVAILABLE_AMOUNT'] > $shopData2['AVAILABLE_AMOUNT']) ? -1 : 1;
                }

                /** @var StockResultCollection $stockResult1 */
                $stockResult1 = $shopData1['STOCK_RESULT'];
                /** @var StockResultCollection $stockResult2 */
                $stockResult2 = $shopData2['STOCK_RESULT'];
                $deliveryDate1 = $stockResult1->getDeliveryDate();
                $deliveryDate2 = $stockResult2->getDeliveryDate();

                if ($deliveryDate1 != $deliveryDate2) {
                    return ($shopData1['AVAILABLE_AMOUNT'] > $shopData2['AVAILABLE_AMOUNT']) ? 1 : -1;
                }

                return $shop1->getAddress() > $shop2->getAddress() ? 1 : -1;
            };

            uasort($stores, $sortFunc);

            /**
             * для самовывоза из DPD показываем то, что вернула доставка
             * для самовывоза из магазина - с учетом времени работы магазина
             */
            $modifyDate = $this->deliveryService->isInnerPickup($pickupDelivery);
            /** @var Store $store */
            foreach ($stores as $store) {
                $metro = $store->getMetro();

                $services = [];
                if (\is_array($servicesList) && !empty($servicesList)) {
                    foreach ($servicesList as $service) {
                        $services[] = $service['UF_NAME'];
                    }
                }

                $address = !empty($metro)
                    ? 'м. ' . $metroList[$metro]['UF_NAME'] . ', ' . $store->getAddress()
                    : $store->getAddress();

                /** @var StockResultCollection $stockResultByStore */
                $stockResultByStore = $resultByStore[$store->getXmlId()]['STOCK_RESULT'];
                $available = $stockResultByStore->getAvailable();
                $delayed = $stockResultByStore->getDelayed();

                $partsDelayed = [];
                /** @var StockResult $item */
                foreach ($delayed as $item) {
                    $partsDelayed[] = [
                        'name'     => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price'    => $item->getPrice(),
                        'weight'   => $item->getOffer()->getCatalogProduct()->getWeight(),
                    ];
                }

                $partsAvailable = [];
                /** @var StockResult $item */
                foreach ($available as $item) {
                    $partsAvailable[] = [
                        'name'     => $item->getOffer()->getName(),
                        'quantity' => $item->getAmount(),
                        'price'    => $item->getPrice(),
                        'weight'   => $item->getOffer()->getCatalogProduct()->getWeight(),
                    ];
                }

                if ($delayed->isEmpty()) {
                    $orderType = 'full';
                } else {
                    if ($available->isEmpty()) {
                        $orderType = 'delay';
                    } else {
                        $orderType = 'parts';
                    }
                }

                $delayedDate = $delayed->isEmpty()
                    ? $stockResultByStore->getDeliveryDate()
                    : $delayed->getDeliveryDate();
                $result['items'][] = [
                    'id'              => $store->getXmlId(),
                    'adress'          => $address,
                    'phone'           => $store->getPhone(),
                    'schedule'        => $store->getSchedule(),
                    'pickup'          => DeliveryTimeHelper::showTime(
                        $resultByStore[$store->getXmlId()]['PARTIAL_RESULT'],
                        $modifyDate ? $delayedDate : null,
                        true,
                        false
                    ),
                    'pickup_full'     => DeliveryTimeHelper::showTime(
                        $resultByStore[$store->getXmlId()]['FULL_RESULT'],
                        $modifyDate ? $stockResultByStore->getDeliveryDate() : null,
                        true,
                        false
                    ),
                    'metroClass'      => !empty($metro) ? '--' . $metroList[$metro]['UF_CLASS'] : '',
                    'order'           => $orderType,
                    'parts_available' => $partsAvailable,
                    'parts_delayed'   => $partsDelayed,
                    'services'        => $services,
                    'price'           => $delayed->isEmpty() ? $stockResultByStore->getPrice() : $delayed->getPrice(),
                    'full_price'      => $stockResultByStore->getPrice(),
                    /* @todo поменять местами gps_s и gps_n */
                    'gps_n'           => $store->getLongitude(),
                    'gps_s'           => $store->getLatitude(),
                ];
                $avgGpsN += $store->getLongitude();
                $avgGpsS += $store->getLatitude();
            }
            $countStores = count($stores);

            $result['avg_gps_n'] = $avgGpsN / $countStores;
            $result['avg_gps_s'] = $avgGpsS / $countStores;
            if ($returnActiveServices) {
                $result['services'] = $servicesList;
            }
        }

        return $result;
    }

    /**
     *
     * @param StoreCollection $stores
     *
     * @throws \Exception
     * @return array
     */
    public function getFullStoreInfo(StoreCollection $stores): array
    {
        $servicesIds = [];
        $metroIds = [];
        /** @var Store $store */
        foreach ($stores as $store) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $servicesIds = array_merge($servicesIds, $store->getServices());
            $metro = $store->getMetro();
            if ($metro > 0) {
                $metroIds[] = $metro;
            }
        }
        $services = [];
        if (!empty($servicesIds)) {
            $services = $this->storeService->getServicesInfo(['ID' => array_unique($servicesIds)]);
        }

        $metro = [];
        if (!empty($metroIds)) {
            $metro = $this->storeService->getMetroInfo(['ID' => array_unique($metroIds)]);
        }

        return [
            $services,
            $metro,
        ];
    }

    /**
     * @param array $filter
     * @param array $order
     *
     * @return StoreCollection
     */
    protected function getStoreList(array $filter, array $order): StoreCollection
    {
        /** @var StockResultCollection $stockResult */
        if (!$stockResult = $this->getStockResult()) {
            return new StoreCollection();
        }

        $defaultFilter = [];
        /** @var Store $store */
        $idFilter = [];
        foreach ($stockResult->getStores() as $store) {
            $idFilter[] = $store->getId();
        }
        if (!empty($idFilter)) {
            $defaultFilter['ID'] = $idFilter;
        }

        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return new StoreCollection();
        }
        if ($this->deliveryService->isDpdPickup($pickupDelivery)) {
            return $this->deliveryService->getStockResultByDelivery($pickupDelivery)->getStores()->toArray();
        }

        $storeRepository = $this->storeService->getRepository();

        return $storeRepository->findBy(array_merge($filter, $defaultFilter), $order);
    }

    /**
     * @return CalculationResult|null
     */
    protected function getPickupDelivery()
    {
        $pickupDelivery = null;
        $deliveries = $this->orderService->getDeliveries();
        foreach ($deliveries as $delivery) {
            if ($this->deliveryService->isPickup($delivery)) {
                $pickupDelivery = $delivery;
                break;
            }
        }

        return $pickupDelivery;
    }

    /**
     * @return bool|StockResultCollection
     */
    protected function getStockResult()
    {
        if (!$pickupDelivery = $this->getPickupDelivery()) {
            return false;
        }

        return $this->deliveryService->getStockResultByDelivery($pickupDelivery);
    }

    /**
     * @param CalculationResult $delivery
     * @param StockResultCollection $stockResult
     * @param Store $store
     *
     * @return array
     */
    protected function getStoreData(
        CalculationResult $delivery,
        StockResultCollection $stockResult,
        Store $store
    ): array {
        $result = [];
        $stockResultByStore = $stockResult->filterByStore($store);
        $delayed = $stockResultByStore->getDelayed();
        $available = $stockResultByStore->getAvailable();

        $result['AVAILABLE_AMOUNT'] = $available->isEmpty() ? 0 : $available->getAmount();
        $result['DELAYED_AMOUNT'] = $delayed->isEmpty() ? 0 : $delayed->getAmount();

        $fullResult = clone $delivery;
        $partialResult = clone $delivery;

        DeliveryTimeHelper::updateDeliveryDate($partialResult, $available->getDeliveryDate());
        DeliveryTimeHelper::updateDeliveryDate($fullResult, $stockResultByStore->getDeliveryDate());

        $result['PARTIAL_RESULT'] = $partialResult;
        $result['FULL_RESULT'] = $fullResult;
        $result['STOCK_RESULT'] = $stockResultByStore;

        return $result;
    }
    
    
}
