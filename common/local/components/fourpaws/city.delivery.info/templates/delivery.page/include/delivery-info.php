<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Collection\IntervalCollection;
use FourPaws\DeliveryBundle\Entity\Interval;
use FourPaws\DeliveryBundle\Service\DeliveryService;
use FourPaws\Helpers\WordHelper;

/** @var array $delivery */ ?>
<div class="b-delivery__delivery-type-row">
    <div class="b-delivery__delivery-type-row__title">
        <p>Курьерская доставка</p>
    </div>
    <div class="b-delivery__delivery-type-row__price">
        <p>Стоимость</p>
        <span><?= WordHelper::numberFormat($delivery['PRICE'], 0) ?> ₽</span>
        <?php if (!empty($delivery['FREE_FROM'])) { ?>
            <span>Бесплатно при заказе от <?= WordHelper::numberFormat($delivery['FREE_FROM'], 0) ?> ₽</span>
        <?php } ?>
    </div>
    <div class="b-delivery__delivery-type-row__day">
        <p>Получение</p>
        <?php if ($delivery['CODE'] === DeliveryService::INNER_DELIVERY_CODE) { ?>
            <span>В день оформления заказа (при оформлении до 13:00) или на следующий день</span>
        <?php } else { ?>
            <span>
                Через <?= $delivery['PERIOD_FROM'] ?> <?= WordHelper::declension(
                    $delivery['PERIOD_FROM'],
                    ['день', 'дня', 'дней']
                ) ?>
            </span>
            <?php
        } ?>
    </div>
    <div class="b-delivery__delivery-type-row__time">
        <?php
        /** @var IntervalCollection $intervals */
        $intervals = $delivery['INTERVALS'];


        if (!$intervals->isEmpty()) { ?>
            <p>Время</p>
            <?php
            $intervalData = [];
            /** @var Interval $interval */
            foreach ($intervals as $i => $interval) {
                $intervalData[] = (string)$interval;
            } ?>
            <span><?= implode(', ', $intervalData) ?></span>
            <?php
        } ?>
    </div>
</div>
