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
_log_array($arResult);
switch ($arResult['CURRENT_STAGE']) {
    case 'initial':
        // стартовая страница
        include __DIR__.'/stage.initial.php';
        break;

    case 'user_list':
        // запрос списка пользователей (ajax)
        include __DIR__.'/stage.user_list.php';
        break;

    case 'user_auth':
        // авторизация под пользовталем (ajax, json response)
        include __DIR__.'/stage.user_auth.php';
        break;
}
