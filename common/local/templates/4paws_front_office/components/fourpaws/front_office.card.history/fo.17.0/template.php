<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var FourPawsFrontOfficeCardRegistrationComponent $component
 * @var CBitrixComponentTemplate $this
 * @var string $templateName
 * @var string $componentPath
 */

switch ($arResult['CURRENT_STAGE']) {
    case 'initial':
        include __DIR__.'/stage.initial.php';
        break;

    case 'history':
        include __DIR__.'/stage.history.php';
        break;

    case 'cheque_details':
        include __DIR__.'/stage.cheque_details.php';
        break;
}
