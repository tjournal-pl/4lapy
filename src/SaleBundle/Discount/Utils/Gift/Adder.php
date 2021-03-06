<?php

namespace FourPaws\SaleBundle\Discount\Utils\Gift;

use Bitrix\Main\LoaderException;
use Bitrix\Main\NotSupportedException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Sale\Internals\BasketTable;
use Exception;
use FourPaws\Catalog\Collection\OfferCollection;
use FourPaws\Catalog\Model\Offer;
use FourPaws\SaleBundle\Discount\Gift;
use FourPaws\SaleBundle\Discount\Utils\AdderInterface;
use FourPaws\SaleBundle\Discount\Utils\BaseDiscountPostHandler;
use FourPaws\SaleBundle\Discount\Utils\Manager;
use FourPaws\SaleBundle\Exception\BitrixProxyException;
use FourPaws\SaleBundle\Exception\InvalidArgumentException;
use FourPaws\SaleBundle\Exception\NotFoundException;
use RuntimeException;

/**
 * Class Adder
 *
 * @package FourPaws\SaleBundle\Discount\Utils
 */
class Adder extends BaseDiscountPostHandler implements AdderInterface
{
    /**
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws NotFoundException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     * @throws LoaderException
     * @throws NotSupportedException
     * @throws ObjectNotFoundException
     * @throws BitrixProxyException
     * @throws Exception
     */
    public function processOrder(): void
    {
        if (!$discount = $this->order->getDiscount()) {
            return;
        }
        // TODO Если подарок был а потом кончился необходимо здесь добавлять подарки которые есть
        $groups = Gift::getPossibleGiftGroups($this->order);

        foreach ($groups as $group) {
            if (\count($group) === 1) {
                $group = current($group);
            } else {
                throw new RuntimeException('TODO');
            }

            if (
                ($group = $this->basketService->getGiftGroupOfferCollection($group['discountId'], $this->order->getBasket()))
                && $group['list'] && $group['list'] instanceof OfferCollection
            ) {
                $existGiftsQuantity = $this->getExistGiftsQuantity($group);

                if ($group['count'] > $existGiftsQuantity) {
                    $group['count'] -= $existGiftsQuantity;
                    // Находим оффер с минимальной ценой
                    $minPrice = PHP_INT_MAX;
                    /** @var OfferCollection $OfferCollection */
                    $OfferCollection = $group['list'];
                    /** @var Offer $offer */
                    $offerId = 0;
                    foreach ($OfferCollection as $offer) {
                        if ($minPrice > $offer->getPrice()) {
                            $minPrice = $offer->getPrice();
                            $offerId = $offer->getId();
                        }
                    }
                    if ($offerId > 0) {
                        $this->addGift(
                            $offerId, $group['count'], $group['discountId'], $OfferCollection->count() === 1
                        );
                    }
                }
            }
        }
    }

    /**
     * @param int $offerId
     * @param int $quantity
     * @param int $discountId
     * @param bool $selected
     *
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentException
     * @throws InvalidArgumentException
     * @throws BitrixProxyException
     * @throws LoaderException
     * @throws ObjectNotFoundException
     * @throws Exception
     */
    protected function addGift(int $offerId, int $quantity, int $discountId, bool $selected = false)
    {
        if (!$offerId || !$quantity || !$discountId || $offerId <= 0 || $quantity <= 0 || $discountId <= 0) {
            return;
        }

        $fields = [
            'PRICE' => 0,
            'CUSTOM_PRICE' => 'Y',
            'NOTES' => 'Подарок',
            'PROPS' => [
                [
                    'NAME' => 'Подарок',
                    'CODE' => 'IS_GIFT',
                    'VALUE' => $discountId,
                    'SORT' => 100,
                ],
                [
                    'NAME' => 'Выбран пользователем',
                    'CODE' => 'IS_GIFT_SELECTED',
                    'VALUE' => $selected ? 'Y' : 'N',
                    'SORT' => 100,
                ],
            ]
        ];

        $basketItem = $this->basketService->addOfferToBasket(
            $offerId,
            $quantity,
            $fields,
            true,
            $this->order->getBasket()
        );
        // внутри файнал экшена мы крашим файнал экшен, который вызывается на добавлении элемента,
        // поэтому некоторые поля не устанавливаются
        // http://lurkmore.so/images/5/50/Yo_sobaka.jpg
        if ($basketItem->getPrice() > 0.0001) {
            /** @noinspection PhpInternalEntityUsedInspection */
            $basketItem->setFields(
                [
                    'PRICE'        => 0,
                    'CUSTOM_PRICE' => 'Y',
                    'NOTES'        => 'Подарок',
                ]
            );
            //FU BX
            BasketTable::update($basketItem->getId(),
                ['PRICE' => 0.0, 'CUSTOM_PRICE' => 'Y', 'NOTES' => 'Подарок']);
        }
    }

    /**
     * @param int|null $discountId
     * @param bool|null $selected
     *
     * @return array
     */
    public function getExistGifts(int $discountId = null, bool $selected = null): array
    {
        $result = Manager::getExistGifts($this->order);

        if (!empty($result) && null !== $discountId) {
            $result = array_filter($result, function ($elem) use ($discountId, $selected) {
                return (
                    $elem['discountId'] === $discountId
                    && (null === $selected || $selected === ($elem['selected'] === 'Y'))
                );
            });
        }
        return $result;
    }

    /**
     * @param array $group
     * @param bool|null $selected
     *
     * @return int
     */
    public function getExistGiftsQuantity(array $group, bool $selected = null): int
    {
        $quantity = 0;

        $list = [];
        if ($group['list'] instanceof OfferCollection) {
            /** @var OfferCollection $offerCollection */
            $offerCollection = $group['list'];
            $list = $offerCollection->getKeys();
        } elseif (\is_array($group['list'])) {
            $list = $group['list'];
        }

        $existGifts = $this->getExistGifts($group['discountId']);
        if (!empty($existGifts)) {
            foreach ($existGifts as $elem) {
                // Считаем только возможные подарки, остальные (которые,
                // например, были добавлены на предыдущем хите, но кончились на складе) будут удалены
                if (
                    \in_array($elem['offerId'], $list, true)
                    && (null === $selected || $selected === ($elem['selected'] === 'Y'))
                ) {
                    $quantity += $elem['quantity'];
                }
            }
        }
        return $quantity;
    }

    /**
     * @param int $offerId
     * @param int $discountId
     *
     * @param int $quantity
     * @throws BitrixProxyException
     * @throws LoaderException
     * @throws ObjectNotFoundException
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     */
    public function selectGift(int $offerId, int $discountId, int $quantity = 1): void
    {
        $possibleGiftGroups = Gift::getPossibleGiftGroups($this->order, $discountId);
        if (!isset($possibleGiftGroups[$discountId])) {
            throw new NotFoundException('Акция не найдена');
        }

        $group = $possibleGiftGroups[$discountId];
        if (\count($group) === 1) {
            $group = current($group);
        } else {
            throw new RuntimeException('TODO');
        }

        if (!\in_array($offerId, $group['list'], true)) {
            throw new NotFoundException('Подарок не может быть предоставлен в рамках данной акции');
        }

        if ($this->getExistGiftsQuantity($group, false) < 1) {
            throw new NotFoundException('Все подарки уже выбраны, сначала необходимо удалить выбранный подарок');
        }

        $existGifts = $this->getExistGifts($discountId);
        foreach ($existGifts as $existGift) {
            // Находим первый невыбранный подарок и херим его
            if ($existGift['selected'] === 'N') {
                if ($existGift['quantity'] > 1) {
                    $this->basketService->updateBasketQuantity($existGift['basketId'], $existGift['quantity'] - 1);
                } else {
                    $this->basketService->deleteOfferFromBasket($existGift['basketId']);
                }
                break;
            }
        }
        $this->addGift($offerId, $quantity, $discountId, true);
    }

    /**
     * @param array $offers
     * @throws BitrixProxyException
     */
    public function selectGifts(array $offers): void
    {
        $discountId = $offers[0]['actionId'];

        $possibleGiftGroups = Gift::getPossibleGiftGroups($this->order, $discountId);
        if (!isset($possibleGiftGroups[$discountId])) {
            throw new NotFoundException('Акция не найдена');
        }

        $group = $possibleGiftGroups[$discountId];
        if (\count($group) === 1) {
            $group = current($group);
        } else {
            throw new RuntimeException('TODO');
        }

        foreach ($offers as $offer) {
            $offerId = (int)$offer['offerId'];
            $quantity = (int)$offer['count'];
            if (!\in_array($offerId, $group['list'], true)) {
                throw new NotFoundException('Подарок не может быть предоставлен в рамках данной акции');
            }

//            if ($this->getExistGiftsQuantity($group, false) < 1) {
//                throw new NotFoundException('Все подарки уже выбраны, сначала необходимо удалить выбранный подарок');
//            }

            $existGifts = $this->getExistGifts($discountId);
            $existGiftsIds = [];
            foreach ($existGifts as $existGift) {
                $existGiftsIds[$existGift['offerId']] = $existGift;
            }
            if (in_array($offerId, array_keys($existGiftsIds))) {
                $basketId = (int)$existGiftsIds[$offerId]['basketId'];
                $basketQuantity = (int)$existGiftsIds[$offerId]['quantity'];
                $this->basketService->updateBasketQuantity($basketId, $basketQuantity + $quantity);
            } else {
                $this->addGift($offerId, $quantity, $discountId, true);
            }
        }
    }
}
