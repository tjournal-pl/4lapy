<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global BasketMiniComponent $component
 *
 * @var array $arResult
 * @var array $arParams
 * @var Basket $basket
 */

use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketItem;
use FourPaws\Components\BasketMiniComponent;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\Helpers\WordHelper;

$basket = $arResult['BASKET'];
/** @var Basket $orderableItems */
$orderableItems = $component->getBasketItemsWithoutGifts($basket->getOrderableItems());

$countWithoutGifts =  $component->getBasketCountWithoutGifts($orderableItems);
$hasItems = $countWithoutGifts > 0;
if (true !== $arParams['IS_AJAX']) {
    echo '<div class="b-header-info__item b-header-info__item--cart">';
} ?>
    <a class="b-header-info__link<?php if ($hasItems) { ?> js-open-popover<?php } ?>"
       href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Корзина">
        <span class="b-icon">
            <?= new SvgDecorator('icon-cart', 16, 16) ?>
        </span>
        <span class="b-header-info__inner">Корзина</span>
        <span class="b-header-info__number js-count-products">
            <?= $countWithoutGifts; ?>
        </span>
    </a>
<?php if ($hasItems) {?>
    <div class="b-popover b-popover--cart js-popover">
        <div class="b-cart-popover">
            <span class="b-cart-popover__amount">
                <?= $countWithoutGifts ?>
                <?= WordHelper::declension(
                    $countWithoutGifts, ['Товар', 'Товара', 'Товаров']
                ) ?>
            </span>
            <span class="b-cart-popover__link" style="width: 58%">
                <a class="b-link b-link--popover-cart" href="<?= $arParams['PATH_TO_BASKET'] ?>" title="Редактировать">
                    Редактировать
                </a>
            </span>
            <a class="b-link b-link--popover-cart-mobile" href="<?= $arParams['PATH_TO_BASKET'] ?>"
               title="Редактировать">
                Редактировать товары в корзине
            </a>
            <a class="b-button b-button--popover-cart" href="<?= $arParams['PATH_TO_ORDER'] ?>" title="Оформить заказ">
                Оформить заказ
            </a>
            <?php
            if ($hasItems) {
                /** @var BasketItem $basketItem */
                foreach ($orderableItems->getBasketItems() as $basketItem) {
                    $offer = $component->getOffer((int)$basketItem->getProductId());
                    $image = $component->getImage((int)$basketItem->getProductId()); ?>
                    <div class="b-cart-item">
                        <div class="b-cart-item__image-wrapper">
                            <?php if (null !== $image) { ?>
                                <img class="b-cart-item__image"
                                     src="<?= $image ?>"
                                     alt="<?= $basketItem->getField('NAME') ?>"
                                     title="<?= $basketItem->getField('NAME') ?>"/>
                            <?php } ?>
                        </div>
                        <div class="b-cart-item__info">
                            <div class="b-clipped-text b-clipped-text--cart-popover">
                                <a class="b-cart-item__name"
                                   href="<?= $basketItem->getField('DETAIL_PAGE_URL') ?>"
                                   title="<?= $basketItem->getField('NAME') ?>">
                                    <?php if ($offer) { ?>
                                        <span class="span-strong"><?= $offer->getProduct()->getBrandName() ?> </span>
                                    <?php } ?>
                                    <?= $basketItem->getField('NAME') ?>
                                </a>
                            </div>
                            <?php if ($basketItem->getQuantity() > 0 && $basketItem->getWeight() > 0) { ?>
                                <span class="b-cart-item__weight">
                                    <?= WordHelper::showWeight(
                                        $basketItem->getWeight() * $basketItem->getQuantity(), true
                                    ) ?>
                                </span>
                            <?php } ?>
                            <span class="b-cart-item__amount">(<?= $basketItem->getQuantity() ?> шт.)</span>
                        </div>
                    </div>
                <?php }
            } ?>
        </div>
    </div>
<?php }

if (true !== $arParams['IS_AJAX']) {
    echo '</div>';
}
