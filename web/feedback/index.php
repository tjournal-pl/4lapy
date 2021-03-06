<?php

use FourPaws\Decorators\SvgDecorator;
use FourPaws\Enum\Form;
use FourPaws\Helpers\FormHelper;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php';
$APPLICATION->SetTitle('Обратная связь');
echo '<div class="b-feedback-page">
            <h1 class="b-title b-title--h1 b-title--feedback">';
$APPLICATION->ShowTitle(false);
echo '</h1>
            <div class="b-feedback-page__wrapper';
echo $_SESSION['FEEDBACK_SUCCESS'] === 'Y' ? ' b-feedback-page__wrapper--flex-center' : '';
echo '">';
?>
<?php
if ($_SESSION['FEEDBACK_SUCCESS'] === 'Y') {
    ?>
    <div class="b-feedback-page__thanks">
        <div class="b-feedback-page__icon">
        <span class="b-icon b-icon--feedback">
            <?= new SvgDecorator('icon-check-color', 25, 25) ?>
        </span>
        </div>
        <p class="b-feedback-page__text-thanks">Спасибо за Ваш отзыв. Мы внимательно ознакомимся с ним и свяжемся с Вами в ближайшее время!</p>
        <p class="b-error-page" style="margin: 20px 0 0 0 !important;"><a href="/catalog/">в каталог</a></p>
    </div>
    <?php
    unset($_SESSION['FEEDBACK_SUCCESS']);
} else {
    $APPLICATION->IncludeComponent(
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
    );
} ?>
<?php
echo '</div></div>
<div class="b-preloader b-preloader--fixed">
    <div class="b-preloader__spinner">
        <img class="b-preloader__image" src="/static/build/images/inhtml/spinner.svg" alt="spinner" title=""/>
    </div>
</div>';
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php'; ?>