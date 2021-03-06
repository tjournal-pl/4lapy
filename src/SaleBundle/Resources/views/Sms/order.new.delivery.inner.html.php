<?php
/**
 * @var string $accountNumber
 * @var bool $userRegistered
 * @var string $phone
 * @var string $email
 * @var float $price
 * @var float $bonusSum
 * @var \DateTime $deliveryDate
 * @var string $deliveryCode
 * @var string|false $deliveryInterval
 */

$deliveryDateFormatted = $deliveryDate ? $deliveryDate->format('d.m.Y') : '';

if ($deliveryInterval && $deliveryDate) {
    $deliveryDateFormatted .= ' . Время: ' . $deliveryInterval;
}

?>
Спасибо. Ваш заказ № <?= $accountNumber ?> на сумму <?= $price - $bonusSum ?> руб. оформлен! И будет доставлен <?= $deliveryDateFormatted ?>