<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var Basket $basket
 * @var \FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface $selectedDelivery
 */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use Bitrix\Main\Grid\Declension;
use FourPaws\App\Application;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\DeliveryBundle\Entity\StockResult;
use FourPaws\Helpers\CurrencyHelper;
use FourPaws\Helpers\WordHelper;
use FourPaws\DeliveryBundle\Entity\CalculationResult\BaseResult;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\StoreBundle\Entity\Store;
use FourPaws\SaleBundle\Entity\OrderStorage;

$basket = $arResult['BASKET'];
$basketQuantity = array_sum($basket->getQuantityList());
$basketWeight = $basket->getWeight();
$basketPrice = $basket->getPrice();

/* @todo отображение акционных товаров */

/** @var OrderStorage $storage */
$storage = $arResult['STORAGE'];

/** @var DeliveryService $deliveryService */
$deliveryService = Application::getInstance()->getContainer()->get('delivery.service');

$showPickupContainer = false;
$showDelayedItems = false;

$availableItems = [];
$availableWeight = 0;
$availablePrice = 0;
$availableQuantity = 0;
$delayedItems = [];
$delayedWeight = 0;
$delayedPrice = 0;
$delayedQuantity = 0;
$selectedDelivery = $arResult['SELECTED_DELIVERY'];

if ($deliveryService->isPickup($selectedDelivery)) {
    $showPickupContainer = true;

    /** @var Store $selectedShop */
    $selectedShop = $arResult['SELECTED_SHOP'];
    $stockResult = $selectedDelivery->getStockResult()->filterByStore($selectedShop);

    $available = $stockResult->getAvailable();
    $availableWeight = 0;
    $availablePrice = 0;
    // если нет доступных товаров, частичного получения не будет
    if ($available->isEmpty()) {
        $available = $stockResult->getDelayed();
        $availableQuantity = $available->getAmount();
        foreach ($available as $item) {
            $availableItems[] = [
                'name'     => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price'    => $item->getPrice(),
            ];

            $availablePrice += $item->getPrice() * $item->getAmount();
            $availableWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
        }
    } else {
        $availableQuantity = $available->getAmount();
        $availableItems = [];
        /** @var StockResult $item */
        foreach ($available as $item) {
            $availableItems[] = [
                'name'     => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price'    => $item->getPrice(),
            ];

            $availablePrice += $item->getPrice() * $item->getAmount();
            $availableWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
        }

        $delayed = $stockResult->getDelayed();
        $delayedWeight = 0;
        $delayedPrice = 0;
        $delayedQuantity = $delayed->getAmount();
        $delayedItems = [];
        /** @var StockResult $item */
        foreach ($delayed as $item) {
            $delayedItems[] = [
                'name'     => $item->getOffer()->getName(),
                'quantity' => $item->getAmount(),
                'price'    => $item->getPrice(),
            ];

            $delayedPrice += $item->getPrice() * $item->getAmount();
            $delayedWeight += $item->getOffer()->getCatalogProduct()->getWeight() * $item->getAmount();
        }

        if (!$delayed->isEmpty()) {
            $showDelayedItems = true;
        }
    }
}

?>
<?php /* отображается на 2 шаге, когда выбрана курьерская доставка */ ?>
<aside class="b-order__list js-list-orders-static" <?= !$showPickupContainer ? '' : 'style="display:none"' ?>>
    <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title">
        <span class="js-mobile-title-order">Заказ: <?= $basketQuantity ?> <?= (new Declension(
                'товар',
                'товара',
                'товаров'
            ))->get(
                $basketQuantity
            ) ?>
            (<?= WordHelper::showWeight($basketWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
                $basketPrice,
                false
            ) ?>
    </h4>
    <div class="b-order-list b-order-list--aside js-full-list js-popup-mobile">
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <?php /** @var BasketItem $item */ ?>
            <?php foreach ($basket as $item) { ?>
                <li class="b-order-list__item b-order-list__item--aside js-full-list">
                    <div class="b-order-list__order-text b-order-list__order-text--aside js-full-list">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                <?= $item->getField('NAME') ?>
                                <?php if ($item->getQuantity() > 1) { ?>
                                    (<?= $item->getQuantity() ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--aside js-full-list">
                        <?= CurrencyHelper::formatPrice($item->getQuantity() * $item->getPrice()) ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
</aside>
<?php /* отображается на 2 шаге, когда выбран самовывоз */ ?>
<aside class="b-order__list js-list-orders-cont" <?= $showPickupContainer ? '' : 'style="display:none"' ?>>
    <h4 class="b-title b-title--order-list js-popup-mobile-link js-full-list-title">
        Заказ: <?= $availableQuantity ?> <?= (new Declension('товар', 'товара', 'товаров'))->get(
            $availableQuantity
        ) ?>
        (<?= WordHelper::showWeight($availableWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
            $availablePrice,
            false
        ) ?>
    </h4>
    <div class="b-order-list js-popup-mobile js-full-list">
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
            заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <?php foreach ($availableItems as $item) { ?>
                <li class="b-order-list__item">
                    <div class="b-order-list__order-text">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                <?= $item['name'] ?>
                                <?php if ($item['quantity'] > 1) { ?>
                                    (<?= $item['quantity'] ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value">
                        <?= CurrencyHelper::formatPrice($item['price'] * $item['quantity']) ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <h4 class="b-title b-title--order-list js-popup-mobile-link js-basket-link js-parts-list-title"
        <?= !$showDelayedItems ? 'style="display:none"' : '' ?>>
        <span class="js-mobile-title-order">Останется в корзине: <?= $delayedQuantity ?></span>
        <?= (new Declension('товар', 'товара', 'товаров'))->get(
            $delayedQuantity
        ) ?> (<?= WordHelper::showWeight($delayedWeight, true) ?>) на сумму <?= CurrencyHelper::formatPrice(
            $delayedPrice,
            false
        ) ?>
    </h4>
    <div class="b-order-list b-order-list--aside js-popup-mobile js-parts-list"
        <?= !$showDelayedItems ? 'style="display:none"' : '' ?>>
        <a class="b-link b-link--popup-back b-link--popup-choose-shop js-popup-mobile-close">Информация о
            заказе</a>
        <ul class="b-order-list__list js-order-list-block">
            <?php foreach ($delayedItems as $item) { ?>
                <li class="b-order-list__item b-order-list__item--aside">
                    <div class="b-order-list__order-text b-order-list__order-text--aside">
                        <div class="b-order-list__clipped-text">
                            <div class="b-order-list__text-backed">
                                <?= $item['name'] ?>
                                <?php if ($item['quantity'] > 1) { ?>
                                    (<?= $item['quantity'] ?> шт)
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <div class="b-order-list__order-value b-order-list__order-value--aside">
                        <?= CurrencyHelper::formatPrice($item['price'] * $item['quantity']) ?>
                    </div>
                </li>
            <?php } ?>
        </ul>
    </div>
    <div class="b-order__link-wrapper"
        <?= !$showDelayedItems ? 'style="display:none"' : '' ?>>
        <a class="b-link b-link--order-gotobusket b-link--order-gotobusket"
           href="/cart"
           title="Вернуться в корзину">
            <span class="b-icon b-icon--order-busket">
                <?= new SvgDecorator('icon-reason', 16, 16) ?>
            </span>
            <span class="b-link__text b-link__text--order-gotobusket">Вернуться в корзину</span>
        </a>
    </div>
</aside>
