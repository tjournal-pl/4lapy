<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $USER;
if (!$USER->IsAdmin())
{
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

$APPLICATION->IncludeComponent(
    'fourpaws:personal.piggybank',
    '',
    [],
    $component,
    ['HIDE_ICONS' => 'Y']
);
