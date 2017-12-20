<?php
/**
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult['VARIABLES']['SECTION_CODE'] === $arResult['VARIABLES']['SECTION_CODE_PATH']) {
    include __DIR__ . '/sections.php';
    return;
}

dump($arResult);
