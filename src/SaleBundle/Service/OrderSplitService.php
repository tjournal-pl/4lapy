<?php

namespace FourPaws\SaleBundle\Service;

use Adv\Bitrixtools\Tools\BitrixUtils;
use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\NotImplementedException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertyItem;
use Bitrix\Sale\Order;
use Bitrix\Sale\UserMessageException;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Collection\StockResultCollection;
use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\DeliveryBundle\Entity\CalculationResult\PickupResultInterface;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\DeliveryBundle\Exception\NotFoundException;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Dto\OrderSplit\Basket\BasketSplitItem;
use FourPaws\SaleBundle\Dto\OrderSplit\Basket\BasketSplitResult;
use FourPaws\SaleBundle\Dto\OrderSplit\SplitStockResult;
use FourPaws\SaleBundle\Entity\OrderSplitResult;
use FourPaws\SaleBundle\Entity\OrderStorage;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\DeliveryNotAvailableException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException as NotFoundSaleException;
use FourPaws\SaleBundle\Exception\OrderCreateException;
use FourPaws\SaleBundle\Exception\OrderSplitException;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\StoreBundle\Exception\NotFoundException as StoreNotFoundException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class OrderSplitService implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BasketService
     */
    protected $basketService;

    /**
     * @var DeliveryService
     */
    protected $deliveryService;

    /**
     * @var OrderStorageService
     */
    protected $orderStorageService;

    /**
     * @var CurrentUserProviderInterface
     */
    protected $currentUserProvider;

    /**
     * OrderSplitService constructor.
     *
     * @param BasketService                $basketService
     * @param DeliveryService              $deliveryService
     * @param OrderStorageService          $orderStorageService
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(
        BasketService $basketService,
        DeliveryService $deliveryService,
        OrderStorageService $orderStorageService,
        CurrentUserProviderInterface $currentUserProvider
    )
    {
        $this->deliveryService = $deliveryService;
        $this->orderStorageService = $orderStorageService;
        $this->basketService = $basketService;
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param OrderStorage $storage
     *
     * @return OrderSplitResult[]
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws BitrixProxyException
     * @throws DeliveryNotAvailableException
     * @throws LoaderException
     * @throws NotFoundException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws OrderCreateException
     * @throws OrderSplitException
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws StoreNotFoundException
     * @throws SystemException
     * @throws UserMessageException
     * @throws \Exception
     * @throws InvalidArgumentException
     * @throws NotFoundSaleException
     * @throws \LogicException
     * @throws \RuntimeException
     */
    public function splitOrder(OrderStorage $storage): array
    {
        $delivery = clone $this->orderStorageService->getSelectedDelivery($storage);
        $canSplit = $this->canSplitOrder($delivery);
        $canGetPartial = $this->canGetPartial($delivery);

        if (!($canSplit || $canGetPartial)) {
            throw new OrderSplitException('Cannot split order');
        }

        $splitResult1 = null;
        $splitResult2 = null;

        $basket = $this->basketService->getBasket();
        if ($isDiscountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::disableExtendsDiscount();
        }

        $storage1 = clone $storage;

        $splitStockResult = $this->splitStockResult($delivery);
        $splitBasket = $this->splitBasket($basket, $splitStockResult);
        $basket1 = $this->generateBasket($splitBasket->getAvailable(), $canGetPartial);

        $storage1->setBonus($this->getMaxBonusPayment($basket1, $storage));

        /**
         * Требуется пересчет стоимости доставки для первого заказа
         */
        $tmpDeliveries = $this->deliveryService->getByBasket(
            $basket1,
            '',
            [$delivery->getDeliveryCode()],
            $storage1->getCurrentDate()
        );
        if (!$delivery1 = reset($tmpDeliveries)) {
            throw new OrderSplitException('Delivery for order1 is unavailable');
        }

        if ($delivery1 instanceof PickupResultInterface) {
            /** @var PickupResultInterface $delivery */
            $delivery1->setSelectedShop($delivery->getSelectedShop());
            if (!$delivery1->isSuccess()) {
                throw new OrderSplitException(
                    sprintf('Delivery for order1 is unavailable: %s', \implode($delivery1->getErrorMessages()))
                );
            }
        }

        $order1 = $this->getOrderService()->initOrder($storage1, $basket1, $delivery1);
        $splitResult1 = (new OrderSplitResult())->setOrderStorage($storage1)
                                                ->setOrder($order1)
                                                ->setDelivery($delivery1);

        $storage2 = clone $storage;
        $basket2 = $this->generateBasket($splitBasket->getDelayed(), false);
        $storage2->setDeliveryInterval($storage->getSecondDeliveryInterval());
        $storage2->setDeliveryDate($storage->getSecondDeliveryDate());
        $storage2->setComment($storage->getSecondComment());
        $storage2->setBonus($storage->getBonus() - $storage1->getBonus());

        $delivery2 = (clone $delivery)->setStockResult($splitStockResult->getDelayed());
        if (!$delivery2->isSuccess()) {
            throw new OrderSplitException('Delivery for order2 is unavailable');
        }
        /**
         * У второго заказа (содержащего товары под заказ) доставка бесплатная
         */
        $delivery2->setDeliveryPrice(0);

        $order2 = $this->getOrderService()->initOrder($storage2, $basket2, $delivery2);
        $splitResult2 = (new OrderSplitResult())->setOrderStorage($storage2)
                                                ->setOrder($order2)
                                                ->setDelivery($delivery2);


        if ($isDiscountEnabled) {
            Manager::enableExtendsDiscount();
        }

        return [
            $splitResult1,
            $splitResult2,
        ];
    }


    /**
     * @param Basket           $basket
     * @param SplitStockResult $splitStockResult
     *
     * @return BasketSplitResult
     * @throws ArgumentException
     * @throws ArgumentNullException
     */
    public function splitBasket(Basket $basket, SplitStockResult $splitStockResult): BasketSplitResult
    {
        $availableItems = new ArrayCollection();
        $delayedItems = new ArrayCollection();
        /** @var BasketItem $basketItem */
        foreach ($basket as $basketItem) {
            if ($basketItem->isDelay()) {
                continue;
            }

            $properties = $basketItem->getPropertyCollection()->getPropertyValues();
            $hasBonus = $properties['HAS_BONUS']['VALUE'];
            if ($availableResult = $splitStockResult->getAvailable()->filterByOfferId($basketItem->getProductId())
                                                    ->first()) {
                /** @var StockResult $availableResult */
                if (($priceForAmount = $availableResult->getPriceForAmountByBasketCode($basketItem->getBasketCode())) &&
                    $priceForAmount->getAmount()
                ) {
                    if ($hasBonus) {
                        if ($hasBonus > $priceForAmount->getAmount()) {
                            $properties['HAS_BONUS']['VALUE'] = $priceForAmount->getAmount();
                            $hasBonus -= $priceForAmount->getAmount();
                        } else {
                            $properties['HAS_BONUS']['VALUE'] = $hasBonus;
                            $hasBonus = 0;
                        }
                    }

                    $availableItems->add((new BasketSplitItem())
                        ->setAmount($priceForAmount->getAmount())
                        ->setProductId($basketItem->getProductId())
                        ->setPrice($basketItem->getPrice())
                        ->setBasePrice($basketItem->getBasePrice())
                        ->setDiscount($basketItem->getDiscountPrice())
                        ->setProperties($properties)
                        ->setGift($priceForAmount->isGift())
                    );
                }
            }
            if ($delayedResult = $splitStockResult->getDelayed()->filterByOfferId($basketItem->getProductId())
                                                  ->first()) {
                /** @var StockResult $delayedResult */
                /** @var StockResult $availableResult */
                if (($priceForAmount = $delayedResult->getPriceForAmountByBasketCode($basketItem->getBasketCode())) &&
                    $priceForAmount->getAmount()
                ) {
                    if (null !== $hasBonus) {
                        $properties['HAS_BONUS']['VALUE'] = (int)$hasBonus;
                    }
                    $delayedItems->add((new BasketSplitItem())
                        ->setAmount($priceForAmount->getAmount())
                        ->setProductId($basketItem->getProductId())
                        ->setPrice($basketItem->getPrice())
                        ->setBasePrice($basketItem->getBasePrice())
                        ->setDiscount($basketItem->getDiscountPrice())
                        ->setProperties($properties)
                        ->setGift($priceForAmount->isGift())
                    );
                }
            }
        }

        return (new BasketSplitResult())->setAvailable($availableItems)
                                        ->setDelayed($delayedItems);
    }

    /**
     * Можно ли разделить заказ
     *
     * @param CalculationResultInterface $delivery
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     *
     * @return bool
     */
    public function canSplitOrder(CalculationResultInterface $delivery): bool
    {
        $result = false;

        $orderable = $delivery->getStockResult()->getOrderable();
        if (
            !$this->deliveryService->isDpdPickup($delivery) &&
            (
                \in_array($delivery->getDeliveryZone(), [
                DeliveryService::ZONE_1,
                DeliveryService::ZONE_2,
                DeliveryService::ZONE_NIZHNY_NOVGOROD,
                DeliveryService::ZONE_NIZHNY_NOVGOROD_REGION,
                DeliveryService::ZONE_VLADIMIR,
                DeliveryService::ZONE_VLADIMIR_REGION,
                DeliveryService::ZONE_VORONEZH,
                DeliveryService::ZONE_VORONEZH_REGION,
                DeliveryService::ZONE_YAROSLAVL,
                DeliveryService::ZONE_YAROSLAVL_REGION,
                DeliveryService::ZONE_TULA,
                DeliveryService::ZONE_TULA_REGION,
                DeliveryService::ZONE_KALUGA,
                DeliveryService::ZONE_KALUGA_REGION,
                DeliveryService::ZONE_IVANOVO,
                DeliveryService::ZONE_IVANOVO_REGION,
                DeliveryService::ZONE_5,
                DeliveryService::ZONE_6,
            ], true) ||
                mb_strpos($delivery->getDeliveryZone(), DeliveryService::ADD_DELIVERY_ZONE_CODE_PATTERN) !== false ||
                mb_strpos($delivery->getDeliveryZone(), DeliveryService::ZONE_MOSCOW_DISTRICT_CODE_PATTERN) !== false
            ) &&
            !$orderable->getByRequest(true)->isEmpty()
        ) {
            $available = $orderable->getRegular();
            $delayed = $orderable->getByRequest();

            if ($result = (!$available->isEmpty() && !$delayed->isEmpty())) {
                $fullDate = $delivery->getDeliveryDate();
                $partialDate = (clone $delivery)->setStockResult($available)->getDeliveryDate();
                $result = $fullDate->getTimestamp() !== $partialDate->getTimestamp();
            }

            $result &= $available->getPrice() && $delayed->getPrice();
        }

        return $result;
    }

    /**
     * Возможно ли частичное получение заказа
     *
     * @param CalculationResultInterface $delivery
     *
     * @return bool
     */
    public function canGetPartial(CalculationResultInterface $delivery): bool
    {
        $result = false;

        $available = $delivery->getStockResult()->getAvailable();
        $delayed = $delivery->getStockResult()->getDelayed();
        if (!$available->isEmpty() &&
            !$delayed->isEmpty() &&
            $this->deliveryService->isInnerPickup($delivery) &&
            $delivery->getStockResult()->getByRequest(true)->isEmpty()
        ) {
            $result = true;
        }

        $result &= $available->getPrice() && $delayed->getPrice();

        return $result;
    }

    /**
     * @param CalculationResultInterface $delivery
     *
     * @throws ApplicationCreateException
     * @throws ArgumentException
     * @throws NotFoundException
     * @throws StoreNotFoundException
     * @return SplitStockResult
     */
    public function splitStockResult(CalculationResultInterface $delivery): SplitStockResult
    {
        $stockResultCollection = $delivery->getStockResult();
        if ($this->canSplitOrder($delivery)) {
            $available = $stockResultCollection->getRegular();
            $delayed = $stockResultCollection->getByRequest();
        } elseif ($this->canGetPartial($delivery)) {
            $available = $stockResultCollection->getAvailable();
            $delayed = $stockResultCollection->getDelayed();
        } elseif (!$delivery->getStockResult()->getDelayed()->isEmpty()) {
            $available = new StockResultCollection();
            $delayed = $delivery->getStockResult();
        } else {
            $delayed = new StockResultCollection();
            $available = $delivery->getStockResult();
        }

        /**
         * Не позволяем разделять так, чтобы один из наборов становился с нулевой ценой
         * (например, содержал одни только подарки)
         */
        if ((!$available->isEmpty() && !$available->getPrice()) ||
            (!$delayed->isEmpty() && !$delayed->getPrice())
        ) {
            $delayed = $stockResultCollection;
            $available = new StockResultCollection();
        }

        return (new SplitStockResult())->setAvailable($available)
                                       ->setDelayed($delayed);
    }

    /**
     * @param StockResultCollection $stockResultCollection
     *
     * @return StockResultCollection
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \RuntimeException
     */
    public function recalculateStockResult(StockResultCollection $stockResultCollection): StockResultCollection
    {
        $result = new StockResultCollection();
        $items = new ArrayCollection();
        /** @var StockResult $stockResult */
        foreach ($stockResultCollection as $stockResult) {
            if (!$amount = $stockResult->getAmountWithoutGifts()) {
                continue;
            }

            $items->add(
                (new BasketSplitItem())
                    ->setProductId($stockResult->getOffer()->getId())
                    ->setAmount($amount)
            );
        }

        /** @var Store $store */
        $store = $stockResultCollection->getStores()->first();

        $basket = $this->generateBasket($items, true);

        $offers = $stockResultCollection->getOffers(false);
        $basketPrices = $this->deliveryService->getBasketPrices($basket);

        foreach ($basketPrices as $offerId => $priceForAmountCollection) {
            if (!isset($offers[$offerId])) {
                $offers[$offerId] = OfferQuery::getById($offerId);
            }

            /** @var Offer $offer */
            $offer = $offers[$offerId];
            $result->add((new StockResult())
                ->setOffer($offer)
                ->setPriceForAmount($basketPrices[$offerId])
                ->setStore($store)
            );
        }

        return $result;
    }

    /**
     * @param ArrayCollection $items
     * @param bool            $recalculateDiscounts
     *
     * @return Basket
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws \RuntimeException
     */
    public function generateBasket(ArrayCollection $items, $recalculateDiscounts = false): Basket
    {
        /** @var Basket $basket */
        $basket = Basket::create(SITE_ID);

        try {
            /** @var BasketSplitItem $item */
            foreach ($items as $item) {
                if ($recalculateDiscounts) {
                    if ($item->isGift()) {
                        continue;
                    }

                    $rewriteFields = [];
                } else {
                    $rewriteFields = [
                        'PRICE'        => $item->getPrice(),
                        'BASE_PRICE'   => $item->getBasePrice(),
                        'DISCOUNT'     => $item->getDiscount(),
                        'PROPS'        => $item->getProperties(),
                        'CUSTOM_PRICE' => BitrixUtils::BX_BOOL_TRUE,
                    ];
                }

                $basketItem = $this->basketService->addOfferToBasket(
                    $item->getProductId(),
                    $item->getAmount(),
                    $rewriteFields,
                    false,
                    $basket
                );

                if ($recalculateDiscounts) {
                    /** @var BasketPropertyItem $propertyValue */
                    foreach ($basketItem->getPropertyCollection() as $propertyValue) {
                        if ($propertyValue->getField('CODE') === 'HAS_BONUS') {
                            $propertyValue->delete();
                        }
                    }
                }

            }
        } catch (\Exception $e) {
            $this->log()->error(
                sprintf(
                    'failed to add offer to basket: %s: %s',
                    \get_class($e),
                    $e->getMessage()
                )
            );
        }

        if ($recalculateDiscounts) {
            $this->recalculateDiscounts($basket);
        }

        return $basket;
    }

    /**
     * @param Basket $basket
     *
     * @throws ArgumentException
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     * @throws NotImplementedException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function recalculateDiscounts(Basket $basket): void
    {
        if (!$isDiscountEnabled = Manager::isExtendDiscountEnabled()) {
            Manager::enableExtendsDiscount();
        }

        Manager::setExtendCalculated(false);
        $userId = null;
        try {
            $userId = $this->currentUserProvider->getCurrentUserId();
        } catch (NotAuthorizedException $e) {
        }
        $order = Order::create(SITE_ID, $userId);
        $order->setBasket($basket);

        if (!$isDiscountEnabled) {
            Manager::disableExtendsDiscount();
        }
    }

    /**
     * @param Basket       $basket
     * @param OrderStorage $storage
     *
     * @return int
     * @throws ArgumentNullException
     */
    protected function getMaxBonusPayment(Basket $basket, OrderStorage $storage): int
    {
        return floor(
            min($storage->getBonus(), $basket->getPrice() * BasketService::MAX_BONUS_PAYMENT)
        );
    }

    /**
     * @return OrderService
     * @throws ServiceCircularReferenceException
     * @throws ServiceNotFoundException
     * @throws \LogicException
     */
    protected function getOrderService(): OrderService
    {
        return Application::getInstance()->getContainer()->get(OrderService::class);
    }
}
