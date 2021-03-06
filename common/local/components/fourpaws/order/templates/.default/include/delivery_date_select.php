<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use FourPaws\DeliveryBundle\Entity\CalculationResult\CalculationResultInterface;
use FourPaws\SaleBundle\Enum\OrderStorage;

/**
 * @var OrderStorage                 $selectorStorage
 * @var string                       $selectorName
 * @var CalculationResultInterface[] $nextDeliveries
 * @var FourPawsOrderComponent       $component
 */
?>
<select class="b-select__block b-select__block--recall b-select__block--feedback-page js-select-recovery js-change-date js-pickup-date <?=$isHidden ? 'js-no-valid' : ''?>"
        <?=$isHidden ? 'disabled' : ''?>
        name="<?= $selectorName ?>">
    <option value="" disabled="disabled" selected="selected">выберите</option>
    <?php foreach ($nextDeliveries as $i => $nextDelivery) {
        if (FormatDate('d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()) == '01.01.2020' || FormatDate('d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()) == '02.01.2020') : ?>
        <?php continue; ?>
        <?php else : ?>
        <option value="<?= $i ?>" <?= ($selectorStorage->getDeliveryDate() === $i) ? 'selected="selected"' : '' ?> data-date-option="<?= FormatDate('l, Y-m-d', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>">
            <?= FormatDate('l, d.m.Y', $nextDelivery->getDeliveryDate()->getTimestamp()) ?>
        </option>
        <?php endif; ?>
    <?php } ?>
</select>
