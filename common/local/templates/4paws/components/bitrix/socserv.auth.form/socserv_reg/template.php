<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Text\HtmlFilter;
use FourPaws\Decorators\SvgDecorator;

/**
 * @var array $arParams
 */ ?>

<ul class="b-registration__social-wrapper">
    <?php
    if (\is_array($arParams['~AUTH_SERVICES']) && !empty($arParams['~AUTH_SERVICES'])) {
        foreach ($arParams['~AUTH_SERVICES'] as $service) {
            ?>
            <li class="b-social-block">
                <a class="b-social-block__link"
                   id="bx_socserv_icon_<?= $service['ICON'] ?>"
                   href="javascript:void(0)"
                   onclick="<?= HtmlFilter::encode($service['ONCLICK']) ?? '' ?>"
                   title="<?= HtmlFilter::encode($service['NAME']) ?>"
                >
                <span class="b-icon b-icon--social b-icon--<?= $service['ICON'] ?>-registration">
                    <?= new SvgDecorator(
                        'icon-' . $service['ICON_DECORATOR']['CODE'],
                        $service['ICON_DECORATOR']['WIDTH'],
                        $service['ICON_DECORATOR']['HEIGHT']
                    ) ?>
                </span>
                </a>
            </li>
            <?php
        }
    }
    ?>
</ul>
