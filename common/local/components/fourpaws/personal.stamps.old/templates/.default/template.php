<?php

use Bitrix\Main\Grid\Declension;
use FourPaws\PersonalBundle\Service\StampService;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
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

$marksDeclension = new Declension('марку', 'марки', 'марок');

?>
<div class="b-kopilka b-kopilka--exchange-discount">
    <h2 class="b-title b-kopilka__title">Марки</h2>

    <div class="b-kopilka__details">
        <div class="b-kopilka__ticket-mark">
            <div class="title-ticket-mark"><?= $arResult['ACTIVE_STAMPS_COUNT'] . ' ' . $marksDeclension->get($arResult['ACTIVE_STAMPS_COUNT']) ?></div>
            <div class="descr-ticket-mark">
                Период начисления марок <nobr>1.09.19</nobr>&nbsp;&mdash; <nobr>30.09.19</nobr>
            </div>
        </div>
        <div class="b-kopilka__info">
	        <h3>Вместе за парту!</h3>
	        <p>Наступает осенняя пора, дети идут в школу, начинаются учебные будни. Вы можете вместе с питомцем тоже начать учиться</p>
	        <h3>Условия акции</h3>
	        <p>1. Делай покупки, получай марки: 1 марка  = <?= StampService::MARK_RATE ?> руб.;</p>
	        <p>2. Отслеживай марки где удобно:<br>
		        на чеке, в личном кабинете на сайте и в приложении;</p>
	        <p>3. Выбери игру и добавь в корзину, нажми "списать марки";</p>
	        <p>4. Получи игру со скидкой и развивай питомца!</p>
	        <a href="/iqgames/">Подробные условия акции</a>
        </div>
    </div>

    <hr class="b-hr" />

    <?php
        $APPLICATION->IncludeComponent('fourpaws:catalog.snippet.list', 'stamps', array(
            'OFFER_FILTER' => [
                '=XML_ID' => $arParams['PRODUCTS_XML_ID'],
            ],
            'COUNT' => 500,
            'TITLE' => 'Товары, участвующие в акции',
	        'CACHE_TIME' => 3600000,
	        'ONLY_PRODUCTS_XML_ID' => $arParams['PRODUCTS_XML_ID'], // показывать только указанные ТП, а не все в родительских товарах
        ), $this->getComponent());

        /*$APPLICATION->IncludeComponent('fourpaws:catalog.snippet.list', '', array(
            'OFFER_FILTER' => [
                '=XML_ID' => [
                    1001696,
                    1010181,
                    1009840,
                    1027914
                ]
            ],
            'COUNT' => 4,
            'TITLE' => '',
        ), $this->getComponent());*/

    /*<div class="b-button__wrap b-button__wrap--action-kopilka">
        <a href="/shares/kopimarki-mart2019/" class="b-button b-button--action-kopilka">Показать все</a>
    </div>*/
    ?>
	<? /* ?>
    <h2 class="b-title b-title--h2-kopilka">Категории товаров, участвующие в акции</h2>

    <h3 class="b-title b-title--block b-title--h3-kopilka">Для кошек</h3>
    <div class="b-common-wrapper b-common-wrapper--kopilka">
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="44468">
            <span class="b-common-item__sticker-wrap" style="background-color:transparent;data-background:transparent;"><img class="b-common-item__sticker" src="/upload/iblock/aef/aefb91e040cfe5ef9001d0274a5f81bd.png" alt="" role="presentation"></span>    <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Wellkiss_suhoy_korm_dlya_koshek_Senior_s_yagnenkom.html?offer=44339" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1021703','name':'Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 8 \u043a\u0433','brand':'Wellkiss','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2651.15,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/464/46408c903e68f18f9922624b3d95ccd7.jpg" alt="Adult корм для взрослых кошек, с курицей" title="Adult корм для взрослых кошек, с курицей">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Wellkiss_suhoy_korm_dlya_koshek_Senior_s_yagnenkom.html?offer=44339" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1021703','name':'Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 8 \u043a\u0433','brand':'Wellkiss','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2651.15,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Wellkiss</span>
                Adult корм для взрослых кошек, с курицей                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card">
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">
                На основе
                <span class="b-common-item__rank-num">3</span>
                отзывов            </span>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                    <span class="b-common-item__rank-text b-common-item__rank-text--red">Скидки до -30% на товары для кошек</span>
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link  b-weight-container__link--mobile  js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">8 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="249" data-offerid="42009" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1018447','name':'Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 400 \u0433','brand':'Wellkiss','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':249,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1018447);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/c19/c19ef8dab840ff37f3044d0535b4cb71.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Wellkiss_suhoy_korm_dlya_koshek_Senior_s_yagnenkom.html?offer=42009">400 г</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="779" data-offerid="44469" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1018446','name':'Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 1,5 \u043a\u0433','brand':'Wellkiss','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':779,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1018446);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/8ab/8ab967ad88259150e72b4763b0a7a35a.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Wellkiss_suhoy_korm_dlya_koshek_Senior_s_yagnenkom.html?offer=44469">1.5 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="1389" data-offerid="44340" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1021701','name':'Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 3 \u043a\u0433','brand':'Wellkiss','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1389,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1021701);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/4e4/4e40c28a8060d1fcbc5bd805da9ee8fa.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Wellkiss_suhoy_korm_dlya_koshek_Senior_s_yagnenkom.html?offer=44340">3 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link ajaxSend" data-oldprice="3119" data-discount="468" data-price="2651" data-offerid="44339" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1021703','name':'Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 8 \u043a\u0433','brand':'Wellkiss','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2651.15,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1021703);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/464/46408c903e68f18f9922624b3d95ccd7.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Wellkiss_suhoy_korm_dlya_koshek_Senior_s_yagnenkom.html?offer=44339">8 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Бельгия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1021703);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="44339">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">2651</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">
                        <span class="b-ruble b-ruble--prev-price"></span>
                        </span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc"></span>
                        <span class="b-common-item__discount-price js-sale-sale"></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount"></span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="71670">
            <span class="b-common-item__sticker-wrap" style="background-color:#feda24;data-background:#feda24;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-fire.svg" alt="" role="presentation"></span>    <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/grandin-dlya-sterilizovannyh-koshek-s-kuricey-pak-400g.html?offer=71685" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1024856','name':'Sterilized \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0432 \u0432\u043e\u0437\u0440\u0430\u0441\u0442\u0435 \u043e\u0442 1 \u0433\u043e\u0434\u0430 \u0434\u043e 7 \u043b\u0435\u0442, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 10 \u043a\u0433','brand':'Grandin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5599,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/b83/b83317c6a707363c0a82debfa4caa37e.jpg" alt="Sterilized корм для стерилизованных кошек в возрасте от 1 года до 7 лет, с курицей" title="Sterilized корм для стерилизованных кошек в возрасте от 1 года до 7 лет, с курицей">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/grandin-dlya-sterilizovannyh-koshek-s-kuricey-pak-400g.html?offer=71685" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1024856','name':'Sterilized \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0432 \u0432\u043e\u0437\u0440\u0430\u0441\u0442\u0435 \u043e\u0442 1 \u0433\u043e\u0434\u0430 \u0434\u043e 7 \u043b\u0435\u0442, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 10 \u043a\u0433','brand':'Grandin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5599,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Grandin</span>
                Sterilized корм для стерилизованных кошек в возрасте от 1 года до 7 лет, с курицей                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card">
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">
                На основе
                <span class="b-common-item__rank-num">3</span>
                отзывов            </span>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link  b-weight-container__link--mobile  js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">10 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="379" data-offerid="71671" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1024849','name':'Sterilized \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0432 \u0432\u043e\u0437\u0440\u0430\u0441\u0442\u0435 \u043e\u0442 1 \u0433\u043e\u0434\u0430 \u0434\u043e 7 \u043b\u0435\u0442, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 400 \u0433','brand':'Grandin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':379,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1024849);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/bf5/bf59a22b2663d711f7d30cd060b0292b.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/grandin-dlya-sterilizovannyh-koshek-s-kuricey-pak-400g.html?offer=71671">400 г</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="1199" data-offerid="71673" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1024850','name':'Sterilized \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0432 \u0432\u043e\u0437\u0440\u0430\u0441\u0442\u0435 \u043e\u0442 1 \u0433\u043e\u0434\u0430 \u0434\u043e 7 \u043b\u0435\u0442, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 1,5 \u043a\u0433','brand':'Grandin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1199,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1024850);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/890/89069e034df6503d86bc7b9cea1ca457.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/grandin-dlya-sterilizovannyh-koshek-s-kuricey-pak-400g.html?offer=71673">1.5 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link ajaxSend" data-oldprice="" data-discount="" data-price="5599" data-offerid="71685" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1024856','name':'Sterilized \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0432 \u0432\u043e\u0437\u0440\u0430\u0441\u0442\u0435 \u043e\u0442 1 \u0433\u043e\u0434\u0430 \u0434\u043e 7 \u043b\u0435\u0442, \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439, 10 \u043a\u0433','brand':'Grandin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5599,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1024856);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/b83/b83317c6a707363c0a82debfa4caa37e.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/grandin-dlya-sterilizovannyh-koshek-s-kuricey-pak-400g.html?offer=71685">10 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Чехия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1024856);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="71685">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">5599</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">
                        <span class="b-ruble b-ruble--prev-price"></span>
                        </span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc"></span>
                        <span class="b-common-item__discount-price js-sale-sale"></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount"></span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="42921">
            <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=43476" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1005495','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 10 \u043a\u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5875,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/0c0/0c0b4fdf5e9b5f4dab75b45ae8150aa4.jpg" alt="Sterilised корм для стерилизованных кошек, с индейкой" title="Sterilised корм для стерилизованных кошек, с индейкой">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=43476" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1005495','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 10 \u043a\u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5875,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Pro Plan</span>
                Sterilised корм для стерилизованных кошек, с индейкой                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card">
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">
                На основе
                <span class="b-common-item__rank-num">2</span>
                отзывов            </span>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link  b-weight-container__link--mobile  js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">10 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="339" data-offerid="45693" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1000008','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 400 \u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':339,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1000008);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/d81/d8159f52d88270330a87f13e453ca411.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=45693">400 г</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="1159" data-offerid="42922" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1000001','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 1,5 \u043a\u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1159,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1000001);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/55d/55dcafcfc81deed8663d24f7b4ec3bb5.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=42922">1.5 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="1099" data-offerid="31113" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1018213','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 1,9 \u043a\u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1099,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1018213);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/fd4/fd4647e4af2572cbbc517fbd209db822.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=31113">1.9 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="2175" data-discount="326" data-price="1849" data-offerid="45702" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1000004','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 3 \u043a\u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1848.75,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1000004);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/c8f/c8f3fae515d0bd6f4638fbcc82220f36.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=45702">3 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link ajaxSend" data-oldprice="" data-discount="" data-price="5875" data-offerid="43476" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1005495','name':'Sterilised \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a, \u0441 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 10 \u043a\u0433','brand':'Pro Plan','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5875,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1005495);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/0c0/0c0b4fdf5e9b5f4dab75b45ae8150aa4.jpg" data-link="/catalog/koshki/korm-koshki/sukhoy/Pro_Plan_After_Care_suhoy_korm_dlya_kastrirovannyhsterilizovannyh_koshek_indeykakuritsa.html?offer=43476">10 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Россия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1005495);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="43476">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">5875</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">
                        <span class="b-ruble b-ruble--prev-price"></span>
                        </span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc"></span>
                        <span class="b-common-item__discount-price js-sale-sale"></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount"></span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="41954">
            <span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"></span>    <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=43487" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1002563','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 10 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5248.75,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/59d/59d5187c7bcd0b1d00cac4d75e5c0ba6.png" alt="Sterilised 37 корм для стерилизованных кошек с 1 до 7 лет" title="Sterilised 37 корм для стерилизованных кошек с 1 до 7 лет">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=43487" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1002563','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 10 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5248.75,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Royal Canin</span>
                Sterilised 37 корм для стерилизованных кошек с 1 до 7 лет                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card">
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                    <div class="b-rating__star-block b-rating__star-block--active">
                        <span class="b-icon">
                            <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-star"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <span class="b-common-item__rank-text b-common-item__rank-text--card b-common-item__rank-text--review">
                На основе
                <span class="b-common-item__rank-num">2</span>
                отзывов            </span>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link  b-weight-container__link--mobile  js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">10 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="315" data-offerid="41911" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1000724','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 400 \u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':315,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1000724);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/1a4/1a469ee52514352ded611cf8ea7d504a.png" data-link="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=41911">400 г</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="315" data-offerid="41955" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1021678','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 400 + 160 \u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':315,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1021678);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/680/680e83f5391a34ff99a4e8367fbda8d3.png" data-link="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=41955">560 г</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="" data-discount="" data-price="1339" data-offerid="45708" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1000715','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 2 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1339,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1000715);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/ce2/ce221b428818e2fb1420cdc72a9fa0a0.png" data-link="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=45708">2 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price ajaxSend" data-oldprice="2569" data-discount="385" data-price="2184" data-offerid="43617" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1003418','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 4 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2183.65,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1003418);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/91c/91c0d344dd80dbd987ff2567d3cfd3ba.png" data-link="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=43617">4 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link ajaxSend" data-oldprice="6175" data-discount="926" data-price="5249" data-offerid="43487" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1002563','name':'Sterilised 37 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0441\u0442\u0435\u0440\u0438\u043b\u0438\u0437\u043e\u0432\u0430\u043d\u043d\u044b\u0445 \u043a\u043e\u0448\u0435\u043a \u0441 1 \u0434\u043e 7 \u043b\u0435\u0442, 10 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':5248.75,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1002563);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/59d/59d5187c7bcd0b1d00cac4d75e5c0ba6.png" data-link="/catalog/koshki/korm-koshki/sukhoy/Royal_Canin_Sterilised_37_suhoy_korm_dlya_sterilizovannyh_koshek.html?offer=43487">10 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Россия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1002563);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="43487">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">5249</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">
                        <span class="b-ruble b-ruble--prev-price"></span>
                        </span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc"></span>
                        <span class="b-common-item__discount-price js-sale-sale"></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount"></span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <hr class="b-hr b-hr--kopilka"/>

    <h3 class="b-title b-title--block b-title--h3-kopilka">Для собак</h3>
    <div class="b-common-wrapper b-common-wrapper--kopilka">
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="81126">
            <span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"></span>    <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/monge-dog-speciality-korm-dlya-sobak-vseh-porod-yagnenok-s-risom-i-kartofe.html?offer=81127" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1027884','name':'All Breeds Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u0441\u043e\u0431\u0430\u043a \u0432\u0441\u0435\u0445 \u043f\u043e\u0440\u043e\u0434, \u0441 \u044f\u0433\u043d\u0435\u043d\u043a\u043e\u043c, \u0440\u0438\u0441\u043e\u043c \u0438 \u043a\u0430\u0440\u0442\u043e\u0444\u0435\u043b\u0435\u043c, 2,5 \u043a\u0433','brand':'Monge','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1119.2,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/5b6/5b6d527e5c5c69c743c2067e11c88e25.jpg" alt="All Breeds Adult корм для взрослых собак всех пород, с ягненком, рисом и картофелем" title="All Breeds Adult корм для взрослых собак всех пород, с ягненком, рисом и картофелем">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/monge-dog-speciality-korm-dlya-sobak-vseh-porod-yagnenok-s-risom-i-kartofe.html?offer=81127" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1027884','name':'All Breeds Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u0441\u043e\u0431\u0430\u043a \u0432\u0441\u0435\u0445 \u043f\u043e\u0440\u043e\u0434, \u0441 \u044f\u0433\u043d\u0435\u043d\u043a\u043e\u043c, \u0440\u0438\u0441\u043e\u043c \u0438 \u043a\u0430\u0440\u0442\u043e\u0444\u0435\u043b\u0435\u043c, 2,5 \u043a\u0433','brand':'Monge','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1119.2,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Monge</span>
                All Breeds Adult корм для взрослых собак всех пород, с ягненком, рисом и картофелем                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card"></div>
                <a class="b-common-item__rank-text" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/monge-dog-speciality-korm-dlya-sobak-vseh-porod-yagnenok-s-risom-i-kartofe.html?offer=81127&amp;new-review=y" title="Оставьте отзыв">Оставьте отзыв</a>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">2.5 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link" data-oldprice="1399" data-discount="280" data-price="1119" data-offerid="81127" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1027884','name':'All Breeds Adult \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0432\u0437\u0440\u043e\u0441\u043b\u044b\u0445 \u0441\u043e\u0431\u0430\u043a \u0432\u0441\u0435\u0445 \u043f\u043e\u0440\u043e\u0434, \u0441 \u044f\u0433\u043d\u0435\u043d\u043a\u043e\u043c, \u0440\u0438\u0441\u043e\u043c \u0438 \u043a\u0430\u0440\u0442\u043e\u0444\u0435\u043b\u0435\u043c, 2,5 \u043a\u0433','brand':'Monge','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':1119.2,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1027884);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/5b6/5b6d527e5c5c69c743c2067e11c88e25.jpg" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/monge-dog-speciality-korm-dlya-sobak-vseh-porod-yagnenok-s-risom-i-kartofe.html?offer=81127" data-available="" data-pickup="" data-curravailable="true">2.5 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__packing">
                        Упаковка <strong>4шт.</strong>
                    </div>
                    <div class="b-common-item__country">
                        Страна производства <strong>Италия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1027884);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="81127">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">1119</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">1399 ₽</span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc">Скидка</span>
                        <span class="b-common-item__discount-price js-sale-sale">280</span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="80055">
            <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/large-breed-puppy-korm-suhoy-dlya-shchenkov-krupnyh-porod-kurica-indeyka.html?offer=42192" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1017257','name':'Puppy Large Breed \u0441\u0443\u0445\u043e\u0439 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u043a\u0440\u0443\u043f\u043d\u044b\u0445 \u0438 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439 \u0438 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 17 \u043a\u0433','brand':'Acana','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':6999,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/7a6/7a6529446471714411bde7265b44066c.png" alt="Puppy Large Breed сухой корм для щенков крупных и гигантских пород с курицей и индейкой" title="Puppy Large Breed сухой корм для щенков крупных и гигантских пород с курицей и индейкой">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/large-breed-puppy-korm-suhoy-dlya-shchenkov-krupnyh-porod-kurica-indeyka.html?offer=42192" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1017257','name':'Puppy Large Breed \u0441\u0443\u0445\u043e\u0439 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u043a\u0440\u0443\u043f\u043d\u044b\u0445 \u0438 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439 \u0438 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 17 \u043a\u0433','brand':'Acana','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':6999,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Acana</span>
                Puppy Large Breed сухой корм для щенков крупных и гигантских пород с курицей и индейкой                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card"></div>
                <a class="b-common-item__rank-text" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/large-breed-puppy-korm-suhoy-dlya-shchenkov-krupnyh-porod-kurica-indeyka.html?offer=42192&amp;new-review=y" title="Оставьте отзыв">Оставьте отзыв</a>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">17 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price" data-oldprice="5499" data-discount="825" data-price="4674" data-offerid="42198" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1017272','name':'Puppy Large Breed \u0441\u0443\u0445\u043e\u0439 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u043a\u0440\u0443\u043f\u043d\u044b\u0445 \u0438 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439 \u0438 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 11,4 \u043a\u0433','brand':'Acana','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':4674.15,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1017272);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/e26/e26880c49e1e5abe8777b1a465f8bafa.png" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/large-breed-puppy-korm-suhoy-dlya-shchenkov-krupnyh-porod-kurica-indeyka.html?offer=42198" data-available="" data-pickup="" data-curravailable="true">11.4 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link" data-oldprice="" data-discount="" data-price="6999" data-offerid="42192" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1017257','name':'Puppy Large Breed \u0441\u0443\u0445\u043e\u0439 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u043a\u0440\u0443\u043f\u043d\u044b\u0445 \u0438 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 \u043a\u0443\u0440\u0438\u0446\u0435\u0439 \u0438 \u0438\u043d\u0434\u0435\u0439\u043a\u043e\u0439, 17 \u043a\u0433','brand':'Acana','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':6999,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1017257);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/7a6/7a6529446471714411bde7265b44066c.png" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/large-breed-puppy-korm-suhoy-dlya-shchenkov-krupnyh-porod-kurica-indeyka.html?offer=42192" data-available="" data-pickup="" data-curravailable="true">17 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Канада</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1017257);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="42192">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">6999</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin"></span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc"></span>
                        <span class="b-common-item__discount-price js-sale-sale"></span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount"></span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="79900">
            <span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"></span>    <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/giant-puppy-korm-korm-suhoy-dlya-shchenkov-ochen-krupnyh-porod1.html?offer=42387" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1001810','name':'Giant Puppy 34 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 2 \u0434\u043e 8 \u043c\u0435\u0441\u044f\u0446\u0435\u0432, 15 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':4546.65,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/57e/57e6989f97b21e97228f7bbb9c1fe8a9.jpg" alt="Giant Puppy 34 корм для щенков гигантских пород с 2 до 8 месяцев" title="Giant Puppy 34 корм для щенков гигантских пород с 2 до 8 месяцев">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/giant-puppy-korm-korm-suhoy-dlya-shchenkov-ochen-krupnyh-porod1.html?offer=42387" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1001810','name':'Giant Puppy 34 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 2 \u0434\u043e 8 \u043c\u0435\u0441\u044f\u0446\u0435\u0432, 15 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':4546.65,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Royal Canin</span>
                Giant Puppy 34 корм для щенков гигантских пород с 2 до 8 месяцев                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card"></div>
                <a class="b-common-item__rank-text" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/giant-puppy-korm-korm-suhoy-dlya-shchenkov-ochen-krupnyh-porod1.html?offer=42387&amp;new-review=y" title="Оставьте отзыв">Оставьте отзыв</a>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">15 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link" data-oldprice="5349" data-discount="802" data-price="4547" data-offerid="42387" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1001810','name':'Giant Puppy 34 \u043a\u043e\u0440\u043c \u0434\u043b\u044f \u0449\u0435\u043d\u043a\u043e\u0432 \u0433\u0438\u0433\u0430\u043d\u0442\u0441\u043a\u0438\u0445 \u043f\u043e\u0440\u043e\u0434 \u0441 2 \u0434\u043e 8 \u043c\u0435\u0441\u044f\u0446\u0435\u0432, 15 \u043a\u0433','brand':'Royal Canin','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':4546.65,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1001810);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/57e/57e6989f97b21e97228f7bbb9c1fe8a9.jpg" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/giant-puppy-korm-korm-suhoy-dlya-shchenkov-ochen-krupnyh-porod1.html?offer=42387" data-available="" data-pickup="" data-curravailable="true">15 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Россия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1001810);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="42387">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">4547</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">5349 ₽</span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc">Скидка</span>
                        <span class="b-common-item__discount-price js-sale-sale">802</span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="b-common-item  b-common-item--catalog-item js-product-item" data-productid="77094">
            <span class="b-common-item__sticker-wrap" style="background-color:#da291c;data-background:#da291c;"><img class="b-common-item__sticker" src="/static/build/images/inhtml/s-proc.svg" alt="" role="presentation"></span>    <span class="b-common-item__image-wrap">
            <a class="b-common-item__image-link js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/dog-chau-edalt-14kg-dlya-sobak-kurica.html?offer=77095" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1026762','name':'\u042d\u0434\u0430\u043b\u0442 14\u043a\u0433 \u0434\u043b\u044f \u0441\u043e\u0431\u0430\u043a \u041a\u0443\u0440\u0438\u0446\u0430','brand':'Dog Chow','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2359.2000000000003,'quantity':0,'position':1}]}},'event':'productClick'});">
            <img class="b-common-item__image js-weight-img" src="/resize/240x240/upload/iblock/4a3/4a34ce5be866691e1d4c6311fb16f00c.png" alt="Adult корм для собак старше 1 года, с курицей" title="Adult корм для собак старше 1 года, с курицей">
            </a>
            </span>
            <div class="b-common-item__info-center-block">
                <a class="b-common-item__description-wrap js-item-link" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/dog-chau-edalt-14kg-dlya-sobak-kurica.html?offer=77095" onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1026762','name':'\u042d\u0434\u0430\u043b\u0442 14\u043a\u0433 \u0434\u043b\u044f \u0441\u043e\u0431\u0430\u043a \u041a\u0443\u0440\u0438\u0446\u0430','brand':'Dog Chow','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2359.2000000000003,'quantity':0,'position':1}]}},'event':'productClick'});" title="">
                <span class="b-clipped-text b-clipped-text--three">
                <span>
                <span class="span-strong">Dog Chow</span>
                Adult корм для собак старше 1 года, с курицей                </span>
                </span>
                </a>
                <div class="b-rating b-rating--card"></div>
                <a class="b-common-item__rank-text" href="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/dog-chau-edalt-14kg-dlya-sobak-kurica.html?offer=77095&amp;new-review=y" title="Оставьте отзыв">Оставьте отзыв</a>
                <div class="b-common-item__rank-wrapper">
                    &nbsp;
                </div>
                <div class="b-common-item__variant">Варианты фасовки</div>
                <div class="b-weight-container b-weight-container--list">
                    <a class="b-weight-container__link b-weight-container__link--mobile js-mobile-select js-select-mobile-package" href="javascript:void(0);" title="">14 кг</a>
                    <div class="b-weight-container__dropdown-list__wrapper">
                        <div class="b-weight-container__dropdown-list"></div>
                    </div>
                    <ul class="b-weight-container__list">
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price" data-oldprice="" data-discount="" data-price="679" data-offerid="77107" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1026768','name':'\u0434\u043b\u044f \u0441\u043e\u0431\u0430\u043a 2,5\u043a\u0433 \u042d\u0434\u0430\u043b\u0442 \u0426\u044b\u043f\u043b\u0435\u043d\u043e\u043a','brand':'Dog Chow','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':679,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1026768);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/3b2/3b2b83670d4164d34da2aac974e8af11.png" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/dog-chau-edalt-14kg-dlya-sobak-kurica.html?offer=77107" data-available="" data-pickup="" data-curravailable="true">2.5 кг</a>
                        </li>
                        <li class="b-weight-container__item">
                            <a href="javascript:void(0)" class="b-weight-container__link js-price active-link" data-oldprice="2949" data-discount="590" data-price="2359" data-offerid="77095" data-onclick="dataLayer.push({'ecommerce':{'currencyCode':'RUB','click':{'actionField':{'list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443'},'products':[{'id':'1026762','name':'\u042d\u0434\u0430\u043b\u0442 14\u043a\u0433 \u0434\u043b\u044f \u0441\u043e\u0431\u0430\u043a \u041a\u0443\u0440\u0438\u0446\u0430','brand':'Dog Chow','category':'','list':'\u041a\u0430\u0442\u0430\u043b\u043e\u0433 \u043f\u043e \u043f\u0438\u0442\u043e\u043c\u0446\u0443','price':2359.2000000000003,'quantity':0,'position':1}]}},'event':'productClick'});" data-onmousedown="try {
                                rrApi.addToBasket(1026762);
                                } catch (e) {
                                }" data-image="/resize/240x240/upload/iblock/4a3/4a34ce5be866691e1d4c6311fb16f00c.png" data-link="/catalog/sobaki/korm-sobaki/sukhoy-korm-sobaki/dog-chau-edalt-14kg-dlya-sobak-kurica.html?offer=77095" data-available="" data-pickup="" data-curravailable="true">14 кг</a>
                        </li>
                    </ul>
                </div>
                <div class="b-common-item__moreinfo">
                    <div class="b-common-item__country">
                        Страна производства <strong>Россия</strong>
                    </div>
                </div>
                <a class="b-common-item__add-to-cart js-basket-add" href="javascript:void(0);" onmousedown="try {
                    rrApi.addToBasket(1026762);
                    } catch (e) {
                    }" title="" data-url="/ajax/sale/basket/add/" data-offerid="77095">
                    <span class="b-common-item__wrapper-link">
                        <span class="b-cart">
                            <span class="b-icon b-icon--cart">
                                <svg class="b-icon__svg" viewBox="0 0 12 12" width="12px" height="12px">
                                    <use class="b-icon__use" xlink:href="https://4lapy.local.articul.ru/static/build/assets/icons.6379a3d8a83a20b3d1ff4ea6a4b2f0fd.svg#icon-cart"></use>
                                </svg>
                            </span>
                        </span>
                        <span class="b-common-item__price js-price-block">2359</span>
                        <span class="b-common-item__currency">
                        <span class="b-ruble">₽</span>
                        </span>
                    </span>
                    <span class="b-common-item__incart">+1</span>
                </a>
                <div class="b-common-item__additional-information">
                    <div class="b-common-item__benefin js-sale-block">
                        <span class="b-common-item__prev-price js-sale-origin">2949 ₽</span>
                        <span class="b-common-item__discount">
                        <span class="b-common-item__disc">Скидка</span>
                        <span class="b-common-item__discount-price js-sale-sale">590</span>
                        <span class="b-common-item__currency"> <span class="b-ruble b-ruble--discount">₽</span>
                        </span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <? */ ?>
</div>
