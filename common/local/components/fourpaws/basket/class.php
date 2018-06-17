<?php
/**
 * Created by PhpStorm.
 * Date: 26.12.2017
 * Time: 18:04
 * @author      Makeev Ilya
 * @copyright   ADV/web-engineering co.
 */
declare(strict_types=1);

namespace FourPaws\Components;

use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketItemCollection;
use Bitrix\Sale\Order;
use Bitrix\Sale\PriceMaths;
use CBitrixComponent;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use FourPaws\App\Application;
use FourPaws\App\Exceptions\ApplicationCreateException;
use FourPaws\BitrixOrm\Model\ResizeImageDecorator;
use FourPaws\Catalog\Model\Offer;
use FourPaws\Catalog\Query\OfferQuery;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;
use FourPaws\Helpers\DateHelper;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\Detach\Adder;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponSessionStorage;
use FourPaws\SaleBundle\Repository\CouponStorage\CouponStorageInterface;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\ConstraintDefinitionException;
use FourPaws\UserBundle\Exception\InvalidIdentifierException;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;
use FourPaws\UserBundle\Service\UserService;
use RuntimeException;
use Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

/** @noinspection AutoloadingIssuesInspection */
/** @noinspection EfferentObjectCouplingInspection */

/**
 * Class BasketComponent
 * @package FourPaws\Components
 */
class BasketComponent extends CBitrixComponent
{
    /**
     * @var BasketService
     */
    public $basketService;
    /** @var array */
    public $offers;
    /**
     * @var DeliveryService
     */
    private $deliveryService;
    /**
     * @var UserService
     */
    private $currentUserService;
    /** @var array $images */
    private $images;
    /**
     * @var CouponSessionStorage
     */
    private $couponsStorage;

    private $promoDescriptions = [];

    /**
     * BasketComponent constructor.
     *
     * @param CBitrixComponent|null $component
     *
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws ApplicationCreateException
     */
    public function __construct(CBitrixComponent $component = null)
    {
        parent::__construct($component);
        $container = Application::getInstance()->getContainer();

        $this->basketService = $container->get(BasketService::class);
        $this->currentUserService = $container->get(CurrentUserProviderInterface::class);
        $this->couponsStorage = $container->get(CouponStorageInterface::class);
        $this->deliveryService = $container->get(DeliveryService::class);
    }

    /** @noinspection PhpMissingParentCallCommonInspection
     *
     * @return void
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws ApplicationCreateException
     * @throws Exception
     * @throws SystemException
     * @throws InvalidIdentifierException
     * @throws ConstraintDefinitionException
     * @throws \InvalidArgumentException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    public function executeComponent(): void
    {
        /** @var Basket $basket */
        $basket = $this->arParams['BASKET'];
        if (null === $basket || !\is_object($basket) || !($basket instanceof Basket)) {
            $basket = $this->basketService->getBasket();
        }


        $this->arResult['BASKET'] = $basket;

        // привязывать к заказу нужно для расчета скидок
        if (null === $order = $basket->getOrder()) {
            $order = Order::create(SITE_ID);
            $order->setBasket($basket);
            // но иногда он так просто не запускается
            if (!Manager::isExtendCalculated()) {
                $order->doFinalAction(true);
            }
        }

        $this->setItems($basket);

        // необходимо подгрузить подарки
        $this->loadPromoDescriptions();
        $this->setCoupon();
        $this->arResult['USER'] = null;
        $this->arResult['USER_ACCOUNT'] = null;
        try {
            $user = $this->currentUserService->getCurrentUser();
            $this->arResult['USER'] = $user;
            $this->arResult['MAX_BONUS_SUM'] = $this->basketService->getMaxBonusesForPayment();
        } /** @noinspection BadExceptionsProcessingInspection */
        catch (NotAuthorizedException $e) {
            /** в случае ошибки не показываем бюджет в большой корзине */
        }
        $this->arResult['POSSIBLE_GIFT_GROUPS'] = Gift::getPossibleGiftGroups($order);
        $this->arResult['POSSIBLE_GIFTS'] = Gift::getPossibleGifts($order);
        $this->calcTemplateFields();
        $this->checkSelectedGifts();
        $this->arResult['SHOW_FAST_ORDER'] = $this->deliveryService->getCurrentDeliveryZone() !== $this->deliveryService::ZONE_4;

        $this->includeComponentTemplate($this->getPage());
    }

    /**
     * @return UserService
     */
    public function getCurrentUserService(): UserService
    {
        return $this->currentUserService;
    }

    /**
     *
     * @param BasketItem $basketItem
     * @param bool       $onlyApplied
     *
     * @return array
     */
    public function getPromoLink(BasketItem $basketItem, bool $onlyApplied = false): array
    {
        $result = [];
        /**
         * @var BasketItemCollection $basketItemCollection
         * @var Order                $order
         */
        $applyResult = $this->arResult['DISCOUNT_RESULT'];
        $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketItem->getBasketCode()];
        if (!$basketDiscounts) {
            /** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
            foreach ($basketItem->getPropertyCollection() as $basketPropertyItem) {
                if ($basketPropertyItem->getField('CODE') === 'DETACH_FROM') {
                    $basketDiscounts = $applyResult['RESULT']['BASKET'][$basketPropertyItem->getField('VALUE')];
                }
            }
        }

        if ($basketDiscounts) {
            /** @noinspection ForeachSourceInspection */
            foreach (\array_column($basketDiscounts, 'DISCOUNT_ID') as $fakeId) {
                if ($onlyApplied && \in_array($fakeId, Adder::getSkippedDiscountsFakeIds(), true)) {
                    continue;
                }
                if ($this->promoDescriptions[$applyResult['DISCOUNT_LIST'][$fakeId]['REAL_DISCOUNT_ID']]) {
                    $result[] = $this->promoDescriptions[$applyResult['DISCOUNT_LIST'][$fakeId]['REAL_DISCOUNT_ID']];
                }
            }
        }

        return $result;
    }

    /**
     * @return DeliveryService
     */
    public function getDeliveryService(): DeliveryService
    {
        return $this->deliveryService;
    }

    /**
     * @param int $offerId
     *
     * @return Offer|null
     */
    public function getOffer(int $offerId): ?Offer
    {
        if ($offerId <= 0) {
            return null;
        }
        if (!isset($this->offers[$offerId])) {
            $this->offers[$offerId] = OfferQuery::getById($offerId);
        }
        return $this->offers[$offerId];
    }

    /**
     * @param int $offerId
     *
     * @return ResizeImageDecorator|null
     */
    public function getImage(int $offerId): ?ResizeImageDecorator
    {
        if ($offerId <= 0) {
            return null;
        }

        if (!isset($this->images[$offerId])) {
            $offer = $this->getOffer($offerId);
            $image = null;
            if ($offer !== null) {
                $images = $offer->getResizeImages(110, 110);
                $this->images[$offerId] = $images->last();
            }
        }
        return $this->images[$offerId];
    }

    /**
     * @param Basket $basket
     *
     * @return Basket|bool
     * @throws ServiceNotFoundException
     * @throws ServiceCircularReferenceException
     * @throws RuntimeException
     * @throws ApplicationCreateException
     * @throws ObjectNotFoundException
     * @throws SystemException
     * @throws Exception
     */
    private function setItems($basket)
    {
        $notAllowedItems = new ArrayCollection();
        $fastOrderClass = null;
        $this->arResult['OFFER_MIN_DELIVERY'] = [];
        $this->arResult['ONLY_PICKUP'] = [];
        $haveOrder = $basket->getOrder() instanceof Order;
        $deliveries = $this->getDeliveryService()->getByLocation();

        $delivery = null;
        foreach ($deliveries as $calculationResult) {
            if ($this->getDeliveryService()->isDelivery($calculationResult)) {
                $delivery = $calculationResult;
                break;
            }
        }

        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getProductId() === 0) {
                /** удаляет непонятно что в корзине */
                if (!$haveOrder) {
                    $basketItem->delete();
                }
                continue;
            }
            $offer = $this->getOffer((int)$basketItem->getProductId());
            $useOffer = $offer instanceof Offer && $offer->getId() > 0;
            if (!$useOffer) {
                /** если нет офера удаляем товар из корзины */
                if (!$haveOrder) {
                    $basketItem->delete();
                }
                continue;
            }

            if ($basketItem->isDelay()) {
                $notAllowedItems->add($basketItem);
            } else {
                if ((null === $delivery) ||
                    !(clone $delivery)->setStockResult(
                        $this->getDeliveryService()->getStockResultForOffer(
                            $offer,
                            $delivery,
                            (int)$basketItem->getQuantity(),
                            $basketItem->getPrice()
                        )
                    )->isSuccess()
                ) {
                    $this->arResult['ONLY_PICKUP'][] = $offer->getId();
                }
            }

            if ($offer->isByRequest()) {
                $dates = [];
                foreach ($deliveries as $calculationResult) {
                    $res = (clone $calculationResult)->setStockResult(
                        $this->getDeliveryService()->getStockResultForOffer(
                            $offer,
                            $calculationResult,
                            (int)$basketItem->getQuantity(),
                            $basketItem->getPrice()
                        )
                    );
                    if (!$res->isSuccess()) {
                        continue;
                    }
                    $dates[] = $res->getDeliveryDate();
                }

                if (!empty($dates)) {
                    /** @var \DateTime $date */
                    $date = min($dates);
                    $this->arResult['OFFER_MIN_DELIVERY'][$basketItem->getProductId()] = DateHelper::formatDate(
                        'XX',
                        $date->getTimestamp()
                    );
                }

                if (!$notAllowedItems->contains($basketItem)) {
                    $notAllowedItems->add($basketItem);
                }
            }
        }
        $this->arResult['NOT_ALLOWED_ITEMS'] = $notAllowedItems;

        return true;
    }

    /**
     *
     *
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws RuntimeException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     */
    private function checkSelectedGifts(): void
    {
        $this->arResult['SELECTED_GIFTS'] = [];
        if (\is_array($this->arResult['POSSIBLE_GIFT_GROUPS']) && !empty($this->arResult['POSSIBLE_GIFT_GROUPS'])) {
            foreach ($this->arResult['POSSIBLE_GIFT_GROUPS'] as $group) {
                if (\count($group) === 1) {
                    $group = current($group);
                } else {
                    throw new RuntimeException('TODO');
                }

                /** @noinspection PhpUndefinedMethodInspection */
                $this->arResult['SELECTED_GIFTS'][$group['discountId']] = $this->basketService
                    ->getAdder('gift')->getExistGifts($group['discountId'], true);
            }
        }
    }

    /**
     *
     *
     * @throws \Bitrix\Main\ArgumentNullException
     */
    private function calcTemplateFields(): void
    {
        $weight = $quantity = $basePrice = $price = 0;
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var BasketItem $basketItem */
        $orderableBasket = $basket->getOrderableItems();

        foreach ($orderableBasket as $basketItem) {
            $itemQuantity = (int)$basketItem->getQuantity();
            $weight += (float)$basketItem->getWeight() * $itemQuantity;
            $quantity += $itemQuantity;
            if (!isset($basketItem->getPropertyCollection()->getPropertyValues()['IS_GIFT'])) {
                $basePrice += (float)$basketItem->getBasePrice() * $itemQuantity;
                $price += (float)$basketItem->getPrice() * $itemQuantity;
            }
        }

        $this->arResult['BASKET_WEIGHT'] = $weight;
        $this->arResult['TOTAL_QUANTITY'] = $quantity;
        $this->arResult['TOTAL_DISCOUNT'] = PriceMaths::roundPrecision($basePrice - $price);
        $this->arResult['TOTAL_PRICE'] = $price;
        $this->arResult['TOTAL_BASE_PRICE'] = $basePrice;
    }

    /**
     * @return string
     */
    private function getPage(): string
    {
        $page = '';
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var Order $order */
        $order = $basket->getOrder();
        if (!Manager::isOrderNotEmpty($order)) {
            $page = 'empty';
        }
        return $page;
    }

    /**
     * Подгружает названия и ссылки на описания акций по XML_ID
     */
    private function loadPromoDescriptions(): void
    {
        /** @var Basket $basket */
        $basket = $this->arResult['BASKET'];
        /** @var Order $order */
        $order = $basket->getOrder();
        $applyResult = $order->getDiscount()->getApplyResult(true);
        $this->arResult['DISCOUNT_RESULT'] = $applyResult;

        if (\is_array($applyResult['DISCOUNT_LIST'])) {
            $discountMap = \array_column($applyResult['DISCOUNT_LIST'], 'REAL_DISCOUNT_ID', 'ID');
            $res = \CIBlockElement::GetList(
                ['ID' => 'ASC'],
                [
                    'PROPERTY_BASKET_RULES' => \array_values($discountMap),
                    'IBLOCK_CODE'           => IblockCode::SHARES,
                    'IBLOCK_TYPE'           => IblockType::PUBLICATION,
                ],
                false,
                false,
                ['NAME', 'DETAIL_PAGE_URL', 'PROPERTY_BASKET_RULES']
            );
            /** @noinspection PhpAssignmentInConditionInspection */
            while ($elem = $res->GetNext()) {
                if (\is_array($elem['PROPERTY_BASKET_RULES_VALUE'])) {
                    foreach ($elem['PROPERTY_BASKET_RULES_VALUE'] as $ruleId) {
                        $this->promoDescriptions[$ruleId] = [
                            'url'  => $elem['DETAIL_PAGE_URL'],
                            'name' => $elem['NAME'],
                        ];
                    }
                }
            }
        }
    }

    /**
     * Set coupon and coupon discount
     *
     * @return void
     */
    private function setCoupon(): void
    {
        $this->arResult['COUPON'] = $this->couponsStorage->getApplicableCoupon() ?? '';
        $this->arResult['COUPON_DISCOUNT'] = !empty($this->arResult['COUPON']) ? $this->basketService->getPromocodeDiscount() : 0;
    }
}
