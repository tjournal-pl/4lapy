<?php
/**
 * @var CBitrixComponentTemplate $this
 * @var CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 * @var Category $category
 * @var Category $childCategory
 * @var Category[] $childCategories
 * @var Category $childChildCategory
 */

use Adv\Bitrixtools\Tools\Iblock\IblockUtils;
use FourPaws\App\Templates\ViewsEnum;
use FourPaws\Catalog\Model\Category;
use FourPaws\Enum\BannerSectionCode;
use FourPaws\Enum\IblockCode;
use FourPaws\Enum\IblockType;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$category = $arResult['CATEGORY'];

$categoriesWithChildren = $category
    ->getChild()
    ->filter(function (Category $category) {
        return $category->getChild()->count();
    });

$filterName = 'catalogSliderFilter';
global ${$filterName};
${$filterName} = ['PROPERTY_SECTION' => $category->getId()];
$APPLICATION->IncludeComponent('bitrix:news.list',
    'index.slider',
    [
        'COMPONENT_TEMPLATE' => 'index.slider',
        'IBLOCK_TYPE' => IblockType::PUBLICATION,
        'IBLOCK_ID' => IblockUtils::getIblockId(IblockType::PUBLICATION, IblockCode::BANNERS),
        'NEWS_COUNT' => '20',
        'SORT_BY1' => 'SORT',
        'SORT_ORDER1' => 'ASC',
        'SORT_BY2' => 'ACTIVE_FROM',
        'SORT_ORDER2' => 'DESC',
        'FILTER_NAME' => $filterName,
        'FIELD_CODE' => [
            0 => 'NAME',
            1 => 'PREVIEW_PICTURE',
            2 => 'DETAIL_PICTURE',
            3 => '',
        ],
        'PROPERTY_CODE' => [
            0 => 'LINK',
            1 => 'IMG_TABLET',
            2 => 'BACKGROUND',
        ],
        'CHECK_DATES' => 'Y',
        'DETAIL_URL' => '',
        'AJAX_MODE' => 'N',
        'AJAX_OPTION_JUMP' => 'N',
        'AJAX_OPTION_STYLE' => 'N',
        'AJAX_OPTION_HISTORY' => 'N',
        'AJAX_OPTION_ADDITIONAL' => '',
        'CACHE_TYPE' => 'A',
        'CACHE_TIME' => '36000000',
        'CACHE_FILTER' => 'Y',
        'CACHE_GROUPS' => 'N',
        'PREVIEW_TRUNCATE_LEN' => '',
        'ACTIVE_DATE_FORMAT' => '',
        'SET_TITLE' => 'N',
        'SET_BROWSER_TITLE' => 'N',
        'SET_META_KEYWORDS' => 'N',
        'SET_META_DESCRIPTION' => 'N',
        'SET_LAST_MODIFIED' => 'N',
        'INCLUDE_IBLOCK_INTO_CHAIN' => 'N',
        'ADD_SECTIONS_CHAIN' => 'N',
        'HIDE_LINK_WHEN_NO_DETAIL' => 'N',
        'PARENT_SECTION' => '',
        'PARENT_SECTION_CODE' => '',
        'INCLUDE_SUBSECTIONS' => 'N',
        'STRICT_SECTION_CHECK' => 'N',
        'DISPLAY_DATE' => 'N',
        'DISPLAY_NAME' => 'N',
        'DISPLAY_PICTURE' => 'N',
        'DISPLAY_PREVIEW_TEXT' => 'N',
        'PAGER_TEMPLATE' => '',
        'DISPLAY_TOP_PAGER' => 'N',
        'DISPLAY_BOTTOM_PAGER' => 'N',
        'PAGER_TITLE' => '',
        'PAGER_SHOW_ALWAYS' => 'N',
        'PAGER_DESC_NUMBERING' => 'N',
        'PAGER_DESC_NUMBERING_CACHE_TIME' => '',
        'PAGER_SHOW_ALL' => 'N',
        'PAGER_BASE_LINK_ENABLE' => 'N',
        'SET_STATUS_404' => 'N',
        'SHOW_404' => 'N',
        'MESSAGE_404' => '',
    ],
    false,
    ['HIDE_ICONS' => 'Y']);

?>

<?php $this->setViewTarget(ViewsEnum::CATALOG_CATEGORY_ROOT_LEFT_BLOCK) ?>
    <aside class="b-filter b-filter--accordion">
        <div class="b-filter__wrapper">
            <?php /** @var Category $cat */ ?>
            <?php foreach ($categoriesWithChildren as $cat) {
    ?>
                <div class="b-accordion b-accordion--filter">
                    <a class="b-accordion__header b-accordion__header--filter js-toggle-accordion"
                       href="javascript:void(0);"
                       title="<?= $cat->getCanonicalName() ?>">
                        <?= $cat->getCanonicalName() ?>
                    </a>
                    <div class="b-accordion__block js-dropdown-block">
                        <ul class="b-filter-link-list">
                            <?php foreach ($cat->getChild() as $child) {
        ?>
                                <li class="b-filter-link-list__item">
                                    <a class="b-filter-link-list__link"
                                       href="<?= $child->getSectionPageUrl() ?>"
                                       title="<?= $child->getCanonicalName() ?>">
                                        <?= $child->getCanonicalName() ?>
                                    </a>
                                </li>
                            <?php
    } ?>
                        </ul>
                    </div>
                </div>
            <?php
} ?>
        </div>
    </aside>
<?php $this->EndViewTarget() ?>

<?php $this->SetViewTarget(ViewsEnum::CATALOG_CATEGORY_ROOT_MAIN_BLOCK) ?>
    <main class="b-catalog__main b-catalog__main--first-step" role="main">
        <?php
        $childCategories = $categoriesWithChildren->slice(0, 2);
        include 'category_view.php';
        $GLOBALS['catalogSectionBannerFilter'] = ['SECTION_CODE' => BannerSectionCode::CATALOG_BANNER_CODE];
        $APPLICATION->IncludeComponent(
            'bitrix:news.list',
            'catalog.section.banner',
            [
                'ACTIVE_DATE_FORMAT'              => 'd.m.Y',
                'ADD_SECTIONS_CHAIN'              => 'N',
                'AJAX_MODE'                       => 'N',
                'AJAX_OPTION_ADDITIONAL'          => '',
                'AJAX_OPTION_HISTORY'             => 'N',
                'AJAX_OPTION_JUMP'                => 'N',
                'AJAX_OPTION_STYLE'               => 'Y',
                'CACHE_FILTER'                    => 'N',
                'CACHE_GROUPS'                    => 'Y',
                'CACHE_TIME'                      => $arParams['CACHE_TIME'],
                'CACHE_TYPE'                      => 'A',
                'CHECK_DATES'                     => 'Y',
                'DETAIL_URL'                      => '',
                'DISPLAY_BOTTOM_PAGER'            => 'N',
                'DISPLAY_DATE'                    => 'Y',
                'DISPLAY_NAME'                    => 'Y',
                'DISPLAY_PICTURE'                 => 'Y',
                'DISPLAY_PREVIEW_TEXT'            => 'Y',
                'DISPLAY_TOP_PAGER'               => 'N',
                'FIELD_CODE'                      => [
                    'ID',
                    'CODE',
                    'XML_ID',
                    'NAME',
                    'DETAIL_PICTURE',
                    'PREVIEW_PICTURE',
                ],
                'FILTER_NAME'                     => 'catalogSectionBannerFilter',
                'HIDE_LINK_WHEN_NO_DETAIL'        => 'N',
                'IBLOCK_ID'                       => IblockUtils::getIblockId(
                    IblockType::PUBLICATION,
                    IblockCode::BANNERS
                ),
                'IBLOCK_TYPE'                     => IblockType::PUBLICATION,
                'INCLUDE_IBLOCK_INTO_CHAIN'       => 'N',
                'INCLUDE_SUBSECTIONS'             => 'N',
                'MESSAGE_404'                     => '',
                'NEWS_COUNT'                      => '1',
                'PAGER_BASE_LINK_ENABLE'          => 'N',
                'PAGER_DESC_NUMBERING'            => 'N',
                'PAGER_DESC_NUMBERING_CACHE_TIME' => '36000',
                'PAGER_SHOW_ALL'                  => 'N',
                'PAGER_SHOW_ALWAYS'               => 'N',
                'PARENT_SECTION'                  => '',
                'PARENT_SECTION_CODE'             => '',
                'PREVIEW_TRUNCATE_LEN'            => '',
                'PROPERTY_CODE'                   => ['LINK', 'IMG_TABLET'],
                'SET_BROWSER_TITLE'               => 'N',
                'SET_LAST_MODIFIED'               => 'N',
                'SET_META_DESCRIPTION'            => 'N',
                'SET_META_KEYWORDS'               => 'N',
                'SET_STATUS_404'                  => 'N',
                'SET_TITLE'                       => 'N',
                'SHOW_404'                        => 'N',
                'SORT_BY1'                        => 'RAND',
                'SORT_ORDER1'                     => 'RAND',
                'STRICT_SECTION_CHECK'            => 'N',
            ],
            false,
            ['HIDE_ICONS' => 'Y']
        );

        $childCategories = $categoriesWithChildren->slice(2);
        include 'category_view.php';
        ?>
    </main>
<?php $this->EndViewTarget();
