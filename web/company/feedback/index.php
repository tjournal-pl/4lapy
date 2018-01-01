<?php

use FourPaws\Enum\Form;
use FourPaws\Helpers\FormHelper;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Обратная связь');

echo '<div class="b-feedback-page">
            <h1 class="b-title b-title--h1 b-title--feedback">';$APPLICATION->ShowTitle(false); echo '</h1>
            <div class="b-feedback-page__wrapper">';
?>
<?php $APPLICATION->IncludeComponent(
    'bitrix:form.result.new',
    'feedback',
    [
        'CACHE_TIME'             => '3600000',
        'CACHE_TYPE'             => 'A',
        'CHAIN_ITEM_LINK'        => '',
        'CHAIN_ITEM_TEXT'        => '',
        'EDIT_URL'               => '',
        'IGNORE_CUSTOM_TEMPLATE' => 'Y',
        'LIST_URL'               => '',
        'SEF_MODE'               => 'N',
        'SUCCESS_URL'            => '',
        'USE_EXTENDED_ERRORS'    => 'Y',
        'VARIABLE_ALIASES'       => [
            'RESULT_ID'   => 'RESULT_ID',
            'WEB_FORM_ID' => 'WEB_FORM_ID',
        ],
        'WEB_FORM_ID'            => FormHelper::getIdByCode(Form::FEEDBACK),
    ]
); ?>
<?php
echo '</div></div>';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>