<?php

use Bitrix\Main\Type\Date;
use Doctrine\Common\Collections\ArrayCollection;
use FourPaws\Decorators\SvgDecorator;
use FourPaws\PersonalBundle\Entity\Pet;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var \CMain $APPLICATION */
/** @var array $arResult */

/** @var ArrayCollection $items */
$items = $arResult['ITEMS'];
?>
<div class="b-tab-content__container -active js-tab-account active" data-tab-content="my-pet">
    <div class="b-account-adress">
        <?php /** @var Pet $pet */
        if (!$items->isEmpty()) {
            foreach ($items as $pet) {?>
                <div class="b-account-border-block b-account-border-block--pet js-parent-cont js-parent-cont--pet"
                     data-image="<?= $pet->getResizePopupImgPath() ?>"
                     data-name-pet="<?= $pet->getName() ?>"
                     data-type="<?= $pet->getType() ?>"
                     data-breed="<?= $pet->getBreedId() ?>"
                     data-data="<?php $birthday = $pet->getBirthday();
                     echo $birthday instanceof Date ? $birthday->format('d.m.Y') : '' ?>"
                     data-male="<?= $pet->getCodeGender() ? ($pet->getCodeGender()  === 'M' ? 1 : 0) : -1 ?>"
                     data-female="<?= $pet->getCodeGender() === 'F' ? 1 : 0 ?>"
                     data-size="<?= $pet->getSizeTitle() === 'нестандартный' ? 'UNKNOWN' : $pet->getSize() ?>"
                     data-chest="<?= $pet->getChest() ?>"
                     data-back="<?= $pet->getBack() ?>"
                     data-neck="<?= $pet->getNeck() ?>"
                     data-id="<?= $pet->getId() ?>">
                    <div class="b-account-border-block__content b-account-border-block__content--pet js-parent-cont">
                        <div class="b-account-border-block__image-wrap">
                            <img class="b-account-border-block__image js-image-wrapper"
                                 src="<?= $pet->getResizeImgPath() ?>"
                                 alt="<?= $pet->getName() ?>"
                                 title="" />
                        </div>
                        <div class="b-account-border-block__info">
                            <div class="b-account-border-block__title b-account-border-block__title--pet">
                                <?= $pet->getName() ?>
                            </div>
                            <p class="b-account-border-block__pet"><?= $pet->getStringType() ?></p>
                            <p class="b-account-border-block__pet"><?= $pet->getBreed() ?></p>
                            <p class="b-account-border-block__pet"><?= $pet->getStringGender() ?></p>
                            <p class="b-account-border-block__pet"><?= $pet->getAgeString() ?></p>
                            <? if($pet->getSize() > 0) { ?>
                                <p class="b-account-border-block__pet">Размер: <?= $pet->getSizeTitle() ?></p>
                            <? } ?>
                            ;
                        </div>
                    </div>
                    <div class="b-account-border-block__button">
                        <?php
                        if ($arResult['canEdit']) {
                            ?>
                            <div class="b-account-border-block__wrapper-link">
                                <a class="b-account-border-block__link js-open-popup js-edit-query"
                                   href="javascript:void(0);"
                                   data-url="/ajax/personal/pets/update/"
                                   title="Редактировать"
                                   data-popup-id="edit-popup-pet">
                                <span class="b-icon b-icon--account-block">
                                    <?= new SvgDecorator('icon-edit', 21, 21) ?>
                                </span>
                                    <span>Редактировать</span>
                                </a>
                            </div>
                            <div class="b-account-border-block__wrapper-link">
                                <a class="b-account-border-block__link js-del-popup-pet"
                                   href="javascript:void(0);"
                                   title="Удалить">
                                <span class="b-icon b-icon--account-block">
                                    <?= new SvgDecorator('icon-trash', 21, 21) ?>
                                </span>
                                    <span>Удалить</span>
                                </a>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="b-account-border-block__hidden js-hidden-del">
                        <?php
                        if ($arResult['canEdit']) {
                            ?>
                            <a class="b-account-border-block__link-delete js-close-hidden"
                               href="javascript:void(0);"
                               title="Удалить">
                            <span class="b-icon b-icon--account-delete">
                                <?= new SvgDecorator('icon-delete-account', 26, 26) ?>
                            </span>
                            </a>
                            <div class="b-account-border-block__title b-account-border-block__title--hidden">
                                Удалить из питомцев
                                <p><span><?= $pet->getName() ?></span>?</p>
                            </div>
                            <a class="b-link b-link--account-del b-link--account-del"
                               href="javascript:void(0)"
                               title="Удалить"
                               data-url="/ajax/personal/pets/delete/?id=<?= $pet->getId() ?>"
                               data-id="<?= $pet->getId() ?>"
                            >
                                <span class="b-link__text b-link__text--account-del">Удалить</span>
                            </a>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            <?php }
        }

        if ($arResult['canAdd']) {
            ?>
            <div class="b-account-border-block b-account-border-block--dashed b-account-border-block--dashed">
                <div class="b-account-border-block__content b-account-border-block__content--dashed">
                    <div class="b-account-border-block__title b-account-border-block__title--dashed">
                        Зачем добавлять питомца?
                    </div>
                    <ul class="b-account-border-block__list">
                        <li class="b-account-border-block__item">
                            Наиболее подходящие рекомендации в интернет-магазине;
                        </li>
                        <li class="b-account-border-block__item">
                            Полезные статьи по уходу вашего питомца.
                        </li>
                    </ul>
                </div>
                <div class="b-account-border-block__button">
                    <a class="b-link b-link--account-tab js-add-query js-open-popup js-open-popup--account-tab"
                       href="javascript:void(0)"
                       title="Добавить питомца"
                       data-popup-id="edit-popup-pet"
                       data-url="/ajax/personal/pets/add/">
                        <span class="b-link__text b-link__text--account-tab">Добавить питомца</span>
                    </a>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
</div>