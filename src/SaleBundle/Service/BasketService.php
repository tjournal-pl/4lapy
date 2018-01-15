<?php
declare(strict_types=1);

namespace FourPaws\SaleBundle\Service;

use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Compatible\DiscountCompatibility;
use Bitrix\Sale\Fuser;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use FourPaws\UserBundle\Service\CurrentUserProviderInterface;

/**
 * Class BasketService
 * @package FourPaws\SaleBundle\Service
 */
class BasketService
{
    /** @var int */
    private $fUser = null;
    /** @var Basket */
    private $basket = null;
    /** @var array */
    private $context = null;
    /**
     * @var CurrentUserProviderInterface
     */
    private $currentUserProvider;

    /**
     * BasketService constructor.
     *
     * @param CurrentUserProviderInterface $currentUserProvider
     */
    public function __construct(CurrentUserProviderInterface $currentUserProvider)
    {
        $this->currentUserProvider = $currentUserProvider;
    }

    /**
     * @param int $offerId
     * @param int $quantity
     *
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     *
     * @return bool
     */
    public function addOfferToBasket(int $offerId, int $quantity = null): bool
    {

        if ($quantity < 1) {
            throw new InvalidArgumentException('Wrong $quantity');
        }
        if ($offerId < 1) {
            throw new InvalidArgumentException('Wrong $offerId');
        }

        $fields = [
            'PRODUCT_ID' => $offerId,
            'QUANTITY' => $quantity,
            'MODULE' => 'catalog',
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
//            'PROPS' => [[
//                'NAME' => 'Тест',
//                'CODE' => 'TEST',
//                'VALUE' => 1,
//                'SORT' => 100,
//            ]]
        ];

        // вызов новго провайдера
//        \Bitrix\Sale\Internals\Catalog\Provider::getProductData(
//            $this->getBasket(), $this->getContext()
//        );

        $result = \Bitrix\Catalog\Product\Basket::addProductToBasketWithPermissions(
            $this->getBasket(),
            $fields,
            $this->getContext()
        );

        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        $this->getBasket()->save();

        return true;
    }


    /**
     * @param int $basketId
     *
     * @throws \Bitrix\Main\ObjectNotFoundException
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Exception
     *
     * @return bool
     */
    public function deleteOfferFromBasket(int $basketId): bool
    {
        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }
        $basketItem = $this->getBasket()->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('BasketItem');
        }
        $result = $basketItem->delete();
        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        $this->getBasket()->save();
        return true;
    }

    /**
     *
     *
     * @param int $basketId
     * @param int|null $quantity
     *
     * @throws \Exception
     * @throws \FourPaws\SaleBundle\Exception\BitrixProxyException
     * @throws \FourPaws\SaleBundle\Exception\NotFoundException
     * @throws \FourPaws\SaleBundle\Exception\InvalidArgumentException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     *
     * @return bool
     */
    public function updateBasketQuantity(int $basketId, int $quantity = null): bool
    {
        if ($quantity < 1) {
            throw new InvalidArgumentException('Wrong $quantity');
        }
        if ($basketId < 1) {
            throw new InvalidArgumentException('Wrong $basketId');
        }
        $basketItem = $this->getBasket()->getItemById($basketId);
        if (null === $basketItem) {
            throw new NotFoundException('BasketItem');
        }
        $result = $basketItem->setField('QUANTITY', $quantity);
        if (!$result->isSuccess()) {
            throw new BitrixProxyException($result);
        }
        $this->getBasket()->save();
        return true;
    }

    /**
     *
     *
     * @param bool|null $reload
     *
     * @return Basket
     */
    public function getBasket(bool $reload = null): Basket
    {
        if (null === $this->basket || $reload) {
            /** @var Basket $basket */
            /** @noinspection PhpInternalEntityUsedInspection */
            DiscountCompatibility::stopUsageCompatible();
            $this->basket = Basket::loadItemsForFUser($this->currentUserProvider->getCurrentFUserId(), SITE_ID);
        }
        return $this->basket;
    }

    /**
     *
     *
     * @return string
     */
    public static function getMiniBasketHtml(): string
    {
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'bitrix:sale.basket.basket.line',
            'header.basket',
            [
                'COMPONENT_TEMPLATE' => 'header.basket',
                'PATH_TO_BASKET' => '/cart/',
                'PATH_TO_ORDER' => '/order/make/',
                'SHOW_NUM_PRODUCTS' => 'Y',
                'SHOW_TOTAL_PRICE' => 'Y',
                'SHOW_EMPTY_VALUES' => 'Y',
                'SHOW_PERSONAL_LINK' => 'Y',
                'PATH_TO_PERSONAL' => '/personal/',
                'SHOW_AUTHOR' => 'N',
                'PATH_TO_REGISTER' => '',
                'PATH_TO_AUTHORIZE' => '',
                'PATH_TO_PROFILE' => '/personal/',
                'SHOW_PRODUCTS' => 'Y',
                'SHOW_DELAY' => 'N',
                'SHOW_NOTAVAIL' => 'Y',
                'SHOW_IMAGE' => 'Y',
                'SHOW_PRICE' => 'Y',
                'SHOW_SUMMARY' => 'N',
                'POSITION_FIXED' => 'N',
                'HIDE_ON_BASKET_PAGES' => 'N',
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        return ob_get_clean();
    }

    /**
     *
     *
     * @return string
     */
    public static function getBasketHtml(): string
    {
        global $APPLICATION;
        ob_start();
        $APPLICATION->IncludeComponent(
            'fourpaws:basket',
            '',
            [],
            false,
            ['HIDE_ICONS' => 'Y']
        );
        return ob_get_clean();
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return [
            'SITE_ID' => SITE_ID,
            'USER_ID' => $this->currentUserProvider->getCurrentUserId()
        ];
    }
}
