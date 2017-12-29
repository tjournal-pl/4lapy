<?php declare(strict_types = 1);

/**
 * @var \CBitrixComponentTemplate $this
 *
 * @var array                     $arResult
 */

use FourPaws\BitrixOrm\Model\CropImageDecorator;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\StoreBundle\Entity\Store;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!is_array($arResult['STORES']) || empty($arResult['STORES'])) {
    return;
}

$frame = $this->createFrame(); ?>
<div class="b-stores__block">
    <h2 class="b-title b-title--stores">Ваш город</h2>
    <a class="b-link b-link--select"
       href="javascript:void(0);"
       title="<?= $arResult['CITY'] ?>"><?= $arResult['CITY'] ?></a>
    <?php /** @todo выпадающий список городов */ ?>
    <div class="b-stores-sort">
        <?php if (\is_array($arResult['SERVICES']) && !empty($arResult['SERVICES'])) {
    ?>
            <div class="b-stores-sort__checkbox-block">
                <?php foreach ($arResult['SERVICES'] as $key => $service) {
        ?>
                    <div class="b-checkbox b-checkbox--stores">
                        <input class="b-checkbox__input"
                               type="checkbox"
                               name="stores-sort"
                               id="stores-sort-<?= $key ?>"
                               data-url="/ajax/store/list/checkboxFilter/"
                               value="<?= $service['ID'] ?>" />
                        <label class="b-checkbox__name b-checkbox__name--stores"
                               for="stores-sort-<?= $key ?>">
                            <span class="b-checkbox__text"><?= $service['UF_NAME'] ?></span>
                        </label>
                    </div>
                <?php
    } ?>
            </div>
        <?php
} ?>
        <div class="b-form-inline b-form-inline--stores-search">
            <form class="b-form-inline__form" data-url="/ajax/store/list/search/">
                <input class="b-input b-input--stores-search"
                       type="text"
                       id="stores-search"
                       name=""
                       placeholder="Поиск по адресу, метро и названию ТЦ" />
            </form>
        </div>
    </div>
</div>
<div class="b-stores__block">
    <div class="b-availability">
        <div class="b-catalog-filter b-catalog-filter--stores js-availability-parent">
            <div class="b-catalog-filter__sort-part b-catalog-filter__sort-part--stores">
                <span class="b-catalog-filter__label b-catalog-filter__label--amount b-catalog-filter__label--stores"><?= count(
                        $arResult['STORES']
                    ) ?> магазина</span>
                <span class="b-catalog-filter__sort">
                    <span class="b-catalog-filter__label b-catalog-filter__label--sort b-catalog-filter__label--stores">Сортировать</span>
                    <span class="b-select b-select--stores">
                        <select class="b-select__block b-select__block--stores"
                                name="sort"
                                data-url="/ajax/store/list/order/">
                            <option value="city">по городу</option>
                            <option value="address">по адресу</option>
                            <option value="metro"<?= (!isset($arResult['METRO'])
                                                      || empty($arResult['METRO'])) ? ' style="display:none"' : '' ?>>по метро</option>
                        </select>
                        <span class="b-select__arrow"></span>
                    </span>
                </span>
            </div>
            <ul class="b-availability-tab-list b-availability-tab-list--stores js-availability-list">
                <li class="b-availability-tab-list__item active">
                    <a class="b-availability-tab-list__link js-product-list"
                       href="javascript:void(0)"
                       aria-controls="shipping-list"
                       title="Списком">Списком</a>
                </li>
                <li class="b-availability-tab-list__item">
                    <a class="b-availability-tab-list__link js-product-map"
                       href="javascript:void(0)"
                       aria-controls="on-map"
                       title="На карте">На карте</a>
                </li>
            </ul>
        </div>
        <div class="b-availability__content js-availability-content">
            <div class="b-tab-delivery b-tab-delivery--stores js-content-list js-map-list-scroll">
                <ul class="b-delivery-list js-delivery-list">
                    <?php /** @var Store $store */
                    foreach ($arResult['STORES'] as $store) {
                        ?>
                        <li class="b-delivery-list__item">
                            <a class="b-delivery-list__link b-delivery-list__link--stores js-accordion-stores-list"
                               href="javascript:void(0);"
                               title="">
                            <span class="b-delivery-list__col b-delivery-list__col--stores b-delivery-list__col--addr">
                                <?php $metro = $store->getMetro();
                        if (!empty($metro)) {
                            ?>
                                    <span class="b-delivery-list__col b-delivery-list__col--color<?= !empty($arResult['METRO'][$metro]['BRANCH']['CLASS']) ? 'b-delivery-list__col--'
                                                                                                                                                             . $arResult['METRO'][$metro]['BRANCH']['CLASS'] : '' ?>"></span>
                                    <?= $arResult['METRO'][$metro]['UF_NAME'] . ', ';
                        } ?>
                                <?= $store->getAddress() ?>
                            </span>
                                <span class="b-delivery-list__col b-delivery-list__col--stores b-delivery-list__col--phone"><?= $store->getPhone(
                                    ) ?></span>
                                <span class="b-delivery-list__col b-delivery-list__col--stores b-delivery-list__col--time"><?= $store->getSchedule(
                                    ) ?></span>
                                <div class="b-tag">
                                    <?php $arServices = $store->getServices();
                        if (\is_array($arServices) && !empty($arServices)) {
                            $count = count($arServices);
                            foreach ($arServices as $key => $service) {
                                ?>
                                            <span class="b-tag__item"><?= $arResult['SERVICES'][$service]['UF_NAME'] ?></span><?= $key
                                                                                                                                  !== $count
                                                                                                                                      - 1 ? ',' : '' ?>
                                            <?php
                            }
                        } ?>
                                </div>
                            </a>
                            <div class="b-delivery-list__information">
                                <?php $image = $store->getImageId();
                        if (!empty($image) && is_numeric($image) && $image > 0) {
                            ?>
                                    <div class="b-delivery-list__image-wrapper">
                                        <img src="<?= /** @noinspection PhpUnhandledExceptionInspection */
                                        CropImageDecorator::createFromPrimary($image)->setCropWidth(630)->setCropHeight(
                                            360
                                        ); ?>"
                                             class="b-delivery-list__image"
                                             alt=""
                                             title="">
                                    </div>
                                    <?php
                        } ?>
                                <div class="b-delivery-list__text">
                                    <p class="b-delivery-list__information-header">Как нас найти</p>
                                    <p class="b-delivery-list__information-text"><?= $store->getDescription() ?> </p>
                                    <a class="b-delivery-list__information-link"
                                       id="shop_id1"
                                       data-shop-id="1"
                                       href="javascript:void(0);"
                                       title="">Показать на карте</a>
                                    <a class="b-delivery-list__information-link"
                                       href="javascript:void(0);"
                                       title="">Проложить маршрут</a>
                                </div>
                            </div>
                        </li>
                        <?php
                    } ?>
                </ul>
            </div>
            <div class="b-tab-delivery-map b-tab-delivery-map--stores js-content-map">
                <a class="b-link b-link b-link--popup-back b-link b-link--popup-choose-shop js-product-list js-map-shoose-shop"
                   href="javascript:void(0);">Выберите магазин</a>
                <?php /** @todo инициализация карты */ ?>
                <div class="b-tab-delivery-map__map" id="map"></div>
                <a class="b-link b-link--close-baloon js-product-list"
                   href="javascript:void(0);"
                   title="">
                    <span class="b-icon b-icon--close-baloon">
                        <?= new SvgDecorator('icon-close-baloon', 18, 18) ?>
                    </span>
                </a>
            </div>
        </div>
    </div>
</div>