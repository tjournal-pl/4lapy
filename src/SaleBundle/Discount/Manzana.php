<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co
 */

namespace FourPaws\SaleBundle\Discount;

use Adv\Bitrixtools\Tools\Log\LazyLoggerAwareTrait;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;
use FourPaws\App\Application as App;
use FourPaws\External\Exception\ManzanaPromocodeUnavailableException;
use FourPaws\External\Manzana\Dto\ChequePosition;
use FourPaws\External\Manzana\Dto\Coupon;
use FourPaws\External\Manzana\Dto\SoftChequeResponse;
use FourPaws\External\Manzana\Exception\ExecuteException;
use FourPaws\External\ManzanaPosService;
use FourPaws\PersonalBundle\Exception\CouponIsNotAvailableForUseException;
use FourPaws\PersonalBundle\Service\PersonalOffersService;
use FourPaws\PersonalBundle\Service\PiggyBankService;
use FourPaws\SaleBundle\Helper\PriceHelper;
use FourPaws\SaleBundle\Service\BasketService;
use FourPaws\UserBundle\Exception\NotAuthorizedException;
use FourPaws\UserBundle\Service\UserService;
use Psr\Log\LoggerAwareInterface;
use RuntimeException;

/**
 * Class Manzana
 *
 * @package FourPaws\SaleBundle\Discount
 */
class Manzana implements LoggerAwareInterface
{
    use LazyLoggerAwareTrait;

    /**
     * @var BasketService
     */
    private $basketService;
    /**
     * @var ManzanaPosService
     */
    private $manzanaPosService;
    /**
     * @var PersonalOffersService
     */
    private $personalOffersService;
    /**
     * @var string
     */
    private $promocode = '';
    /**
     * @var UserService
     */
    private $userService;
    /**
     * @var float
     */
    private $discount = 0.0;

    /**
     * Manzana constructor.
     *
     * @param BasketService     $basketService
     * @param ManzanaPosService $manzanaPosService
     * @param UserService       $userService
     */
    public function __construct(BasketService $basketService, ManzanaPosService $manzanaPosService, UserService $userService)
    {
        $this->basketService = $basketService;
        $this->manzanaPosService = $manzanaPosService;
        $this->userService = $userService;
    }

    /**
     * @param string $promocode
     */
    public function setPromocode(string $promocode): void
    {
        $this->promocode = trim($promocode);
    }

    /**
     * @param Order|null $order
     *
     * @throws ArgumentOutOfRangeException
     * @throws ManzanaPromocodeUnavailableException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function calculate(?Order $order = null): void
    {
        if ($order) {
            $basket = $order->getBasket();
        } else {
            $basket = $this->basketService->getBasket();
        }
        /** @var Basket $basket */
        $basket = $basket->getOrderableItems();

        if (!$basket->count()) {
            /**
             * Empty basket
             */
            return;
        }

        $price = $basket->getPrice();

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $user = $this->userService->getCurrentUser();
            $card = $user->getDiscountCardNumber();
        } catch (NotAuthorizedException $e) {
            $card = '';
        }

        $request = $this->manzanaPosService->buildRequestFromBasket($basket, $card, $this->basketService);

        try {
            if ($this->promocode) {
                /** @var PiggyBankService $piggyBankService */
                $piggyBankService = App::getInstance()->getContainer()->get('piggy_bank.service');
                $piggyBankService->checkPiggyBankCoupon($this->promocode);

                $response = $this->manzanaPosService->processChequeWithCoupons($request, $this->promocode);

                $this->checkPromocodeByResponse($response, $this->promocode);

                /**
                 * @todo переделать костыль
                 */
                $this->saveCouponDiscount($response);
            } else {
                $response = $this->manzanaPosService->processCheque($request);
            }

            $this->recalculateBasketFromResponse($basket, $response);
            $this->discount = $price - $basket->getPrice();
        } catch (ExecuteException|CouponIsNotAvailableForUseException $e) {
            /** @var BasketItem $item */
            foreach ($basket as $item) {
                $price = PriceHelper::roundPrice($item->getPrice());
                /** @noinspection PhpInternalEntityUsedInspection */
                $item->setFieldsNoDemand([
                    'PRICE' => $price,
                    'DISCOUNT_PRICE' => $item->getBasePrice() - $price,
                    'CUSTOM_PRICE' => 'Y'
                ]);
            }

            if ($e instanceof ExecuteException) {
                $this->log()->error(
                    \sprintf(
                        'Manzana recalculate error: %s',
                        $e->getMessage()
                    )
                );
            } else if ($e instanceof CouponIsNotAvailableForUseException) {
                $this->log()->error(
                    \sprintf(
                        'Coupon checking error: %s',
                        $e->getMessage()
                    )
                );
            }
        }
    }

    /**
     * @param array      $promocodes
     * @param Order|null $order
     *
     * @return array
     *
     * @throws \Bitrix\Main\ObjectException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getAllowPromocodes(array $promocodes, ?Order $order = null): array
    {
        if ($order) {
            $basket = $order->getBasket();
        } else {
            $basket = $this->basketService->getBasket();
        }
        /** @var Basket $basket */
        $basket = $basket->getOrderableItems();

        if (!$basket->count()) {
            /**
             * Empty basket
             */
            return [];
        }

        try {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            $user = $this->userService->getCurrentUser();
            $card = $user->getDiscountCardNumber();
        } catch (NotAuthorizedException $e) {
            $card = '';
        }

        $request = $this->manzanaPosService->buildRequestFromBasket($basket, $card, $this->basketService);

        foreach ($promocodes as $key => $promocode) {
            try {
                if ($promocode) {
                    $promocode = \htmlspecialchars($promocode);
                    $personalOfferService = $this->getPersonalOffersService();
                    $personalOfferService->checkCoupon($promocode);
                    $this->setPromocode($promocode);
                    $request->addCoupon($promocode);
                    $response = $this->manzanaPosService->execute($request, true);
                    $apply = false;
                    foreach ($response->getCoupons() as $coupon) {
                        if ($coupon->isApplied()) {
                            $apply = true;
                            break;
                        }
                    }
                    if (!$apply) {
                        unset($promocodes[$key]);
                    }
                } else {
                    unset($promocodes[$key]);
                }
            } catch (ExecuteException|CouponIsNotAvailableForUseException|ManzanaPromocodeUnavailableException $e) {
                unset($promocodes[$key]);
            }
        }

        return $promocodes;
    }

    /**
     * @param Basket             $basket
     * @param SoftChequeResponse $response
     *
     * @throws ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     */
    public function recalculateBasketFromResponse(Basket $basket, SoftChequeResponse $response): void
    {
        $manzanaItems = $response->getItems();

        /**
         * @var BasketItem $item
         */
        foreach ($basket as $item) {
            $basketCode = (int)str_replace('n', '', $item->getBasketCode());

            $manzanaItems->map(function (ChequePosition $position) use ($basketCode, $item) {
                if ($position->getChequeItemNumber() === $basketCode) {
                    $price = PriceHelper::roundPrice($position->getSummDiscounted() / $position->getQuantity());

                    /** @noinspection PhpInternalEntityUsedInspection */
                    $item->setFieldsNoDemand([
                        'BASE_PRICE' => $item->getBasePrice(),
                        'PRICE' => $price,
                        'DISCOUNT_PRICE' => $item->getBasePrice() - $price,
                        'CUSTOM_PRICE' => 'Y',
                    ]);
                }
            });
        }
    }

    /**
     * @param SoftChequeResponse $response
     * @param string             $promocode
     *
     * @throws ManzanaPromocodeUnavailableException
     */
    public function checkPromocodeByResponse(SoftChequeResponse $response, string $promocode): void
    {
        $applied = false;

        if ($response->getCoupons()) {
            $applied = $response->getCoupons()->filter(function (Coupon $coupon) use ($promocode) {
                return $coupon->isApplied() && $coupon->getNumber() === $promocode;
            })->count() > 0;
        }

        if (!$applied) {
            throw new ManzanaPromocodeUnavailableException(
                \sprintf(
                    'Promocode %s is not found or unavailable in current context',
                    $this->promocode
                )
            );
        }
    }

    /**
     * @param SoftChequeResponse $response
     */
    private function saveCouponDiscount(SoftChequeResponse $response): void
    {
        $this->basketService->setPromocodeDiscount($response->getSumm() - $response->getSummDiscounted());
    }

    /**
     * @return float
     */
    public function getDiscount(): float
    {
        return $this->discount;
    }

    /**
     * @param int $discount
     *
     * @return Manzana
     */
    public function setDiscount(int $discount): Manzana
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * @return PersonalOffersService|object
     */
    protected function getPersonalOffersService()
    {
        if ($this->personalOffersService)
        {
            return $this->personalOffersService;
        }

        $this->personalOffersService = App::getInstance()->getContainer()->get('personal_offers.service');

        return $this->personalOffersService;
    }
}
